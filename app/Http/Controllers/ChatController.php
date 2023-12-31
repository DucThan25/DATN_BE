<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatRoom;
use Illuminate\Http\Request;
use App\Events\NewChatMessageRoom;

use App\Models\User;
use App\Models\Chat;
use App\Events\ChatMessageSent;
use App\Events\ChatMessageStatus;
use App\Http\Requests\Chat\CreateChatRequest;
use App\Http\Requests\Chat\SendTextMessageRequest;
use App\Models\ChatMessageUser;
use App\Http\Resources\ChatResource;
use App\Http\Resources\MassageResource;

class ChatController extends Controller
{
    //chat room
    public function rooms( Request $request){
        return ChatRoom::all();
    }

    public function message( Request $request, $roomId){
        return ChatMessage::where('chat_room_id', $roomId)
            ->with('user')
            ->orderBy('created_at','ASC')
            ->get();
    }

    public function newMessage( Request $request, $roomId){
        $newMessage = new ChatMessage();
        $newMessage->user_id = auth()->user()->id;;
        $newMessage->chat_room_id = $roomId;
        $newMessage->message = $request->message;
        $newMessage->save();

        broadcast(new NewChatMessageRoom($newMessage , auth()->user()))->toOthers();
        return  $newMessage;
    }
    public function createChatRoom(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $room = ChatRoom::create([
            'name' => $request->input('name'),
        ]);
        return response()->json(['data' => $room]);
    }

    // chat user

    public function getChats(Request $request){
        $user = $request->user();
        $chats = $user->chats()->with('participants')->get();
        $success = true;
        return response()->json( [
            'chats' => $chats,
            'success' => $success
        ],200);
    }
    public function createChat(CreateChatRequest $request){
        $users = $request->users;
        $chat =  $request->user()->chats()->whereHas('participants',function($q) use($users){
            $q->where('user_id', $users[0]);
        })->first();
        if(empty($chat)){
        array_push( $users,$request->user()->id);
        $chat = Chat::create()->makePrivate($request->isPrivate);
        $chat->participants()->attach($users); 
        }
        $success = true;
        return response()->json( [
            'chat' => new ChatResource($chat),
            'success' =>$success
        ],200);
    }
    public function searchUsers(Request $request){
        $users = User::where('email','like',"%{$request->email}%")->limit(3)->get();
        return response()->json( [
            'users'=> $users ,
        ],200);
    }

    public function getChatById(Chat $chat,Request $request){
        if($chat->isParticipant($request->user()->id)){
            $messages = $chat->messages()->with('sender')->orderBy('created_at','asc')->paginate('150');
            return response()->json( [
               'chat' => new ChatResource($chat),
               'messages' => MassageResource::collection($messages)->response()->getData(true)
            ],200);
        }else{
            return response()->json([
                'message' => 'not found'
            ], 404);
        }
    }

    public function sendTextMessage(SendTextMessageRequest $request){
        $chat = Chat::find($request->chat_id);
        if($chat->isParticipant($request->user()->id)){
        $message = ChatMessageUser::create([
            'message' => $request->message,
            'chat_id' => $request->chat_id,
            'user_id' => $request->user()->id,
            'data' => json_encode(['seenBy'=>[],'status'=>'sent']) //sent, delivered,seen
        ]);
        $success = true;
        $message =  new MassageResource($message);
        broadcast(new ChatMessageSent($message));
        // foreach($chat->participants as $participant){
        //     if($participant->id != $request->user()->id){
        //         $participant->notify(new NewMessage($message));
        //     }
        // }
        
        return response()->json( [
            "message"=> $message,
            "success"=> $success
        ],200);
        }else{
        return response()->json([
            'message' => 'not found'
        ], 404);
        }
    }

    public function messageStatus(Request $request,ChatMessageUser $message){
        if($message->chat->isParticipant($request->user()->id)){
            $messageData = json_decode($message->data);
            array_push($messageData->seenBy,$request->user()->id);
            $messageData->seenBy = array_unique($messageData->seenBy);
            if(count($message->chat->participants)-1 < count( $messageData->seenBy)){
                $messageData->status = 'delivered';
            }else{
                $messageData->status = 'seen';    
            }
            $message->data = json_encode($messageData);
            $message->save();
            $message =  new MassageResource($message);
            broadcast(new ChatMessageStatus($message));
            return response()->json([
                'message' =>  $message,
                'success' => true
            ], 200);
        }else{
            return response()->json([
                'message' => 'Not found',
                'success' => false
            ], 404); 
        }
    }

}

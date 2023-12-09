<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatRoom;
use Illuminate\Http\Request;
use App\Events\NewChatMessageRoom;

class ChatController extends Controller
{
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
}

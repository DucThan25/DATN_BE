<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateInfoRequest;
use App\Models\Friend;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Home\FriendRepository;
use App\Repositories\Home\PostRepository;
use App\Repositories\UserRepository;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Response;

class UserController extends Controller
{
    use ResponseTrait;

    protected $userRepository;
    protected $postRepository;
    protected $friendRepository;

    public function __construct(
        UserRepository $userRepository,
        PostRepository $postRepository,
        FriendRepository $friendRepository
    ){
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
        $this->friendRepository = $friendRepository;
    }

    public function list(Request $request)
    {
        try {
            $users = $this->userRepository->getlist($request);
            return $this->responseSuccess($users);
        } catch (\Exception $e) {
            Log::error('Error list user', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
                'line' => __LINE__,
            ]);
            return $this->responseError();
        }
    }

    public function updateInfo(UpdateInfoRequest $request)
    {
        $id = Auth::user()->id;
        $dataAvatar = [];
        $dataCoverImage = [];
        if($request->hasFile('avatar')){
            $pathAvatar = Storage::url($request->file('avatar')->store('avatar'));
            $dataAvatar = ['avatar' => $pathAvatar];
        }
        if($request->hasFile('cover_image')){
            $pathCoverImage = Storage::url($request->file('cover_image')->store('cover_image'));
            $dataCoverImage = ['cover_image' => $pathCoverImage];
        }
        if($request->input('date')) {
            $date = Carbon::parse($request->input('date'));
        }else {
            $date = null;
        }
        $data = Arr::collapse([
            $request->validated(), $dataAvatar, $dataCoverImage,
            [
                'date' => $date
            ],
        ]);
        try {
            $this->userRepository->update($data, $id);
            return $this->responseSuccess();
        } catch (\Exception $e) {
            Log::error('Error update infor', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
                'line' => __LINE__,
                'data' => $request->all()
            ]);
            return $this->responseError();
        }
    }

    public function me()
    {
        $user = auth()->user();
        return $this->responseSuccess($user);
    }

    //trang cá nhân
    public function profile(Request $request, $id)
    {
        try {
            $userAuth = auth()->user()->id;
            $checkFriend = '';
            if($id != $userAuth) {
                $friendCheck = $this->friendRepository->checkFriend($id, $userAuth);
                if($friendCheck && $friendCheck->status == Friend::STATUS['CONFIRMED']) {
                    $checkFriend = Friend::STATUS['CONFIRMED'];
                }elseif ($friendCheck && $friendCheck->status == Friend::STATUS['WAIT_CONFIRMATION'] && $friendCheck->user_id == $userAuth) {
                    $checkFriend = Friend::STATUS['INVITATION_SENT'];
                }elseif ($friendCheck && $friendCheck->status == Friend::STATUS['WAIT_CONFIRMATION'] && $friendCheck->friend_id == $userAuth) {
                    $checkFriend = Friend::STATUS['INVITED'];
                }else {
                    $checkFriend = Friend::STATUS['IRRELEVANT'];
                }
            }
            $user = $this->userRepository->find($id);
            $userPost = $this->postRepository->myPost($id);
            $friends = $this->friendRepository->getFriendIds($id);
            $friendIds = [];
            $identifyFriends = [];
            if(count($friends) > 0) {
                foreach ($friends as $friend) {
                    if($friend->user_id == $id) {
                        array_push($friendIds, $friend->friend_id);
                    } else {
                        array_push($friendIds, $friend->user_id);
                    }
                }
                $identifyFriends = $this->friendRepository->getInforFriend($request, $friendIds);
            }
            $data = [
                'user' => $user,
                'userPost' => $userPost,
                'friends' => $identifyFriends,
                'checkFriend' => $checkFriend
            ];
            return $this->responseSuccess($data);
        } catch (\Exception $e) {
            Log::error('Error get infor', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
                'line' => __LINE__,
            ]);
            return $this->responseError();
        }

    }

    //show avatar
    public function showAvatar($filename)
    {
        $path = storage_path('app/avatar/' . $filename);

        if (file_exists($path)) {
            $file = file_get_contents($path);
            return (new Response($file, 200))
                ->header('Content-Type', 'image/jpg'); // Thay 'image/jpeg' bằng loại hình ảnh tương ứng
        } else {
            return response('Hình ảnh không tồn tại1', 404);
        }
    }   
}

<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Friend;
use App\Models\Notify;
use App\Models\User;
use App\Repositories\Home\FriendRepository;
use App\Repositories\NotifyRepository;
use App\Repositories\UserRepository;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;

class FriendController extends Controller
{
    use ResponseTrait;

    protected $friendRepository;
    protected $userRepository;
    protected $notifyRepository;

    public function __construct(FriendRepository $friendRepository, UserRepository $userRepository, NotifyRepository $notifyRepository)
    {
        $this->friendRepository = $friendRepository;
        $this->userRepository = $userRepository;
        $this->notifyRepository = $notifyRepository;
    }

    public function list(Request $request, $id)
    {
        try {
            $friends = $this->friendRepository->getFriendIds($id);
            $friendIds = [];
            if(count($friends) > 0) {
                foreach ($friends as $friend) {
                   if($friend->user_id == $id) {
                       array_push($friendIds, $friend->friend_id);
                   } else {
                       array_push($friendIds, $friend->user_id);
                   }
                }
                $identifyFriends = $this->friendRepository->getInforFriend($request, $friendIds);
                return $this->responseSuccess($identifyFriends);
            }
        }catch (\Exception $exception) {
            Log::error('Error get list friend', [
                'method' => __METHOD__,
                'message' => $exception->getMessage(),
                'data' => $request->all()
            ]);
            return $this->responseError();
        }
    }

    public function listSuggest(Request $request, $id)
    {
        try {
            $friends = $this->friendRepository->getNotSuggestIds($id);
            $friendIds = [];
            if(count($friends) > 0) {
                foreach ($friends as $friend) {
                    if($friend->user_id == $id) {
                        array_push($friendIds, $friend->friend_id);
                    } else {
                        array_push($friendIds, $friend->user_id);
                    }
                }
                $identifyFriends = $this->friendRepository->getInforSuggest($request, $friendIds);
                return $this->responseSuccess($identifyFriends);
            }
        }catch (\Exception $exception) {
            Log::error('Error get list friend', [
                'method' => __METHOD__,
                'message' => $exception->getMessage(),
                'data' => $request->all()
            ]);
            return $this->responseError();
        }
    }

    public function listRequestAddFriend(Request $request, $id)
    {
        try {
            $friends = $this->friendRepository->getRequestAddFriendIds($id);
            $friendIds = [];
            if(count($friends) > 0) {
                foreach ($friends as $friend) {
                    if($friend->user_id == $id) {
                        array_push($friendIds, $friend->friend_id);
                    } else {
                        array_push($friendIds, $friend->user_id);
                    }
                }
                $identifyFriends = $this->friendRepository->getInforFriend($request, $friendIds);
                return $this->responseSuccess($identifyFriends);
            }
        }catch (\Exception $exception) {
            Log::error('Error get list friend', [
                'method' => __METHOD__,
                'message' => $exception->getMessage(),
                'data' => $request->all()
            ]);
            return $this->responseError();
        }
    }

    public function listRequestAddFriendLimit(Request $request, $id)
    {
        try {
            $friends = $this->friendRepository->getRequestAddFriendIds($id);
            $friendIds = [];
            if(count($friends) > 0) {
                foreach ($friends as $friend) {
                    if($friend->user_id == $id) {
                        array_push($friendIds, $friend->friend_id);
                    } else {
                        array_push($friendIds, $friend->user_id);
                    }
                }
                $identifyFriends = $this->friendRepository->getInforFriendLimit($request, $friendIds);
                return $this->responseSuccess($identifyFriends);
            }
        }catch (\Exception $exception) {
            Log::error('Error get list friend', [
                'method' => __METHOD__,
                'message' => $exception->getMessage(),
                'data' => $request->all()
            ]);
            return $this->responseError();
        }
    }

    public function requestAddFriend($id)
    {
        try {
            DB::beginTransaction();
            if($this->friendRepository->checkExistFriend($id)) {
                $error = ['error_add_friend' => ['Không được kết bạn với người đã có trong danh sách bạn bè']];
                return $this->responseError('error', $error, Response::HTTP_BAD_REQUEST, 400);
            }
            else if($this->friendRepository->checkRequestAddFriend($id)) {
                $error = ['error_add_friend' => ['Đã có yêu cầu kết bạn, vui lòng chờ xác nhận']];
                return $this->responseError('error', $error, Response::HTTP_BAD_REQUEST, 400);
            }else {
                $friend = $this->userRepository->findNotMe($id);
                $paramAddFriend = [];
                $paramAddFriend['user_id'] = auth()->user()->id;
                $paramAddFriend['friend_id'] = $friend->id;
                $paramAddFriend['status'] = Friend::STATUS['WAIT_CONFIRMATION'];
                DB::commit();
                $addFriend = $this->friendRepository->addFriend($paramAddFriend);
                $paramNotify = [
                    'creator_id' => $addFriend->user_id,
                    'receiver_id' => $addFriend->friend_id,
                    'type' => Notify::TYPE_NOTIFY['ADD_FRIEND'],
                    'check_read' => Notify::CHECK_READ['NOT_SEEN'],
                    'group_id' => null
                ];
                $this->notifyRepository->create($paramNotify);
                return $this->responseSuccess();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error request add friend', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
                'line' => __LINE__,
            ]);
            return $this->responseError();
        }
    }

    public function confirmAddFriend($id)
    {
        $paramConfirm = [];
        $paramConfirm['status'] = Friend::STATUS['CONFIRMED'];
        try{
            $friend = $this->friendRepository->getRequestAdd($id);
            $this->friendRepository->update($paramConfirm, $friend->id);

            $paramNotify = [
                'creator_id' => $friend->friend_id,
                'receiver_id' => $friend->user_id,
                'type' => Notify::TYPE_NOTIFY['CONFIRM_FRIEND'],
                'check_read' => Notify::CHECK_READ['NOT_SEEN'],
                'group_id' => null
            ];
            $this->notifyRepository->create($paramNotify);
            return $this->responseSuccess();
        } catch (\Exception $e) {
            Log::error('Error confirm add friend', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
                'line' => __LINE__,
            ]);
            return $this->responseError();
        }

    }

    public function cancelRequestAddFriend($id)
    {
        try{
            $friend = $this->friendRepository->getRequestAdd($id);
            $this->friendRepository->delete($friend->id);
            return $this->responseSuccess();
        } catch (\Exception $e) {
            Log::error('Error delete request add friend', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
                'line' => __LINE__,
            ]);
            return $this->responseError();
        }

    }

    public function removeFriend($id)
    {
        try{
            $friend = $this->friendRepository->identifyFriends($id);
            $this->friendRepository->delete($friend->id);
            return $this->responseSuccess();
        } catch (\Exception $e) {
            Log::error('Error remove friend', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
                'line' => __LINE__,
            ]);
            return $this->responseError();
        }
    }
}

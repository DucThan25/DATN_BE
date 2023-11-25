<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use App\Models\Notify;
use App\Repositories\LikeRepository;
use App\Repositories\NotifyRepository;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Log;

class LikeController extends Controller
{
    use ResponseTrait;

    protected $likeRepository;
    protected $notifyRepository;

    public function __construct(LikeRepository $likeRepository, NotifyRepository $notifyRepository,)
    {
        $this->likeRepository = $likeRepository;
        $this->notifyRepository = $notifyRepository;
    }

    public function likeAndUnlike($postId)
    {
        $userId = auth()->user()->id;
        try {
            $like = $this->likeRepository->checkLikePost($userId, $postId);
            if($like) {
                $this->likeRepository->delete($like->id);
                $this->responseSuccess();
            }else {
                $data = [
                    'user_id' => $userId,
                    'post_id' => $postId
                ];
                $createLike = $this->likeRepository->create($data);
                
                $userCreatePost = Post::find($createLike->post_id);
                if($userCreatePost->group_id !== NULL)
                {
                    $paramNotify = [
                        'creator_id' => auth()->user()->id,
                        'receiver_id' => $userCreatePost->user_id,
                        'type' => Notify::TYPE_NOTIFY['LIKE'],
                        'check_read' => Notify::CHECK_READ['NOT_SEEN'],
                        'group_id' => $userCreatePost->group_id,
                    ];
                    $this->notifyRepository->create($paramNotify);
                }
                else{
                    $paramNotify = [
                        'creator_id' => auth()->user()->id,
                        'receiver_id' => $userCreatePost->user_id,
                        'type' => Notify::TYPE_NOTIFY['LIKE'],
                        'check_read' => Notify::CHECK_READ['NOT_SEEN'],
                        'group_id' => NULL,
                    ];
                    $this->notifyRepository->create($paramNotify);
                }
                
                return $this->responseSuccess();
            }
        } catch (\Exception $e) {
            Log::error('Error like post', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
                'line' => __LINE__,
            ]);
            return $this->responseError();
        }

    }
}

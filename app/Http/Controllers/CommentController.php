<?php

namespace App\Http\Controllers;

use App\Http\Requests\Home\Comment\StoreCommentRequest;
use App\Http\Requests\Home\Comment\UpdateCommentRequest;
use App\Models\Comment;
use App\Repositories\CommentRepository;
use App\Repositories\Community\GroupRepository;
use App\Repositories\Home\PostRepository;
use App\Traits\ResponseTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    use ResponseTrait;

    protected $commentRepository;
    protected $postRepository;
    protected $groupRepository;

    public function __construct(
        CommentRepository $commentRepository,
        PostRepository $postRepository,
        GroupRepository $groupRepository
    ) {
        $this->commentRepository = $commentRepository;
        $this->postRepository = $postRepository;
        $this->groupRepository = $groupRepository;
    }

    public function store(StoreCommentRequest $request, $postId)
    {
        $data = Arr::collapse([
            $request->validated(),
            [
                'user_id' => auth()->user()->id,
                'post_id' => $postId
            ],
        ]);
        try {
            $post = $this->postRepository->find($postId);
            if($post->group_id) {
                $this->groupRepository->find($post->group_id);
            }
            $this->commentRepository->create($data);
            return $this->responseSuccess();
        } catch (\Exception $e) {
            Log::error('Error store comment post home', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
                'line' => __LINE__,
                'data' => $request->all()
            ]);
            return $this->responseError();
        }
    }

    public function update (UpdateCommentRequest $request, $id)
    {
        $data = $request->validated();
        $post = $this->commentRepository->find($id);
        $this->authorize('update', $post);
        try {
            $this->commentRepository->update($data,$id);
            return $this->responseSuccess();
        } catch (\Exception $e) {
            Log::error('Error update comment post home', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
            ]);
            return $this->responseError();
        }
    }

    public function delete($id) {
        $comment = $this->commentRepository->find($id);
        $post = $this->postRepository->find($comment->post_id);
        $this->authorize('delete', [$comment, $post]);
        try{
            $this->commentRepository->delete($id);
            return $this->responseSuccess();
        } catch (\Exception $e) {
            Log::error('Error delete comment post home', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
            ]);
            return $this->responseError();
        }
    }

    public function replyComment(StoreCommentRequest $request, $parentId)
    {
        $commentParent = $this->commentRepository->find($parentId);
        $post = $this->postRepository->find($commentParent->post_id);
        if($post->group_id) {
            $group = $this->groupRepository->find($post->group_id);
            $this->authorize('commentGroup', [Comment::class, $group]);
        }
        $data = Arr::collapse([
            $request->validated(),
            [
                'user_id' => auth()->user()->id,
                'parent_id' => $parentId,
                'post_id' => $commentParent->post_id
            ],
        ]);
        try {
            $this->commentRepository->create($data);
            return $this->responseSuccess();
        } catch (\Exception $e) {
            Log::error('Error reply comment comment post home', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
                'line' => __LINE__,
                'data' => $request->all()
            ]);
            return $this->responseError();
        }
    }
}

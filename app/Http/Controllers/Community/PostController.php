<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Requests\Home\Post\StorePostRequest;
use App\Http\Requests\Home\Post\UpdatePostRequest;
use App\Models\Post;
use App\Repositories\Community\GroupRepository;
use App\Repositories\Community\PostGroupRepository;
use App\Repositories\Home\PostRepository;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    use ResponseTrait;

    protected $postRepository;
    protected $groupRepository;
    protected $postGroupRepository;

    public function __construct(
        PostRepository $postRepository,
        GroupRepository $groupRepository,
        PostGroupRepository $postGroupRepository
    ) {
        $this->postRepository = $postRepository;
        $this->groupRepository = $groupRepository;
        $this->postGroupRepository = $postGroupRepository;
    }

    // public function index($groupId)
    // {
    //     $group = $this->groupRepository->find($groupId);
    //     $this->authorize('viewPostGroup', [Post::class, $group]);
    //     try {
    //         $posts = $this->postGroupRepository->getListPost($groupId);
    //         return $this->responseSuccess($posts);
    //     } catch (\Exception $e) {
    //         Log::error('Error list post group', [
    //             'method' => __METHOD__,
    //             'message' => $e->getMessage(),
    //             'line' => __LINE__,
    //         ]);
    //         return $this->responseError();
    //     }
    // }

    public function store(StorePostRequest $request, $groupId)
    {
        $pathImage = '';
        if($request->hasFile('image')){
            $pathImage = Storage::url($request->file('image')->store('post_image'));
        }
        $data = Arr::collapse([
            $request->validated(),
            ['user_id' => auth()->user()->id,
             'group_id' => $groupId,
             'type' => Post::TYPE['COMMUNITY'],
             'image' => $pathImage
            ],
        ]);
        $group = $this->groupRepository->find($groupId);
        $this->authorize('createPostGroup', [Post::class, $group]);
        try {
            $this->postRepository->create($data);
            return $this->responseSuccess();
        } catch (\Exception $e) {
            Log::error('Error store post home', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
                'line' => __LINE__,
                'data' => $request->all()
            ]);
            return $this->responseError();
        }
    }

    public function update (UpdatePostRequest $request, $groupId, $id, Post $post)
    {
        $post = $this->postRepository->find($id);
        $this->authorize('update', $post);
        if($request->hasFile('image')){
            $pathImage = Storage::url($request->file('image')->store('post_image'));
            $data = Arr::collapse([
                $request->validated(),
                [
                    'image' => $pathImage,
                ],
            ]);
        }elseif ($post->image && $request->has('delete_image')){
            $data = Arr::collapse([
                $request->validated(),
                [
                    'image' => null,
                ],
            ]);
        }else {
            $data = $request->validated();
        }
        try {
            $this->postRepository->update($data,$id);
            return $this->responseSuccess();
        } catch (\Exception $e) {
            Log::error('Error update post home', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
            ]);
            return $this->responseError();
        }
    }

    public function delete($groupId, $id) {
        try{
            $group = $this->groupRepository->find($groupId);
            $post = $this->postRepository->find($id);
            $this->authorize('deletePostGroup', [$post, $group]);
            $this->postRepository->delete($id);
            return $this->responseSuccess();
        } catch (\Exception $e) {
            Log::error('Error delete post home', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
            ]);
            return $this->responseError();
        }
    }
}

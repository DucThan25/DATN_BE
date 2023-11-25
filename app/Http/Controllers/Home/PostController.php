<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Requests\Home\Post\StorePostRequest;
use App\Http\Requests\Home\Post\UpdatePostRequest;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Community\PostGroupRepository;
use App\Repositories\Community\UserGroupRepository;
use App\Repositories\Home\FriendRepository;
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
    protected $friendRepository;
    protected $userGroupRepository;
    protected $postGroupRepository;

    public function __construct(
        PostRepository $postRepository,
        FriendRepository $friendRepository,
        UserGroupRepository $userGroupRepository,
        PostGroupRepository $postGroupRepository
    ) {
        $this->postRepository = $postRepository;
        $this->friendRepository = $friendRepository;
        $this->userGroupRepository = $userGroupRepository;
        $this->postGroupRepository = $postGroupRepository;
    }

    public function index()
    {
        $id = auth()->user()->id;
        $myPost = $this->postRepository->myPost($id);
        $friends = $this->friendRepository->getFriendIds($id);
        $friendIds = [];
        $groupIds = [];
        if(count($friends) > 0) {
            foreach ($friends as $friend) {
                if ($friend->user_id == $id) {
                    array_push($friendIds, $friend->friend_id);
                } else {
                    array_push($friendIds, $friend->user_id);
                }
            }
        }
        $postFriends = $this->postRepository->postFriends($friendIds, $id);
        $userGroups = $this->userGroupRepository->groupsJoined($id);
        if(count($userGroups) > 0) {
            foreach ($userGroups as $item)
            {
                array_push($groupIds, $item->group_id);
            }
        }
        $postGroups = $this->postGroupRepository->postGroupJoined($groupIds, $id);
        $homePosts = (array_merge($myPost, $postGroups, $postFriends));
        return $this->responseSuccess($homePosts);
    }

    public function detail($postId)
    {
        try {
            $id = auth()->user()->id;
            $post = $this->postRepository->detail($postId, $id);
            return $this->responseSuccess($post);
        } catch (\Exception $e) {
            Log::error('Error detail post home', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
                'line' => __LINE__,
            ]);
            return $this->responseError();
        }

    }

    public function store(StorePostRequest $request)
    {
        $pathImage = '';
        if($request->hasFile('image')){
            $pathImage = Storage::url($request->file('image')->store('post_image'));
        }
        $data = Arr::collapse([
            $request->validated(),
            [
                'user_id' => auth()->user()->id,
                'image' => $pathImage,
            ],
        ]);
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

    public function update (UpdatePostRequest $request, $id, Post $post)
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

    public function delete($id) {
        $post = $this->postRepository->find($id);
        $this->authorize('delete', $post);
        try{
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

    public function listPostGroupJoined()
    {
        $id = auth()->user()->id;
        try{
            $groupIds = [];
            $userGroups = $this->userGroupRepository->groupsJoined($id);
            if(count($userGroups) > 0) {
                foreach ($userGroups as $item)
                {
                    array_push($groupIds, $item->group_id);
                }
            }
            $postGroups = $this->postGroupRepository->postGroupJoined($groupIds, $id);
            return $this->responseSuccess($postGroups);
        } catch (\Exception $e) {
            Log::error('Error get list post group feed page', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
            ]);
            return $this->responseError();
        }
    }
}

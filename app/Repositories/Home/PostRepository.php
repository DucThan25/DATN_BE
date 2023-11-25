<?php
namespace App\Repositories\Home;

use App\Models\Post;
use App\Repositories\BaseRepository;

class PostRepository extends BaseRepository
{
    protected $post;
    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return Post::class;
    }

    public function postFriends($friendIds, $id)
    {
        return Post::with(['user', 'comments', 'likes'=> function($q) use ($id) {
            $q->where('user_id', $id);
        }])
            ->withCount('likes')
            ->whereIn('user_id', $friendIds)
            ->where('type', Post::TYPE['HOME'])
            ->whereNull('group_id')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->toArray();
    }

    public function myPost($id)
    {
        $authUserId = auth()->user()->id;
      return Post::with(['user', 'comments', 'likes' => function($q) use ($authUserId) {
          $q->where('user_id', $authUserId);
      }])
          ->withCount('likes')
          ->where('user_id', $id)
          ->whereNull('group_id')
          ->orderBy('created_at', 'DESC')
          ->get()
          ->toArray();
    }

    public function detail($postId, $id)
    {
        return Post::with(['user', 'group','comments', 'likes' => function($q) use ($postId, $id) {
            $q->where('user_id', $id);
        }])
            ->withCount('likes')
            ->findOrFail($postId);
    }

    public function postGroup($id, $userId)
    {
        return Post::with(['user', 'comments', 'group', 'likes' => function($q) use ($id, $userId) {
            $q->where('user_id', $userId);
        }])
            ->withCount('likes')
            ->where('group_id', $id)->orderBy('created_at', 'DESC')->get();
    }
}

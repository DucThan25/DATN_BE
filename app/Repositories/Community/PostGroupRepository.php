<?php
namespace App\Repositories\Community;

use App\Models\Post;
use App\Repositories\BaseRepository;

class PostGroupRepository extends BaseRepository
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

    public function postGroupJoined($groupIds, $id)
    {
        return Post::with(['user', 'group', 'comments', 'likes'=> function($q) use ($id) {
            $q->where('user_id', $id);
        }])
            ->withCount('likes')
            ->whereIn('group_id', $groupIds)->get()->toArray();
    }
}

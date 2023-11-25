<?php
namespace App\Repositories\Community;

use App\Models\Group;
use App\Models\UserGroup;
use App\Repositories\BaseRepository;

class GroupRepository extends BaseRepository
{
    public function getModel()
    {
        return Group::class;
    }

    public function getlist($request) {
        $query = Group::query();
        if ($request->has('q') && strlen($request->input('q')) > 0) {
            $query->where('name', 'LIKE', "%" . $request->input('q') . "%");
        }
        $group = $query->get();
        return $group;
    }

    public function myGroups($id)
    {
        $groups = $this->model->with(['userGroups' => function($q) use ($id) {
            $q->where('user_id', $id);
        }])
            ->where('user_id', $id)->get();
        return $groups;
    }

    public function groupDetail($id)
    {
        return $this->model->with(['userGroups' => function($q) use ($id) {
            $q->where('status', UserGroup::STATUS['JOINED']);
        }])->findOrFail($id);
    }
}

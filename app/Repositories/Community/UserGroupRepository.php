<?php
namespace App\Repositories\Community;

use App\Models\UserGroup;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Log;

class UserGroupRepository extends BaseRepository
{
    protected $group;

    public function getModel()
    {
        return UserGroup::class;
    }

    public function groupsJoined($id)
    {
        return $this->model->with(['user', 'group'])->where('user_id', $id)->where('status', UserGroup::STATUS['JOINED'])->get();
    }

    public function getUserGroup($id)
    {
        return $this->model->where('group_id', $id)->get();
    }

    public function findUserGroup($userId, $groupId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('group_id', $groupId)
            ->where('status', UserGroup::STATUS['JOINED'])
            ->first();
    }

    public function findMember($userId, $groupId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('group_id', $groupId)
            ->where('status', UserGroup::STATUS['JOINED'])
            ->where('role', UserGroup::ROLE_GROUP['MEMBER'])
            ->first();
    }

    public function confirmJoin(array $input, $groupId, $userId)
    {
        $model = $this->model
            ->where('group_id', $groupId)
            ->where('user_id', $userId)
            ->where('status', UserGroup::STATUS['WAIT_CONFIRMATION'])
            ->first();
        $model->fill($input);
        $model->save();
        return $model;
    }

    public function setRole($member)
    {
        if($member->role === UserGroup::ROLE_GROUP['COLLABORATOR']) {
            $member->role = UserGroup::ROLE_GROUP['MEMBER'];
        }elseif ($member->role === UserGroup::ROLE_GROUP['MEMBER']) {
            $member->role = UserGroup::ROLE_GROUP['COLLABORATOR'];
        }
        $member->save();
        return $member;
    }

    public function listMember($id)
    {
        return $this->model
            ->with('user')
            ->where('group_id', $id)
            ->where('status', UserGroup::STATUS['JOINED'])
            ->where('role', UserGroup::ROLE_GROUP['MEMBER'])
            ->get();
    }

    public function listAdministrators($id)
    {
        return $this->model
            ->with('user')
            ->where('group_id', $id)
            ->where('status', UserGroup::STATUS['JOINED'])
            ->where(function ($query){
                $query->where('role', UserGroup::ROLE_GROUP['ADMIN'])
                    ->orWhere('role', UserGroup::ROLE_GROUP['COLLABORATOR']);
            })
            ->get();
    }

    public function infoMeIngroup($id, $userId)
    {
        return $this->model->where('group_id', $id)->where('user_id', $userId)->first();
    }

    public function listRequestJoin($request, $id)
    {
        return $this->model
            ->with('user')
            ->where('group_id', $id)
            ->whereHas('user', function ($query) use ($request) {
                if ($request->has('q') && strlen($request->input('q')) > 0) {
                    $query->where('name', 'LIKE', "%" . $request->input('q') . "%");
                }
            })
            ->where('status', UserGroup::STATUS['WAIT_CONFIRMATION'])
            ->get();
    }

    public function allPeopleInGroup($request, $id)
    {
        return $this->model
            ->with('user')
            ->where('group_id', $id)
            ->where('status', UserGroup::STATUS['JOINED'])
            ->whereHas('user', function ($query) use ($request) {
                if ($request->has('q') && strlen($request->input('q')) > 0) {
                    $query->where('name', 'LIKE', "%" . $request->input('q') . "%");
                }
            })
            ->get();
    }

    public function checkJoinedGroup($userId, $id)
    {
        return $this->model->where('group_id', $id)->where('user_id', $userId)->first();
    }

    public function cancelRequestJoinGroup($userId, $id)
    {
        return $this->model
            ->where('group_id', $id)
            ->where('user_id', $userId)
            ->where('status', UserGroup::STATUS['WAIT_CONFIRMATION'])
            ->first();
    }

    public function allCollaboratorId($id)
    {
        return $this->model->where('group_id', $id)->where('role', UserGroup::ROLE_GROUP['COLLABORATOR'])->pluck('id')->toArray();
    }

    public function getArrayUserGroup($id)
    {
        return $this->model->where('group_id', $id)->pluck('id')->toArray();
    }
    public function deleteAllUserGroup(array $userGroupIds)
    {
        return $this->model->whereIn('id', $userGroupIds)->delete();
    }
}

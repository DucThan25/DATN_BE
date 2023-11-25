<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Requests\Community\Group\StoreGroupRequest;
use App\Http\Requests\Community\Group\UpdateGroupRequest;
use App\Models\Group;
use App\Models\Notify;
use App\Models\User;
use App\Models\UserGroup;
use App\Repositories\Community\GroupRepository;
use App\Repositories\Community\UserGroupRepository;
use App\Repositories\Home\PostRepository;
use App\Repositories\NotifyRepository;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GroupController extends Controller
{
    use ResponseTrait;
    protected $groupRepository;
    protected $userGroupRepository;
    protected $postRepository;
    protected $notifyRepository;

    public function __construct(
        GroupRepository $groupRepository,
        UserGroupRepository $userGroupRepository,
        PostRepository $postRepository,
        NotifyRepository $notifyRepository,
    ){
        $this->groupRepository = $groupRepository;
        $this->userGroupRepository = $userGroupRepository;
        $this->postRepository = $postRepository;
        $this->notifyRepository = $notifyRepository;
    }

    public function listRequestJoin(Request $request, $id)
    {
        try {
            $requestJoind = $this->userGroupRepository->listRequestJoin($request, $id);
            return $this->responseSuccess($requestJoind);

        } catch (\Exception $e) {
            Log::error('Error group request join', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
                'line' => __LINE__,
            ]);
            return $this->responseError();
        }
    }

    public function listMember (Request $request, $id)
    {
        try {
            $allPeopleInGroup = $this->userGroupRepository->allPeopleInGroup($request, $id);
            $listMember = $this->userGroupRepository->listMember($id);
            $listAdministrators = $this->userGroupRepository->listAdministrators($id);
            $data = [
                'allPeopleInGroup' => $allPeopleInGroup,
                'listMember' => $listMember,
                'listAdministrators' => $listAdministrators,
            ];
            return $this->responseSuccess($data);
        } catch (\Exception $e) {
            Log::error('Error get list member group', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
                'line' => __LINE__,
            ]);
            return $this->responseError();
        }
    }

    public function myGroups()
    {
        $id = auth()->user()->id;
        $groups = $this->groupRepository->myGroups($id);
        return $this->responseSuccess($groups);
    }

    public function groupJoined()
    {
        $id = auth()->user()->id;
        try {
            $groupsJoined = $this->userGroupRepository->groupsJoined($id);
            return $this->responseSuccess($groupsJoined);
        } catch (\Exception $e) {
            Log::error('Error group joined', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
                'line' => __LINE__,
            ]);
            return $this->responseError();
        }
    }

    public function store(StoreGroupRequest $request)
    {
        $pathAvatar = '';
        $pathCoverImage = '';
        if($request->hasFile('avatar')){
            $pathAvatar = $request->file('avatar')->store('avatar');
        }
        if($request->hasFile('cover_image')){
            $pathCoverImage = $request->file('cover_image')->store('cover_image');
        }
        $dataGroup = Arr::collapse([
            $request->validated(),
            [
                'user_id' => auth()->user()->id,
                'avatar' => $pathAvatar,
                'cover_image' => $pathCoverImage,
            ],
        ]);
        DB::beginTransaction();
        try {
            $group = $this->groupRepository->create($dataGroup);
            $dataUserGroup = [
                'user_id' => auth()->user()->id,
                'group_id' => $group->id,
                'status' => UserGroup::STATUS['JOINED'],
                'role' => UserGroup::ROLE_GROUP['ADMIN'],
            ];
            // $userGroup = $this->userGroupRepository->create($dataUserGroup);
            // DB::commit();
            // $admin = User::where('role', User::ROLE['ADMIN'])->first();
            // $dataAddAdminJoinGroup = [
            //     'user_id' => $admin->id,
            //     'group_id' => $group->id,
            //     'status' => UserGroup::STATUS['JOINED'],
            //     'role' => UserGroup::ROLE_GROUP['MEMBER'],
            // ];
            $this->userGroupRepository->create($dataUserGroup);
            DB::commit();
            return $this->responseSuccess($group->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store group', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
                'line' => __LINE__,
                'data' => $request->all()
            ]);
            return $this->responseError();
        }
    }

    public function listAll(Request $request)
    {
        try {
            $groups = $this->groupRepository->getlist($request);
            return $this->responseSuccess($groups);
        } catch (\Exception $e) {
            Log::error('Error list all group', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
                'line' => __LINE__,
            ]);
            return $this->responseError();
        }
    }

    public function update(UpdateGroupRequest $request, $id)
    {
        $group = $this->groupRepository->find($id);
        $this->authorize('update', $group);
        $dataAvatar = [];
        $dataCoverImage = [];
        if($request->hasFile('avatar')){
            $pathAvatar = $request->file('avatar')->store('avatar');
            $dataAvatar = ['avatar' => $pathAvatar];
        }
        if($request->hasFile('cover_image')){
            $pathCoverImage = $request->file('cover_image')->store('cover_image');
            $dataCoverImage = ['cover_image' => $pathCoverImage];
        }
        $data = Arr::collapse([
            $request->validated(),$dataAvatar, $dataCoverImage]);
        try {
            $this->groupRepository->update($data, $id);
            return $this->responseSuccess();
        } catch (\Exception $e) {
            Log::error('Error update group', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
                'line' => __LINE__,
                'data' => $request->all()
            ]);
            return $this->responseError();
        }
    }

    public function delete($id)
    {
        $group = $this->groupRepository->find($id);
        $this->authorize('delete', $group);
        try {
            $userGroups = $this->userGroupRepository->getUserGroup($id);
            foreach ($userGroups as $item) {
                $this->userGroupRepository->delete($item->id);
            }
            $this->groupRepository->delete($id);
            return $this->responseSuccess();
        } catch (\Exception $e) {
            Log::error('Error delete  group', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
            ]);
            return $this->responseError();
        }
    }

    public function requestJoin($id)
    {
        try {
            DB::beginTransaction();
            $group = $this->groupRepository->find($id);
            $findUserGroup = $this->userGroupRepository->findUserGroup(auth()->user()->id, $id);
            if($findUserGroup) {
                $error = ['error_request_join' => ['Bạn đã ở trong nhóm']];
                return $this->responseError('error', $error, Response::HTTP_BAD_REQUEST, 400);
            } else {
                if($group->type == Group::TYPE['PUBLIC']) {
                    $status = UserGroup::STATUS['JOINED'];
                } else {
                    $status = UserGroup::STATUS['WAIT_CONFIRMATION'];
                }
                $data = [
                    'user_id' => auth()->user()->id,
                    'group_id' => $id,
                    'status' => $status,
                    'role' => UserGroup::ROLE_GROUP['MEMBER'],
                ];
                DB::commit();
                $addRequestJoinGroup = $this->userGroupRepository->create($data);
                $groupRequested = $this->groupRepository->find($addRequestJoinGroup->group_id);
                if($groupRequested && $groupRequested->type === Group::TYPE['PRIVATE']) {
                    $paramNotify = [
                        'creator_id' => $addRequestJoinGroup->user_id,
                        'receiver_id' => $groupRequested->user_id,
                        'type' => Notify::TYPE_NOTIFY['REQUEST_JOIN_GROUP'],
                        'check_read' => Notify::CHECK_READ['NOT_SEEN'],
                        'group_id' => $addRequestJoinGroup->group_id,
                    ];
                    $this->notifyRepository->create($paramNotify);
                }
                return $this->responseSuccess();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error request join group', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
            ]);
            return $this->responseError();
        }
    }

    public function cancelRequestJoin($id)
    {
        $userId = auth()->user()->id;
        try {
            $request = $this->userGroupRepository->cancelRequestJoinGroup($userId, $id);
            $this->userGroupRepository->delete($request->id);
            return $this->responseSuccess();
        } catch (\Exception $e) {
            Log::error('Error cancel my request join group', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
            ]);
            return $this->responseError();
        }


    }

    public function confirmJoin($groupId, $userId)
    {
        $group = $this->groupRepository->find($groupId);
        $this->authorize('confirmJoin', $group);
        $data = [
            'status' => UserGroup::STATUS['JOINED'],
        ];
        try {
            $this->userGroupRepository->confirmJoin($data, $groupId, $userId);
            return $this->responseSuccess();
        } catch (\Exception $e) {
            Log::error('Error confirm join group', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
            ]);
            return $this->responseError();
        }

    }

    public function leave($id)
    {
        try {
            DB::beginTransaction();
            $userGroup = $this->userGroupRepository->findUserGroup(auth()->user()->id, $id);
            if ($userGroup && $userGroup->role == UserGroup::ROLE_GROUP['ADMIN']) {
                $allCollaboratorId = $this->userGroupRepository->allCollaboratorId($id);
                if(count($allCollaboratorId) > 0) {
                    $random = $allCollaboratorId[array_rand($allCollaboratorId, 1)];
                    $userFromRandom = $this->userGroupRepository->find($random);
                    $dataUserGroup = [
                        'role' => UserGroup::ROLE_GROUP['ADMIN']
                    ];
                    $dataGroup = [
                        'user_id' =>  $userFromRandom->user_id
                    ];
                    $this->userGroupRepository->update($dataUserGroup, $random);
                    $this->groupRepository->update($dataGroup, $id);
                    DB::commit();
                    $this->userGroupRepository->delete($userGroup->id);
                    return $this->responseSuccess();
                } else {
                    $userGroupIds = $this->userGroupRepository->getArrayUserGroup($id);
                    $this->userGroupRepository->deleteAllUserGroup($userGroupIds);
                    $this->groupRepository->delete($id);
                    DB::commit();
                    return $this->responseSuccess();
                }
            } else {
                $this->userGroupRepository->delete($userGroup->id);
                DB::commit();
                return $this->responseSuccess();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error leave group', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
            ]);
            return $this->responseError();
        }
    }

    public function pleaseLeave($groupId, $userId)
    {
        try {
            $group = $this->groupRepository->find($groupId);
            $this->authorize('pleaseLeave', $group);
            $member = $this->userGroupRepository->findUserGroup($userId, $groupId); /** bạn đầu là finMember */
            $this->userGroupRepository->delete($member->id);
            return $this->responseSuccess();
        } catch (\Exception $e) {
            Log::error('Error please leave group', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
            ]);
            return $this->responseError();
        }
    }

    public function setRole(Request $request, $groupId, $userId)
    {
        try {
            $group = $this->groupRepository->find($groupId);
            $this->authorize('setRole', $group);
            $member = $this->userGroupRepository->findUserGroup($userId, $groupId);
            if($member) {
                $this->userGroupRepository->setRole($member);
                return $this->responseSuccess();
            } else {
                $error = ['error_set_role' => ['Không tìm thấy thành viên này trong nhóm']];
                return $this->responseError('error', $error, Response::HTTP_BAD_REQUEST, 400);
            }
        } catch (\Exception $e) {
            Log::error('Error set role for member group', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
                'data' => $request->all()
            ]);
            return $this->responseError();
        }
    }

    public function detail($id)
    {
        try{
            $userId = auth()->user()->id;
            $checkJoinedGroup = $this->userGroupRepository->checkJoinedGroup($userId, $id);
            $check = '';
            if($checkJoinedGroup && $checkJoinedGroup->status === UserGroup::STATUS['JOINED']) {
                $check = UserGroup::STATUS['JOINED'];
            } elseif ($checkJoinedGroup && $checkJoinedGroup->status === UserGroup::STATUS['WAIT_CONFIRMATION']) {
                $check = UserGroup::STATUS['WAIT_CONFIRMATION'];
            } else {
                $check = UserGroup::STATUS['NOT_IN_GROUP'];
            }
            $groupDetail = $this->groupRepository->groupDetail($id);
            if($check && $check === UserGroup::STATUS['JOINED']) {
                $checkAdminGroup = false;
                $checkCollaborator = false;
                $infoMeInGroup = $this->userGroupRepository->infoMeIngroup($id, $userId);
                if($infoMeInGroup->role === UserGroup::ROLE_GROUP['ADMIN']) {
                    $checkAdminGroup = true;
                }
                if($infoMeInGroup->role === UserGroup::ROLE_GROUP['COLLABORATOR']) {
                    $checkCollaborator = true;
                }
                $postGroups = $this->postRepository->postGroup($id, $userId);
                foreach ($postGroups as $post) {
                    $post['checkCollaborator'] = $checkCollaborator;
                    $post['checkAdmin'] = $checkAdminGroup;
                }
                $data = [
                    'groupDetail' => $groupDetail,
                    'postGroup' => $postGroups,
                    'checkJoinedGroup' => $check,
                    'checkAdmin' => $checkAdminGroup,
                ];
                return $this->responseSuccess($data);
            } else {
                $data = [
                    'groupDetail' => $groupDetail,
                    'checkJoinedGroup' => $check,
                ];
                return $this->responseSuccess($data);
            }
        } catch (\Exception $e) {
            Log::error('Error get detail by id group', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
            ]);
            return $this->responseError();
        }


    }

    public function confirmPutInGroup($id)
    {
        try {
            $data = [
                'status' => UserGroup::STATUS['JOINED']
            ];
            $this->userGroupRepository->update($data, $id);
            $ConfirmRequested = $this->userGroupRepository->find($id);

            $paramNotify = [
                'creator_id' => auth()->user()->id,
                'receiver_id' => $ConfirmRequested->user_id,
                'type' => Notify::TYPE_NOTIFY['CONFIRM_JOIN_GROUP'],
                'check_read' => Notify::CHECK_READ['NOT_SEEN'],
                'group_id' => $ConfirmRequested->group_id,
            ];
            $this->notifyRepository->create($paramNotify);
            return $this->responseSuccess();
        } catch (\Exception $e) {
            Log::error('Error confirm put in group', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
            ]);
            return $this->responseError();
        }
    }

    public function doNotPutInGroup($id)
    {
        try {
            $this->userGroupRepository->delete($id);
            return $this->responseSuccess();
        } catch (\Exception $e) {
            Log::error('Error do not put in group', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
            ]);
            return $this->responseError();
        }
    }
}

<?php
namespace App\Repositories\Home;

use App\Models\Friend;
use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Log;

class FriendRepository extends BaseRepository
{
    public function getModel()
    {
        return Friend::class;
    }

    public function fillter($queryString)
    {
        $model = $this->model->where('name', 'LIKE', "%" . $queryString . "%");
        return $model;
    }

    public function checkExistFriend($id)
    {
        return Friend::where('user_id', auth()->user()->id)
            ->where('friend_id', $id)
            ->where('status',Friend::STATUS['CONFIRMED'])
            ->first();
    }

    public function checkRequestAddFriend($id)
    {
        return Friend::where('user_id', auth()->user()->id)
            ->where('friend_id', $id)
            ->where('status',Friend::STATUS['WAIT_CONFIRMATION'])
            ->first();
    }

    public function getRequestAdd($id)
    {
        $requestAdd = $this->model
            ->where(function ($query) use ($id) {
                $query->where('friend_id', auth()->user()->id)->where('user_id', $id);
            })
            ->orWhere(function ($query) use ($id) {
                $query->where('user_id', auth()->user()->id)->where('friend_id', $id);
            });
        return $requestAdd->first();
    }

    public function addFriend($data)
    {
        return $this->model->create($data);
    }

    public function identifyFriends($id)
    {
        $query = Friend::where('status', Friend::STATUS['CONFIRMED'])->where(function ($query) use ($id) {
            $query->where(function ($query) use ($id) {
                $query->where('user_id', auth()->user()->id)
                    ->where('friend_id', $id);
            })->orWhere(function ($query) use ($id) {
                $query->where('user_id', $id)
                    ->where('friend_id', auth()->user()->id);
            });
        });
        $friend = $query->first();
        return $friend;
    }

    public function getFriendIds($id)
    {
        $query = Friend::where('status', Friend::STATUS['CONFIRMED']);
        $query->where(function ($query) use ($id){
            $query->where('user_id', $id)->orWhere('friend_id', $id);
        });
        $friends = $query->get();
        return $friends;
    }

    public function getNotSuggestIds($id)
    {
        $query = $this->model->where(function ($query) use ($id){
            $query->where('user_id', $id)->orWhere('friend_id', $id);
        });
        $friends = $query->get();
        return $friends;
    }

    public function getRequestAddFriendIds($id)
    {
        $query = Friend::where('status', Friend::STATUS['WAIT_CONFIRMATION']);
        $query->where(function ($query) use ($id){
            $query->Where('friend_id', $id);
        });
        $friends = $query->get();
        return $friends;
    }

    public function getInforFriend($request ,$friendIds)
    {
        $query = User::WhereIn('id', $friendIds);
        if ($request->has('q') && strlen($request->input('q')) > 0) {
            $query->where('name', 'LIKE', "%" . $request->input('q') . "%");
        };
        $identifyFriends = $query->get();
        foreach ($identifyFriends as $item) {
            $countFriend = $this->getFriendIds($item->id);
            $item['friends_count'] = count($countFriend);
        }
        return $identifyFriends;
    }

    public function getInforSuggest($request ,$friendIds)
    {
        $id = auth()->user()->id;
        $friendIds['idLogin']= $id;
        $query = User::WhereNotIn('id', $friendIds);
        if ($request->has('q') && strlen($request->input('q')) > 0) {
            $query->where('name', 'LIKE', "%" . $request->input('q') . "%");
        };
        $identifyFriends = $query->get();
        foreach ($identifyFriends as $item) {
            $countFriend = $this->getFriendIds($item->id);
            $item['friends_count'] = count($countFriend);
        }
        return $identifyFriends;
    }

    public function getInforFriendLimit($request ,$friendIds)
    {
        $page = $request->input('page');
        $offset = ($page - 1 ) * 7;
        $query = User::WhereIn('id', $friendIds);
        if ($request->has('q') && strlen($request->input('q')) > 0) {
            $query->where('name', 'LIKE', "%" . $request->input('q') . "%");
        };
        $identifyFriends = $query->skip($offset)->take(7)->get();
        foreach ($identifyFriends as $item) {
            $countFriend = $this->getFriendIds($item->id);
            $item['friends_count'] = count($countFriend);
        }
        return $identifyFriends;
    }

    public function checkFriend($id, $userAuth)
    {
        $query = Friend::query();
        $query->where(function ($query) use ($id, $userAuth){
            $query->where('user_id', $id)->Where('friend_id', $userAuth);
        })->orWhere(function ($query) use ($id, $userAuth) {
            $query->where('user_id', $userAuth)->Where('friend_id', $id);
        });
        return $query->first();
    }

}

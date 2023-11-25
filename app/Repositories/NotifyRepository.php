<?php
namespace App\Repositories;

use App\Models\Notify;

class NotifyRepository extends BaseRepository
{
    protected $notify;

    public function getModel()
    {
        return Notify::class;
    }

    public function getList($id)
    {
        return $this->model->with(['group', 'post', 'user'])->where('receiver_id', $id)->orderBy('created_at', 'DESC')->get();
    }

    public function countNotifyUnRead($id)
    {
        return count($this->model->where('receiver_id', $id)->where('check_read', Notify::CHECK_READ['NOT_SEEN'])->get());
    }

    public function read($notify) {
        $notify->check_read = Notify::CHECK_READ['WATCHED'];
        $notify->save();
        return true;
    }

}

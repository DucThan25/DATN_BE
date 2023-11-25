<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserGroup extends Model
{
    use HasFactory;

    protected $table = 'group_user';
    protected $fillable = [
      'user_id',
      'group_id',
      'status',
      'role'
    ];

    const STATUS = [
        'JOINED' => 1,
        'WAIT_CONFIRMATION' => 2,
        'NOT_IN_GROUP' => 3
    ];

    const STATUS_TEXT_ARR = [
        self::STATUS['JOINED'] => 'Đã vào nhóm',
        self::STATUS['WAIT_CONFIRMATION'] => 'Chờ xác nhận',
        self::STATUS['NOT_IN_GROUP'] => 'Chưa vào nhóm'
    ];

    const ROLE_GROUP = [
        'ADMIN' => 1,
        'COLLABORATOR' => 2,
        'MEMBER' => 3
    ];

    const ROLE_GROUP_TEXT = [
        self::ROLE_GROUP['ADMIN'] => 'Quản trị viên 1',
        self::ROLE_GROUP['COLLABORATOR'] => 'Cộng tác viên 2',
        self::ROLE_GROUP['MEMBER'] => 'Thành viên 3'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}

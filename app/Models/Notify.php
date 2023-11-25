<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notify extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'receiver_id',
        'type',
        'check_read',
        'group_id'
    ];

    const TYPE_NOTIFY = [
        'ADD_FRIEND' => 1,
        'REQUEST_JOIN_GROUP' => 2,
        'CONFIRM_FRIEND' => 3,
        'CONFIRM_JOIN_GROUP' => 4,
        'LIKE' => 5
    ];

    const CHECK_READ = [
        'WATCHED' => 1,
        'NOT_SEEN' => 2
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'creator_id', 'id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}

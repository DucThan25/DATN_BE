<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
      'user_id',
      'group_id',
      'title',
      'content',
      'type',
      'image'
    ];

    const TYPE = [
        'HOME' => 1,
        'COMMUNITY' => 2
    ];

    const TYPE_TEXT_ARR = [
        self::TYPE['HOME'] => 'Bài viết trang chủ',
        self::TYPE['COMMUNITY'] => 'Bài viết hội nhóm'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id', 'id')->with('user')->orderBy('created_at', 'DESC');
    }

    public function likes()
    {
        return $this->hasMany(Like::class, 'post_id', 'id');
    }
}

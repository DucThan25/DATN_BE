<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Group extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
      'user_id',
      'name',
      'introduce',
      'type',
      'avatar',
      'cover_image',
    ];

    protected $appends = ['avatar'];

    const TYPE = [
        'PUBLIC' => 1,
        'PRIVATE' => 2
    ];

    const TYPE_TEXT_ARR = [
        self::TYPE['PUBLIC'] => 'Công khai',
        self::TYPE['PRIVATE'] => 'Không công khai'
    ];

    public function getAvatarAttribute()
    {
        return isset($this->attributes['avatar']) ? Storage::url($this->attributes['avatar']) : null;
    }

    public function getCoverImageAttribute()
    {
        return isset($this->attributes['cover_image']) ? Storage::url($this->attributes['cover_image']) : null;
    }

    public function user()
    {
        return $this->belongsToMany(User::class); //một group có nhiều user
    }

    public function userGroups()
    {
        return $this->hasMany(UserGroup::class, 'group_id', 'id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'group_id', 'id');
    }

    public function notifys()
    {
        return $this->hasMany(Notify::class, 'group_id', 'id');
    }


}

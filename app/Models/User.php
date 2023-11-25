<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'date',
        'gender',
        'address',
        'introduce',
        'avatar',
        'cover_image',
        'role',
        'token',
        'status',
        'type_account',
        'check_change_password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }

    const TYPE_ACCOUNT = [
      'FACEBOOK' => 1,
      'GOOGLE' => 2,
      'BASIC' => 3
    ];

    const CHANGE_PASSWORD = [
      'CHANGED' => 1,
      'UN_CHANGE' => 2
    ];

    const STATUS = [
        'ACTIVE' => 1,
        'DE_ACTIVE' => 2
    ];

    const PASSWORD_DEFAULT = '123456';

    const GENDER = [
        'MALE' => 1,
        'FEMALE' => 2
    ];

    const ROLE = [
        'ADMIN' => 2,
        'USER' => 1
    ];
    const GENDER_TEXT_ARR = [
        self::GENDER['MALE'] => 'Nam',
        self::GENDER['FEMALE'] => 'Nữ'
    ];

    const ROLE_TEXT_ARR = [
        self::ROLE['ADMIN'] => 'Admin',
        self::ROLE['USER'] => 'User'
    ];
    protected $appends = ['gender_text'];

    public function setPasswordAttribute($value)
    {
        if(!$value) {
            $this->attributes['password'] = bcrypt('admin12345');
        }else {
            $this->attributes['password'] = bcrypt($value);
        }

    }

    public function getGenderTextAttribute()
    {
        if($this->gender == self::GENDER['MALE']){
            $name = 'Nam';
        }
        else{
            $name = 'Nữ';
        }
        return $name;
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id', 'id');
    }

    public function friends()
    {
        return $this->hasMany(Friend::class, 'user_id', 'id');
    }

    public function userGroups()
    {
        return $this->hasMany(UserGroup::class, 'user_id', 'id');
    }

    public function notify()
    {
        return $this->hasMany(Notify::class, 'creator_id','id');
    }
    
}

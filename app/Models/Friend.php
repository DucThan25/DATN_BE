<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friend extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'friend_id',
        'status'
    ];

    const STATUS = [
        'WAIT_CONFIRMATION' => 1,
        'CONFIRMED' => 2,
        'IRRELEVANT' => 3,
        'INVITATION_SENT' => 4,
        'INVITED' => 5
    ];

    const STATUS_TEXT_ARR = [
      self::STATUS['WAIT_CONFIRMATION'] => 'Chờ xác nhận',
      self::STATUS['CONFIRMED'] => 'Đã xác nhận',
      self::STATUS['IRRELEVANT'] => 'Chưa liên quan',
      self::STATUS['INVITATION_SENT'] => 'Đã gửi lời mời kết bạn',
      self::STATUS['INVITED'] => 'Đã nhận lời mời kết bạn',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

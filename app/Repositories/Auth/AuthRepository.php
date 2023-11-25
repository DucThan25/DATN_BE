<?php
namespace App\Repositories\Auth;

use App\Models\User;
use App\Repositories\BaseRepository;

class AuthRepository extends BaseRepository
{
    protected $user;
    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return User::class;
    }

    public function register($data)
    {
        return $this->model->create($data);
    }

    public function getFirst($email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function findAccountVerify($token)
    {
        return User::where('token', $token)->where('status', User::STATUS['DE_ACTIVE'])->first();
    }
}

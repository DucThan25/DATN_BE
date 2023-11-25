<?php
namespace App\Repositories;

use App\Models\User;
use App\Repositories\BaseRepository;
use http\Env\Request;

class UserRepository extends BaseRepository
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

    public function findNotMe($id)
    {
        $user = $this->model->where('id', '<>', auth()->user()->id)->findOrFail($id);
        return $user;
    }

    public function getlist($request) {
        $query = User::query();
        if ($request->has('q') && strlen($request->input('q')) > 0) {
            $query->where('name', 'LIKE', "%" . $request->input('q') . "%");
        }
        $users = $query->get();
        return $users;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Notify;
use App\Repositories\NotifyRepository;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Log;

class NotifyController extends Controller
{
    use ResponseTrait;
    protected $notifyRepository;

    public function __construct(NotifyRepository $notifyRepository,)
    {
        $this->notifyRepository = $notifyRepository;
    }

    public function list()
    {
        try {
            $notifies = $this->notifyRepository->getList(auth()->user()->id);
            $countNotifyUnRead = $this->notifyRepository->countNotifyUnRead(auth()->user()->id);
            $data = [
                'notifies' => $notifies,
                'countNotifyUnRead' => $countNotifyUnRead
            ];
            return $this->responseSuccess($data);
        } catch (\Exception $e) {
            Log::error('Error get list notifies', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
            ]);
            return $this->responseError();
        }

    }

    public function read($id)
    {
        try {
            $notify = $this->notifyRepository->find($id);
            if($notify) {
                $this->notifyRepository->read($notify);
            }
            return $this->responseSuccess();
        } catch (\Exception $e) {
            Log::error('Error read notify', [
                'method' => __METHOD__,
                'message' => $e->getMessage(),
            ]);
            return $this->responseError();
        }

    }
}

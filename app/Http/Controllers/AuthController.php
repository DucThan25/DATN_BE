<?php

namespace App\Http\Controllers;

use App\Helpers\Constant;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Jobs\SendConfirmationMail;
use App\Jobs\SendConfirmationMailPass;
use App\Mail\VerifyEmail;
use App\Models\User;
use App\Repositories\Auth\AuthRepository;
use App\Traits\ResponseTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    use ResponseTrait;

    protected $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $user = User::find(auth()->id()); //kiểm tra đăng nhập
            if ($user) {
                $credentials = [
                    'email' => $user->email,
                    'password' => $request->input('current_password'),
                ];
                if (!auth()->attempt($credentials)) {
                    $error = ['current_password' => ['Mật khẩu hiện tại không chính xác']];
                    return $this->responseError('error', $error, 400);
                }
                if ($request->input('password') === $request->input('current_password')) {
                    $error = ['password' => ['Mật khẩu mới phải khác mật khẩu hiện tại']];
                    return $this->responseError('error', $error, 400);
                }
                $user->password = ($request->input('password'));
                $user->check_change_password = User::CHANGE_PASSWORD['CHANGED'];
                $user->save();
            }
            return $this->responseSuccess();
        } catch (\Exception $e) {
            Log::error('Error change user password', [
                'method' => __METHOD__,
                'message' => $e->getMessage()
            ]);
            return $this->responseError();
        }
    }

    public function login()
    {
        try {
            $credentials = request(['email', 'password']);
            $user = User::where('email',$credentials['email'])->first();
            if (!$token = auth()->attempt($credentials)) {
                return $this->responseError(['error' => 'Unauthorized'], 401);
            }else if ($user && $user->status === User::STATUS['DE_ACTIVE']) {
                return $this->responseError(['error_active' => 'Tài khoản của bạn chưa được kích hoạt!'], 401);
            }else {
                return $this->respondWithToken($token);
            }
        } catch (\Exception $e) {
            Log::error('Error login', [
                'method' => __METHOD__,
                'message' => $e->getMessage()
            ]);
            $this->responseError();
        }
    }

    public function register(RegisterRequest $request)
    {
        $data = Arr::collapse([
            $request->validated(),
            [
                'status' => User::STATUS['DE_ACTIVE'],
                'token' => Str::random(8),
                'password' => User::PASSWORD_DEFAULT,
                'check_change_password' => User::CHANGE_PASSWORD['UN_CHANGE'],
                'type_account' => User::TYPE_ACCOUNT['BASIC'],
                'role' => User::ROLE['USER']
            ],
        ]);
        DB::beginTransaction();
        try{
            DB::commit();
            $user = $this->authRepository->register($data);
            SendConfirmationMail::dispatch($user);
            return $this->responseSuccess(['message' => 'Gửi mail xác nhận thành công']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error register', [
                'method' => __METHOD__,
                'message' => $e->getMessage()
            ]);
            $this->responseError();
        }
    }

    public function confirmEmail($token)
    {
        try {
            $user = $this->authRepository->findAccountVerify($token);
            if (!$user) {
                return $this->responseError(['message' => 'Lỗi xác thực tài khoản vui lòng thử đăng ký lại sau', 400]);
            }else {
                $data = [
                    'status' => User::STATUS['ACTIVE'],
                    'token' => null
                ];
                $this->authRepository->update($data, $user->id);
                return Redirect::away((env('FRONT_END_URL', 'http://localhost:8080/')));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error verify email', [
                'method' => __METHOD__,
                'message' => $e->getMessage()
            ]);
            $this->responseError();
        }

    }

    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }
    
    public function callBack()
    {
        try{
            $paramUser = [];
            $user = Socialite::driver('google')->stateless()->user();
            $paramUser['type_account'] = User::TYPE_ACCOUNT['GOOGLE'];
            
            $findUser = $this->authRepository->getFirst($user->getEmail());
            if(!$findUser) {
                $paramUser['email'] = $user->getEmail();
                $paramUser['name'] = $user->getName();
                $paramUser['avatar'] = $user->getAvatar();
                $paramUser['status'] = User::STATUS['ACTIVE'];
                $paramUser['email'] == config('main.email_admin') ? $paramUser['role'] = User::ROLE['ADMIN'] : $paramUser['role'] = User::ROLE['USER'];
                $paramUser['password'] = '';
                $paramUser['check_change_password'] = User::CHANGE_PASSWORD['CHANGED'];
                $newUser = $this->authRepository->register($paramUser);
                $user= $newUser;
            } else {
                $user = $findUser;
            }
            auth()->login($user);
            $token = auth()->login($user);
            return redirect(env('FRONT_END_URL', 'http://localhost:8080/').'login?token=' . $token);
        } catch (\Exception $e) {
            Log::error('Error login', [
                'method' => __METHOD__,
                'message' => $e->getMessage()
            ]);
            $this->responseError();
        }
    }

    public function logout()
    {
        try {
            auth()->logout();
            return response()->json(['message' => 'Đăng xuất thành công']);
        } catch (\Exception $e) {
            Log::error('Error logout', [
                'method' => __METHOD__,
                'message' => $e->getMessage()
            ]);
            $this->responseError();
        }
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

}

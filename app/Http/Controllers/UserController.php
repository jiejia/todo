<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Common\Pojo\ResultCode;
use App\Exceptions\PasswordException;

class UserController extends Controller
{
    protected $userService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserService $userService)
    {
        //
        $this->middleware('auth', ['except' => [
            'login',
            'store',
        ]]);

        $this->userService = $userService;
    }

    /**
     * 用户注册
     *
     * @param Request $request
     * @return array
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     *
     * @version  2020/1/31 11:13
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.3.4
     */
    public function store(Request $request): array
    {
        try {
            $data = $this->userService->store($request->all());
            return ['code' => ResultCode::OK['code'], 'msg' => ResultCode::OK['msg'], 'data' => $data];
        } catch (ValidationException $e) {
            return ['code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'], 'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'], 'data' => $e->errors()];
        }
    }

    /**
     * 用户登录
     *
     * @param Request $request
     * @return array
     *
     * @license  PHP Version 7.3.4
     * @version  2020/1/31 11:13
     * @author   jiejia <jiejia2009@gmail.com>
     */
    public function login(Request $request): array
    {
        try {
            $data = $this->userService->login($request->all());
            return ['code' => ResultCode::OK['code'], 'msg' => ResultCode::OK['msg'], 'data' => $data];
        } catch (ValidationException $e) {
            return ['code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'], 'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'], 'data' => $e->errors()];
        } catch (PasswordException $e) {
            return ['code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'], 'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'], 'data' => ['password' => [$e->getMessage()]]];

        }
    }

    public function logout(Request $request): array
    {
        return [];
    }

    //
}

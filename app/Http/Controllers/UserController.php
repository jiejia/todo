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
            'sendPasswordEmail',
            'checkLogin'
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

    public function update(Request $request): array
    {
        try {
            $data = $this->userService->update(array_merge($request->all(), ['user_id' => Auth::id()]));
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
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     *
     * @version  2020/2/1 11:52
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.3.4
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

    /**
     * 详情
     *
     * @param Request $request
     * @return array
     *
     * @license  PHP Version 7.3.4
     * @version  2020/1/31 16:16
     * @author   jiejia <jiejia2009@gmail.com>
     */
    public function detail(Request $request): array
    {
        try {
            $data = $this->userService->detail(array_merge($request->all(), ['user_id' => Auth::id()]));
            return ['code' => ResultCode::OK['code'], 'msg' => ResultCode::OK['msg'], 'data' => $data];
        } catch (ValidationException $e) {
            return ['code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'], 'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'], 'data' => $e->errors()];
        }
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     * @version  2020-3-27 11:47
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.3.4
     */
    public function sendPasswordEmail(Request $request): array
    {
        try {
            $data = $this->userService->sendPasswordEmail($request->all());
            return ['code' => ResultCode::OK['code'], 'msg' => ResultCode::OK['msg'], 'data' => $data];
        } catch (ValidationException $e) {
            return ['code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'], 'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'], 'data' => $e->errors()];
        }
    }

    /**
     * @param Request $request
     * @return array
     * @license  PHP Version 7.3.4
     * @version  2020-3-27 11:57
     * @author   jiejia <jiejia2009@gmail.com>
     */
    public function checkLogin(Request $request): array
    {
        try {
            $data = $this->userService->checkLogin($request->all());
            return ['code' => ResultCode::OK['code'], 'msg' => ResultCode::OK['msg'], 'data' => $data];
        } catch (ValidationException $e) {
            return ['code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'], 'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'], 'data' => $e->errors()];
        }
    }
}

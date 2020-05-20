<?php

namespace App\Http\Controllers;

use App\Common\Utils\MessageManager;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Common\Pojo\ResultCode;
use App\Exceptions\User\PasswordException;

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
            'checkLogin',
            'passwordReset'
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
            $data = $this->userService->store(array_merge($request->all(), ['user_id' => Auth::id()]));
            return ['code' => ResultCode::OK['code'], 'msg' => ResultCode::OK['msg'], 'data' => $data];
        } catch (ValidationException $e) {
            return [
                'code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'],
                'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'],
                'data' => [],
                'errors' => MessageManager::getValidateErrors($e->errors())
            ];
        }
    }

    /**
     * 修改密码
     *
     * @param Request $request
     * @return array
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     *
     * @version  2020-5-9 14:36
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.2.9
     */
    public function passwordReset(Request $request): array
    {
        try {
            $data = $this->userService->passwordReset(array_merge($request->all(), ['user_id' => Auth::id()]));
            return ['code' => ResultCode::OK['code'], 'msg' => ResultCode::OK['msg'], 'data' => $data];
        } catch (ValidationException $e) {
            return [
                'code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'],
                'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'],
                'data' => [],
                'errors' => MessageManager::getValidateErrors($e->errors())
            ];
        }
    }

    /**
     * 更新个人信息
     *
     * @param Request $request
     * @return array
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     *
     * @version  2020-5-9 14:37
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.2.9
     */
    public function updateProfile(Request $request): array
    {
        try {
            $data = $this->userService->updateProfile(array_merge($request->all(), ['user_id' => Auth::id()]));
            return ['code' => ResultCode::OK['code'], 'msg' => ResultCode::OK['msg'], 'data' => $data];
        } catch (ValidationException $e) {
            return [
                'code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'],
                'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'],
                'data' => [],
                'errors' => MessageManager::getValidateErrors($e->errors())
            ];
        }
    }

    /**
     * 修改头像
     *
     * @param Request $request
     * @return array
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     *
     * @version  2020-5-19 9:42
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.2.9
     */
    public function changeAvatar(Request $request): array
    {
        try {
            $data = $this->userService->changeAvatar(array_merge($request->all(), ['user_id' => Auth::id()]));
            return ['code' => ResultCode::OK['code'], 'msg' => ResultCode::OK['msg'], 'data' => $data];
        } catch (ValidationException $e) {
            return [
                'code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'],
                'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'],
                'data' => [],
                'errors' => MessageManager::getValidateErrors($e->errors())
            ];
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
            return [
                'code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'],
                'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'],
                'data' => [],
                'errors' => MessageManager::getValidateErrors($e->errors())
            ];
        } catch (PasswordException $e) {
            return [
                'code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'],
                'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'],
                'data' => [],
                'errors' => ['password' => $e->getMessage()]
            ];
        }
    }

    /**
     * 登出
     *
     * @param Request $request
     * @return array
     * @version  2020-5-19 9:35
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.2.9
     */
    public function logout(Request $request): array
    {
        $token = $request->header('authorization');
        $token = str_replace('Bearer ', '', $token);
        return [
            'code' => 0,
            'msg' => '操作成功',
            'data' => ['token' => $token, 'msg' => '请客户端自行关闭此token']
        ];
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
            return [
                'code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'],
                'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'],
                'data' => [],
                'errors' => MessageManager::getValidateErrors($e->errors())
            ];
        }
    }

    /**
     * 发送修改密码邮件
     *
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
            $data = $this->userService->sendPasswordEmail(array_merge($request->all(), ['user_id' => Auth::id()]));
            return ['code' => ResultCode::OK['code'], 'msg' => ResultCode::OK['msg'], 'data' => $data];
        } catch (ValidationException $e) {
            return [
                'code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'],
                'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'],
                'data' => [],
                'errors' => MessageManager::getValidateErrors($e->errors())
            ];
        }
    }

    /**
     * 刷新token
     *
     * @param Request $request
     * @return array
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     * @version  2020-5-19 9:48
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.2.9
     */
    public function refresh(Request $request): array
    {
        try {
            $data = $this->userService->refresh(array_merge($request->all(), ['user_id' => Auth::id()]));
            return ['code' => ResultCode::OK['code'], 'msg' => ResultCode::OK['msg'], 'data' => $data];
        } catch (ValidationException $e) {
            return [
                'code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'],
                'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'],
                'data' => [],
                'errors' => MessageManager::getValidateErrors($e->errors())
            ];
        }
    }

    /**
     * 检查登录
     *
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
            return [
                'code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'],
                'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'],
                'data' => [],
                'errors' => MessageManager::getValidateErrors($e->errors())
            ];
        }
    }
}

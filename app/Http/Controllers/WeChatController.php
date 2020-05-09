<?php
namespace App\Http\Controllers;

use App\Common\Pojo\ResultCode;
use App\Exceptions\PasswordException;
use App\Services\WechatService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;


class WeChatController extends Controller
{
    protected $wechatService;

    public function __construct(WechatService $wechatService)
    {
        $this->wechatService = $wechatService;
    }


    public function login(Request $request): array
    {
        try {
            $data = $this->wechatService->login($request->all());
            return ['code' => ResultCode::OK['code'], 'msg' => ResultCode::OK['msg'], 'data' => $data];
        } catch (ValidationException $e) {
            return ['code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'], 'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'], 'data' => $e->errors()];
        } catch (Exception $e) {
            return ['code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'], 'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'], 'data' => $e->getMessage()];

        }
    }

    public function checkSession(Request $request): array
    {
        try {
            $data = $this->wechatService->checkSession($request->all());
            return ['code' => ResultCode::OK['code'], 'msg' => ResultCode::OK['msg'], 'data' => $data];
        } catch (ValidationException $e) {
            return ['code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'], 'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'], 'data' => $e->errors()];
        } catch (Exception $e) {
            return ['code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'], 'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'], 'data' => $e->getMessage()];

        }
    }
}

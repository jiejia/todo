<?php

namespace App\Http\Controllers;

use App\Common\Utils\MessageManager;
use App\Services\TaskService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Common\Pojo\ResultCode;

class TaskController extends Controller
{
    protected $taskService;

    protected $user;

    /**
     * TaskController constructor.
     * @param TaskService $taskService
     */
    public function __construct(TaskService $taskService)
    {
        //
        $this->middleware('auth');

        $this->user = Auth::user();

        $this->taskService = $taskService;
    }

    /**
     * 发布或编辑分类
     *
     * @param Request $request
     * @return array
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     *
     * @version  2020/1/31 11:13
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.3.4
     */
    public function createOrUpdate(Request $request): array
    {
        try {
            $data = $this->taskService->createOrUpdate(array_merge($request->all(), ['user_id' => Auth::id()]));
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
     * 列表
     *
     * @param Request $request
     * @return array
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     *
     * @version  2020/1/31 16:16
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.3.4
     */
    public function listOrSearch(Request $request): array
    {
        try {
            $data = $this->taskService->listOrSearch(array_merge($request->all(), ['user_id' => Auth::id()]));
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
            $data = $this->taskService->detail(array_merge($request->all(), ['user_id' => Auth::id()]));
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
     * 删除任务
     *
     * @param Request $request
     * @return array
     *
     * @license  PHP Version 7.3.4
     * @version  2020/1/31 17:10
     * @author   jiejia <jiejia2009@gmail.com>
     */
    public function delete(Request $request): array
    {
        try {
            $data = $this->taskService->delete(array_merge($request->all(), ['user_id' => Auth::id()]));
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

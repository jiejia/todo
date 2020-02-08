<?php

namespace App\Http\Controllers\Task;

use App\Services\Task\CategoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Common\Pojo\ResultCode;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    protected $categoryService;

    protected $user;

    /**
     * CategoryController constructor.
     * @param CategoryService $categoryService
     */
    public function __construct(CategoryService $categoryService)
    {
        //
        $this->middleware('auth');

        $this->user = Auth::user();

        $this->categoryService = $categoryService;
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
            $data = $this->categoryService->createOrUpdate(array_merge($request->all(), ['user_id' => Auth::id()]));
            return ['code' => ResultCode::OK['code'], 'msg' => ResultCode::OK['msg'], 'data' => $data];
        } catch (ValidationException $e) {
            return ['code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'], 'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'], 'data' => $e->errors()];
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
            $data = $this->categoryService->listOrSearch(array_merge($request->all(), ['user_id' => Auth::id()]));
            return ['code' => ResultCode::OK['code'], 'msg' => ResultCode::OK['msg'], 'data' => $data];
        } catch (ValidationException $e) {
            return ['code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'], 'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'], 'data' => $e->errors()];
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
            $data = $this->categoryService->detail(array_merge($request->all(), ['user_id' => Auth::id()]));
            return ['code' => ResultCode::OK['code'], 'msg' => ResultCode::OK['msg'], 'data' => $data];
        } catch (ValidationException $e) {
            return ['code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'], 'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'], 'data' => $e->errors()];
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
            $data = $this->categoryService->delete(array_merge($request->all(), ['user_id' => Auth::id()]));
            return ['code' => ResultCode::OK['code'], 'msg' => ResultCode::OK['msg'], 'data' => $data];
        } catch (ValidationException $e) {
            return ['code' => ResultCode::PARAMETER_VALIDATION_ERROR['code'], 'msg' => ResultCode::PARAMETER_VALIDATION_ERROR['msg'], 'data' => $e->errors()];
        }
    }
}

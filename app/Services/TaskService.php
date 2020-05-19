<?php
namespace App\Services;

use App\Common\Services\BaseService;
use App\Repositories\TaskRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskService extends BaseService
{
    /**
     * @var
     */
    protected $taskRepository;

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rules = [
        'id' => 'bail|integer|exists:tasks,id',
        'title' => 'bail|required_without:id|string|between:1,100',
        'tags' => 'bail|string|between:1,100',
        'content' => 'bail|string|between:0,255',
        'deadline' => 'bail|date',

        'category_id' => 'bail|required_without:id|integer|exists:task_category,id', // TODO 任务分类需要验证是否属于当前登录用户
        'user_id' => 'bail|required|integer|exists:users,id',
        'status' => 'bail|in:0,1',
    ];

    protected $messages = [
      'id.integer' => 'ID必须为整数',
      'id.exists' => 'ID不存在',
      'title.required' => '标题必填',
      'title.between' => '标题不能大于100个字符',
      'category_id.exists' => '任务分类不存在',
      'title.required_without' => '标题不能未空',
      'category_id.required_without' => '请选择任务分类',
      'status.in' => '状态只能是0,1其中之一'
    ];

    /**
     * TaskService constructor.
     * @param TaskRepository $taskRepository
     */
    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * @param array $data
     * @return mixed
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     *
     * @version  2020/1/31 14:34
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.3.4
     */
    public function createOrUpdate(array $data)
    {
        $this->validate($data, $this->rules, $this->messages);

        if (isset($data['id'])) {
            return $this->taskRepository->update($data, $data['id']);
        } else {
            return $this->taskRepository->create($data);
        }
    }

    /**
     * 获取列表
     *
     * @param array $data
     * @return mixed
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     *
     * @version  2019/10/9 13:23
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.3.4
     */
    public function listOrSearch(array $data)
    {
        $rules = [
            'user_id' => 'bail|required|integer|exists:users,id',
            's' => 'string|max:20',
            'tag' => 'string|max:20',
            'category_id' => 'integer|exists:task_category,id',
            'status' => 'integer|in:0,1',
            'deadline' => 'date',
            'order_by' => 'in:created_at_desc,created_at_asc,updated_at_desc,updated_at_asc'
        ];

        $this->validate($data, $rules);

        $pagination = $this->taskRepository->condition($data)->paginate(20);
        $records = $pagination->all();
        return [
            'records' => $records,
            'total' => $pagination->total(),
            'current' => $pagination->currentPage(),
            'size' => (int)$pagination->perPage(),
            'pages' => ceil($pagination->total() / $pagination->perPage()),
        ];
    }

    /**
     * 获取详情
     *
     * @param array $data
     * @param array $columns
     * @return mixed
     *
     * @version  2020/1/31 14:48
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.3.4
     */
    public function detail(array $data, $columns = ['*'])
    {
        $rules = [
            'id' => 'bail|required|integer|exists:tasks',
            'user_id' => 'bail|required|integer|exists:users,id'
        ];
        $this->validate($data, $rules);

        $where = [
            'id' => $data['id'],
            'user_id' => $data['user_id']
        ];
        return $this->taskRepository->findWhere($where, $columns)->first();
    }

    /**
     * 删除
     *
     * @param array $data
     * @return mixed
     *
     * @license  PHP Version 7.3.4
     * @version  2020/1/31 17:10
     * @author   jiejia <jiejia2009@gmail.com>
     */
    public function delete(array $data)
    {
        $rules = [
          'id' => 'required|array',
          'id.*' => 'integer',
          'user_id' => 'integer|exists:users,id'
        ];
        $this->validate($data, $rules);
        $condition = [
            ['id', 'in', $data['id']],
            'user_id' => $data['user_id']
        ];
        $this->taskRepository->deleteWhere($condition);

        return $data['id'];
    }

}

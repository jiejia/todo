<?php
namespace App\Services\Task;

use App\Common\Services\BaseService;
use App\Repositories\Task\CategoryRepository;

class CategoryService extends BaseService
{
    /**
     * @var
     */
    protected $categoryRepository;

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rules = [
        'id' => 'bail|integer|exists:tasks,id',
        'name' => 'bail|required|string|between:1,10',
        'status' => 'bail|integer|in:0,1,2,3,4',
        'color' => 'bail|required|string|size:7',
        'user_id' => 'bail|required|integer|exists:users,id',
    ];

    protected $messages = [
      'id.integer' => 'ID必须为整数',
      'id.exists' => 'ID不存在',
      'title.required' => '标题必填',
      'title.between' => '标题不能大于100个字符',
      'category_id.exists' => '任务分类不存在'
    ];

    /**
     * 类型
     */
    public const TYPE = [
        1 => 'Original',
        2 => 'Famous Quotes',
        3 => 'Books / Poetry',
        4 => 'Movie',
        5 => 'Song'
    ];

    /**
     * CategoryService constructor.
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
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
        $rules = $this->rules;
        $rules['color'] = [
          'bail', 'required',
           'regex:/^#[0-9a-zA-Z]{6}$/'
        ];
        $rules['name'] = [
            'bail',
            'required',
            'string',
            'between:1,10',
            function($attribute, $value, $fail) use ($data) {
                $count = $this->categoryRepository->findWhere(['user_id' => $data['user_id']])->count();

//                dd($count);
                if ($count > 14) {
                    return $fail("清单最多15个");
                }
            }
        ];
        $this->validate($data, $rules);

        if (isset($data['id'])) {
            return $this->categoryRepository->update($data, $data['id']);
        } else {
            $update = [];
            return $this->categoryRepository->create($data);
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
            'status' => 'integer|in:0,1,2,3,4',
            'order_by' => 'in:created_at_desc,created_at_asc,updated_at_desc,updated_at_asc'
        ];

        $this->validate($data, $rules);

        $pagination = $this->categoryRepository->condition($data)->paginate(10);
        $records = $pagination->all();


        foreach ($records as &$record) {
            $record->count =  $record->tasks->count();
        }

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

        $where = ['id' => $data['id'], 'user_id' => $data['user_id']];
        $record = $this->categoryRepository->findWhere($where, $columns)->first();

        return $record;
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
        foreach ($data['id'] as $id) {
            $where = ['id' => $id, 'user_id' => $data['user_id']];
            $this->categoryRepository->deleteWhere($where);
        }
        return $data['id'];
    }

    public function maxCount($attribute, $value)
    {
        dd($attribute);
    }

}

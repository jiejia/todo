<?php
namespace App\Services;

use App\Common\Services\BaseService;
use App\Exceptions\PasswordException;
use App\Repositories\QuoteRepository;
use App\Repositories\UserRepository;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Common\Utils\TokenManager;


class UserService extends BaseService
{
    /**
     * @var QuoteRepository
     */
    protected $userRepository;

    /**
     * 验证规则
     *
     * @var array
     */
    protected $rules = [
        'email' => 'bail|required|string|max:30|unique:users|email',
        'username' => 'bail|required|string|max:20|unique:users',
        'password' => 'bail|required|string|max:32|confirmed',
    ];

    /**
     * 搜索规则
     *
     * @var array
     */
    protected $searchRules = [
        'user_id' => 'integer|exists:users,id',
        'keywords' => 'string|max:20',
        'tags' => 'string|max:30',
        'cate_id' => 'integer',
        'color' => 'string|max:20',
        'font' => 'string|max:20',
        'order_by' => 'in:created_at_desc,created_at_asc,updated_at_desc,updated_at_asc'
    ];

    /**
     * 提示信息
     *
     * @var array
     */
    protected $message = [
        'email.unique' => 'email已经存在',
        'email.required' => 'email不能为空',
        'email.max' => 'email最大长度为12个字符',
        'email.email' => 'email格式错误',
        'username.unique' => '用户名已经存在',
        'username.required' => '用户名不能为空',
        'username.max' => '用户名最大长度为12个字符',
        'password.required' => '密码不能为空',
        'password.max' => '密码长度不能超过16个字符',
        'password.min' => '密码长度不能小于6个字符'
    ];

    /**
     *构造函数
     *
     * UserService constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * 用户注册
     *
     * @param array $data
     * @return mixed
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     *
     * @version  2019/10/9 13:24
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.3.4
     */
    public function store(array $data) 
    {   
        $this->validate($data, $this->rules, $this->message);

        return $this->userRepository->create($data);
    }

    /**
     * 用户登录
     *
     * @param array $data
     * @return array
     * @throws PasswordException
     *
     * @version  2020/1/31 11:12
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.3.4
     */
    public function login(array $data)
    {
        ### 验证用户名
        $rules = [
            'username' => 'bail|required|exists:users',
        ];
        $messages = [
          'username.required' => '用户名不能为空',
          'username.exists' => '用户名不存在'
        ];
        $this->validate($data, $rules, $messages);
        $user = $this->userRepository->findWhere(['username' => $data['username']])->first();

        ### 验证密码
        if ($user) {
            $passwordHash = $user->password;
            $result = Hash::check($data['password'], $passwordHash);
            if (! $result) {
                throw new PasswordException('密码错误');
            }
        }

        ### 登录(生成token)
        $claims = [
            'username' => $user->username,
            'uid' => $user->id,
        ];
        $token = TokenManager::generate($claims);
        $this->userRepository->update(['last_login_time' => time()], $user->id);

        return ['token' => $token];

    }

    /**
     * 获取列表
     *
     * @param array $param
     * @return array
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     *
     * @version  2019/10/9 13:24
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.3.4
     */
    public function list(array $param = [])
    {
        $this->validate($param, $this->searchRules);

        $pagination = $this->userRepository->condition($param)->paginate();
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
     * 获取单条记录
     *
     * @param array $where
     * @param array $columns
     * @return mixed
     *
     * @version  2020/1/31 10:05
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.3.4
     */
    public function detail(array $where, $columns = ['*'])
    {
        $record = $this->userRepository->findWhere($where, $columns)->first();

        return $record;
    }


    /**
     * 格式化返回值
     *
     * @param $record
     * @return mixed
     *
     * @license  PHP Version 7.3.4
     * @version  2019/10/6 10:52
     * @author   jiejia <jiejia2009@gmail.com>
     */
    public function formatReturn($record)
    {
//        $record->tags = explode(',', $record->tags);
//        $record->type = self::TYPE[$record->cate_id];
        return $record;
    }

    /**
     * 更新用户
     *
     * @param array $data
     * @param $id
     * @return mixed
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     *
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.3.4
     * @version  2019/10/7 9:48
     */
    public function update(array $data, $id)
    {
        $user = $this->single($id);
        $rules = $this->rules;

        # 处理数据
        if (isset($data['old_password']) && !empty($data['old_password'])) {

            #验证修改密码
            $rules['old_password'] = [
                'bail',
                'required',
                function ($attribute, $value, $fail) use ($user, $data){
                    if (! Hash::check($data['old_password'], $user->password)) {
                        $fail($attribute.' is invalid.');
                    }
                },
            ];
            $rules['password'] = [
                'bail',
                'required_with:old_password',
                'between:6,20',
                'different:old_password',
                'confirmed '
            ];
            $rules['password_confirmation'] = [
                'bail',
                'required_with:password',
            ];
        }


        # 验证用户名
        $rules['name'] = [
            'bail',
            'between:2,20',
            Rule::unique('users', 'id')->ignore($id),
        ];

        $this->validate($data, $rules, $this->message);

        if (isset($data['old_password']) && !empty($data['old_password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->update($data, $id);
    }
}

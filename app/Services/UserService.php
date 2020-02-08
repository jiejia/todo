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
     * @param array $data
     * @return array
     * @throws PasswordException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.3.4
     * @version  2020/2/1 11:43
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
    public function listOrSearch(array $param = [])
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
     * 用户详情
     *
     * @param array $data
     * @param array $columns
     * @return mixed
     *
     * @version  2020/2/1 11:44
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.3.4
     */
    public function detail(array $data, $columns = ['*'])
    {
        $rules = [
            'id' => 'bail|required|integer|exists:users',
        ];
        $this->validate($data, $rules);

        $where = ['id' => $data['id']];
        $record = $this->userRepository->findWhere($where, $columns)->first();

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
    public function update(array $data)
    {
        $this->validate(['id' => $data['user_id']], ['id' => 'required|bail|integer|exists:users,id']);
        $user = $this->userRepository->find($data['user_id']);

        $rules = [
            //'username' => 'bail|required|string|max:20|unique:users',
            'old_password' => [
                'bail',
                function ($attribute, $value, $fail) use ($user, $data){
                    if (! Hash::check($data['old_password'], $user->password)) {
                        $fail('旧密码错误');
                    }
                },
            ],
            'password' => [
                'bail',
                'required_with:old_password',
                'between:6,20',
                'different:old_password',
                'confirmed'
            ],
            'password_confirmation' => [
                'bail',
                'required_with:password'
            ],
        ];
        $messages = [
            'password.required_with' => '新密码不能为空',
            'password.between' => '新密码应该为6到20个字符',
            'password.different' => '新密码不能和久密码相同',
            'password.confirmed' => '新密码和确认密码不同',
        ];

        $this->validate($data, $rules, $messages);
        return $this->userRepository->update($data, $data['user_id']);
    }
}

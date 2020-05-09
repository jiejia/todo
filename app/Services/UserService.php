<?php
namespace App\Services;

use App\Common\Services\BaseService;
use App\Exceptions\PasswordException;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use App\Common\Utils\TokenManager;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\Repositories\PasswordResetRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Services\Task\CategoryService;
use Illuminate\Validation\Rule;

class UserService extends BaseService
{
    /**
     * @var QuoteRepository
     */
    protected $userRepository;

    protected $categoryServices;

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

        $categoryServices = $this->app->make(CategoryService::class);

        $categoryServices->createOrUpdate(['color' => '#ffccff', 'name' => '默认']);

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
     * @param $data
     * @return array
     * @license  PHP Version 7.3.4
     * @version  2020-3-27 11:57
     * @author   jiejia <jiejia2009@gmail.com>
     */
    public function checkLogin($data)
    {
        $rules = [
            'authorization' => 'bail|required|string|max:200'
        ];
        $this->validate($data, $rules);

        $tokenData = TokenManager::decode($data['authorization']);
        if ($tokenData) {
            $user =  $this->userRepository->find($tokenData['uid']);
            if ($user) {
                return ['login' => 1];
            }
        }

        return ['login' => 0];
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
     * 修改密码
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
    public function changePassword(array $data)
    {
        $this->validate(['id' => $data['id']], ['id' => 'required|bail|integer|exists:users,id']);
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
        return $this->userRepository->update($data, $data['id']);
    }

    /**
     * 更新个人信息
     *
     * @param array $data
     * @return mixed
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     *
     * @version  2020-5-9 14:37
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.2.9
     */
    public function updateProfile(array $data)
    {
        $rules = [
            'id' => 'bail|required|integer|exists:users',
//            'email' => 'bail|required|string|max:30|unique:users|email',
            'nickname' => [
                'bail',
                'between:6,30',
                Rule::unique('users')->ignore($data['id'], 'id')
            ]
        ];
        $messages = [
            'nickname.between' => '昵称长度应该在6到30个字符之间',
            'nickname.unique' => '昵称已存在',
        ];

        $this->validate($data, $rules, $messages);
        return $this->userRepository->update($data, $data['id']);
    }

    /**
     * 更换头像
     *
     * @param array $data
     * @return mixed
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     *
     * @version  2020-5-9 15:26
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.2.9
     */
    public function changeAvatar(array $data)
    {
        $rules = [
            'id' => 'bail|required|integer|exists:users',
            'avatar' => 'bail|required|image|max:2048|mimes:jpeg,gif,png',
        ];
        $messages = [
            'avatar.required' => '上传文件为空',
            'avatar.image' => '上传文件必须为图片',
            'avatar.max' => '上传文件大小应小于2m',
            'avatar.mimes' => '上传文件必须为jpeg,gif,png格式'
        ];

        $this->validate($data, $rules, $messages);
        return $this->userRepository->update($data, $data['id']);
    }

    /**
     * @param array $data
     * @return array
     * @throws \Exception
     * @version  2020/2/11 17:37
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.3.4
     */
    public function sendPasswordEmail(array $data)
    {
        ### 验证用户名
//        dd($data);
        $rules = [
            'email' => 'bail|required|exists:users|email',
        ];
        $messages = [
            'email.required' => '邮箱不能为空',
            'email.exists' => '邮箱不存在',
            'email.email' => '邮箱格式错误'
        ];
        $this->validate($data, $rules, $messages);
        $user = $this->userRepository->findWhere(['email' => $data['email']])->first();

        ### 生成token
        $passwordResetRepository = app()->make(PasswordResetRepository::class);
        $passwordResetRepository->deleteWhere(['email' => $user['email']]);
        $token = hash_hmac('sha256', Str::random(40), config('key'));
        $passwordResetRepository->create( ['email' => $user['email'], 'token' => Hash::make($token), 'created_at' => new Carbon]);

        ### 发送邮件
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF;                      // Enable verbose debug output
            $mail->isSMTP();                                            // Send using SMTP
            $mail->Host       = 'smtp.qq.com';                    // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = '314728819@qq.com';                     // SMTP username
            $mail->Password   = 'xnpguxxmnathbijb';                               // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
            $mail->Port       = 465;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('314728819@qq.com', 'Mailer');
            $mail->addAddress('jiejia2009@gmail.com', $user['username']);     // Add a recipient
            $mail->addReplyTo('todo@qeemeng.com', 'Information');

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Reset Password Notification';
            $mail->Body    = '重置密码链接 <a href="http://localhost:8080/#/resetPassword?token=' . $token . '&email=' . $user['email'] . '">点击修改</a>';
            $mail->AltBody = '60秒后过期';
            $mail->AltBody = '如果您没有请求密码重置，则不需要进一步操作。';

            $mail->send();
        } catch (Exception $e) {
//            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        return [];
    }
}

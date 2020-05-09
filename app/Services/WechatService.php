<?php
namespace App\Services;

use App\Common\Services\BaseService;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\Repositories\PasswordResetRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Common\Wechat\WXBizDataCrypt;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;


class WechatService extends BaseService
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
     * @return array|mixed
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     * @version  2020/2/29 13:24
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.3.4
     */
    public function login2(array $data)
    {
        ### 验证code
        $rules = [
            'code' => 'bail|required|string',
            'signature' => 'bail|required|string',
            'rawData' => 'bail|required|string',
            'iv' => 'bail|required|string',
            'encryptedData' => 'bail|required|string',
        ];
        $this->validate($data, $rules);

        ### 从微信服务器获取 open_id 和 session_key
        $http = new \GuzzleHttp\Client();
        $url = "https://api.weixin.qq.com/sns/jscode2session";
        $response = $http->get($url, ['verify' => false,'query' =>
            [
                'appid' => config('wechat.app_id'),
                'secret' => config('wechat.app_secret'),
                'js_code' => $data['code'],
                'grant_type' => 'authorization_code'
            ]]);
        $content = $response->getBody()->getContents();
        $content = json_decode($content, true);

        ### 验证签名
        $signature = $data['signature'];
        $signature2 = sha1($data['rawData'] . $content['session_key']);
        $this->validate(['signature' => $signature, 'signature2' => $signature2], ['signature' => 'required|same:signature2']);

        ### 第一次登录，生成用户
        $rawData = json_decode($data['rawData'], true);
        $user = $this->userRepository->findWhere(['openid' => $content['openid']]);
        if (! $user) {
            $user = [
                'username' => $rawData['nickName'],
                'openid' => $content['openid'],
            ];
            $this->userRepository->create($user);
        }

        ### 生成第三方登录态
        $crypt = new WXBizDataCrypt(config('wechat.app_id'), $content['session_key']);
        $code = $crypt->decryptData($data['encryptedData'], $data['iv'], $decrypt);
        $sessionId = Hash::make($content['session_key'] . $content['openid'] . config('app.key'));
        $redisKey = 'session_id.' . $content['openid'];
        Redis::set($redisKey, $sessionId);
        Redis::expire($redisKey, 3600 * 24);

        if ($code == 0) {
            $decrypt = json_decode($decrypt, true);
            $decrypt = array_merge($decrypt, ['sessionId' => $sessionId]);
            return $decrypt;
        } else {
            return [];
        }
    }

    /**
     * @param array $data
     * @return array
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     * @version  2020/3/1 16:38
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.3.4
     */
    public function login(array $data)
    {
        $rules = [
            'code' => 'bail|required|string'
        ];
        $this->validate($data, $rules);

        ### 从微信服务器获取 open_id 和 session_key
        $http = new \GuzzleHttp\Client();
        $url = "https://api.weixin.qq.com/sns/jscode2session";
        $response = $http->get($url, ['verify' => false,'query' =>
            [
                'appid' => config('wechat.app_id'),
                'secret' => config('wechat.app_secret'),
                'js_code' => $data['code'],
                'grant_type' => 'authorization_code'
            ]]);
        $content = $response->getBody()->getContents();
        $content = json_decode($content, true);

        ### 第一次登录，生成用户
        $user = $this->userRepository->findWhere(['openid' => $content['openid']]);
        if ($user->isEmpty()) {
            $user = [
                'openid' => $content['openid'],
            ];
            $this->userRepository->create($user);
        }

        ### 生成第三方登录态
        $sessionId = Hash::make($content['session_key'] . $content['openid'] . config('app.key'));
        $redisKey = 'session_id.' . $content['openid'];
        Redis::hset($redisKey, 'session_key', $content['session_key']);
        Redis::hset($redisKey, 'openid', $content['openid']);
        Redis::expire($redisKey, 3600 * 24);

        return ['sessionId' => $sessionId];
    }


    /**
     * 检查session是否过期
     *
     * @param array $data
     * @return array
     * @license  PHP Version 7.3.4
     * @version  2020/3/2 10:38
     * @author   jiejia <jiejia2009@gmail.com>
     */
    public function checkSession(array $data)
    {
        $sessionId = Redis::hget($data['sessionId'], 'openid');

        if ($sessionId) {
            Redis::expire($sessionId, 3600 * 24); // 未过期，续期sessionId
            return ['res' => true];
        } else {
            return ['res' => false];
        }
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
        $passwordResetRepository->create( ['email' => $user['email'], 'token' =>Hash::make($token), 'created_at' => new Carbon]);

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

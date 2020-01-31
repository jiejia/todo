<?php

namespace App\Common\Pojo;


/**
 * Class ResultCode
 * @package  App\Common\Pojo
 *
 * @version  2019年4月2日
 * @author   jiejia <jiejia2009@gmail.com>
 * @license  PHP Version 7.2.10
 *
 */
//class ResultCode extends SplEnum
class ResultCode
{
    const OK = ['code' => 0, 'msg' => '操作成功'];

    const UNAUTHORIZED = ['code' => 401, 'msg' => '没有权限'];

    const UNKNOWN_ERROR = ['code' => 999, 'msg' => '未知错误'];

    const HTTP_METHOD_NOT_ALLOWED_ERROR = ['code' => 405, 'msg' => 'HTTP方法错误'];

    const NEED_PERMISSION = ['code' => 1000, 'msg' => '需要权限'];

    const URI_NOT_FOUND = ['code' => 1001, 'msg' => '资源不存在'];

    const MISSING_ARGS = ['code' => 1002, 'msg' => '参数不全'];

    const IMAGE_TOO_LARGE = ['code' => 1003, 'msg' => '上传的图片太大'];

    const HAS_BAN_WORD = ['code' => 1004, 'msg' => '输入有违禁词'];

    const INPUT_TOO_SHORT = ['code' => 1005, 'msg' => '输入为空，或者输入字数不够'];

    const TARGET_NOT_FOUND = ['code' => 1006, 'msg' => '相关的对象不存在'];

    const NEED_CAPTCHA = ['code' => 1007, 'msg' => '需要验证码，验证码有误'];

    const IMAGE_UNKNOWN = ['code' => 1008, 'msg' => '不支持的图片格式'];

    const IMAGE_WRONG_FORMAT = ['code' => 1009, 'msg' => '照片格式有误(仅支持JPG,JPEG,GIF,PNG或BMP)'];

    const IMAGE_WRONG_CK = ['code' => 1010, 'msg' => '访问私有图片ck验证错误'];

    const IMAGE_CK_EXPIRED = ['code' => 1011, 'msg' => '访问私有图片ck过期'];

    const TITLE_MISSING = ['code' => 1012, 'msg' => '题目为空'];

    const DESC_MISSING = ['code' => 1013, 'msg' => '描述为空'];

    const USER_INFO_NOT_FOUND = ['code' => 1014, 'msg' => '用户信息不存在'];

    const WRONG_PHONE = ['code' => 1015, 'msg' => '手机号有误'];

    const CONFIRM_WRONG_PASSWORD = ['code' => 1016, 'msg' => '两次输入的安全密码不一致'];

    const COMMON_ERROR = ['code' => 1017, 'msg' => '系统错误'];

    const DATA_FORMAT_ERROR = ['code' => 1018, 'msg' => '请求数据格式错误'];

    const LOGIN_ERROR = ['code' => 1019, 'msg' => '登录错误'];

    const TOKEN_NOT_FOUND = ['code' => 1020, 'msg' => 'TOKEN不存在'];

    const OBTAIN_PERMISSION_ERROR = ['code' => 1021, 'msg' => '获取权限出错'];

    const USER_NAME_EXISTS = ['code' => 1022, 'msg' => '用户信息已经存在'];

    const UPDATE_DATA_ERROR = ['code' => 1023, 'msg' => '数据更新错误'];

    const SMS_SEND_SUCCESS = ['code' => 1024, 'msg' => '短信发送成功'];

    const SMS_SNED_FAILURE = ['code' => 1025, 'msg' => '短信发送失败'];

    const SMS_VERIFY_SUCCESS = ['code' => 1026, 'msg' => '短信验证成功'];

    const SMS_VERIFY_FAILURE = ['code' => 1027, 'msg' => '短信验证失败'];

    const USER_NAME_EXIST = ['code' => 1028, 'msg' => '用户名已存在'];

    const PARAMETER_VALIDATION_ERROR = ['code' => 1029, 'msg' => '参数验证错误'];

    static function getResultCode(int $code)
    {
        $reflectionClass = new \ReflectionClass(self::class);
        $constants = $reflectionClass->getConstants();
        foreach ($constants as $constant) {
            if ((int)$constant['code'] === $code) {
                return $constant;
            }
        }
        return $constants['COMMON_ERROR'];
    }
}

<?php
namespace App\Common\Utils;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;
use Exception;

class TokenManager
{
    private const ISSUER = 'http://todo.test';

    private const AUDIENCE = 'http://todo.test';

    private const EXPIRES_TIME = 60 * 60 * 24;

    private const KEY = 'lkCDEE2DGJsr1jWDStHnNUvLLQuzinC3';

    /**
     * 生成token
     *
     * @param array $claims
     * @param int $expires
     * @return string
     *
     * @version  2020/1/31 11:12
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.3.4
     */
    public static function generate(array $claims, int $expires = 0)
    {
        $time = time();
        $expires = $time + ($expires != 0 ? $expires : self::EXPIRES_TIME);
        //$id = md5($claims['id'] . $claims['username'] . $time);

        $token = (new Builder())
//                                ->issuedBy(self::ISSUER) // Configures the issuer (iss claim)
//                                ->permittedFor(self::AUDIENCE) // Configures the audience (aud claim)
                                //->identifiedBy($id, true) // Configures the id (jti claim), replicating as a header item
//                                ->issuedAt(strtotime('2019-12-12')) // Configures the time that the token was issue (iat claim)
                                ->canOnlyBeUsedAfter($time) // Configures the time that the token can be used (nbf claim)
                                ->expiresAt($expires); // Configures the expiration time of the token (exp claim)

        if (count($claims) > 0) {
            foreach ($claims as $k => $v) {
                $token->withClaim($k, $v);
            }
        }

        $token = $token->getToken(new Sha256(), new Key(self::KEY));

        return (string)$token;
    }

    /**
     * 解密token
     *
     * @param $token
     * @return array|bool
     *
     * @license  PHP Version 7.3.4
     * @version  2020/1/31 11:12
     * @author   jiejia <jiejia2009@gmail.com>
     */
    public static function decode($token)
    {
        try {
            $token = (new Parser())->parse((string) $token); // Parses from a string

            ### 验证 key
            $signer = new Sha256();
            if (! $token->verify($signer, self::KEY)) {
                return false;
            }

            ### 验证是否过期
            $time = time();
            $expire = $token->getClaim('exp');
            if ($time > $expire) {
                return false;
            }

            ### 获取token数据
            $id = $token->getClaim('uid');
            $username = $token->getClaim('username');
            return ['uid' => $id, 'username' => $username];

        } catch (Exception $e) {

        }
        return false;
    }
}

<?php
namespace App\Http\Middleware;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Routing\Middleware\ThrottleRequests;

class RateLimits extends ThrottleRequests
{
    protected function resolveRequestSignature($request)
    {
        //用户唯一性通过方法、路径、ip、用户token通过sha1方式加密
        return sha1(implode('|', [
                $request->method(),
                $request->root(),
                $request->path(),
                $request->ip(),
                $request->header('X-Access-Token')
            ]
        ));
    }

    protected function buildResponse($key, $maxAttempts)
    {
        //超时返回429
        $response = new Response('Too frequent access.', 429);
        $retryAfter = $this->limiter->availableIn($key);
        return $this->addHeaders(
            $response, $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts, $retryAfter,1),
            $retryAfter
        );
    }
}

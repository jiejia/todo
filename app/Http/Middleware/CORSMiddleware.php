<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 处理AJAX跨域问题
 *
 * Class CORSMiddleware
 * @package app\Common\Middleware
 */
class CORSMiddleware
{
    /**
     * @var
     */
    private $headers;

    /**
     * @var
     */
    private $allow_origin;

    /**
     * @param Request  $request
     * @param \Closure $next
     *
     * @return Response|mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $this->headers = [
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => $request->header('Access-Control-Request-Headers'),
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age' => 1728000
        ];

        $this->allow_origin = config('cors_domain');

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if (!empty($origin) && !\in_array(trim(str_replace('X_Requested_With: XMLHttpRequest', '', $origin)), $this->allow_origin, true)) {
            return new Response('Forbidden', 403);
        }

        if ($request->isMethod('OPTIONS')) {
            return $this->setCORSHeaders(new Response('OK', 200), $origin);
        }

        $response = $next($request);
        $methodVariable = array($response, 'header');

        if (\is_callable($methodVariable, false, $callable_name)) {
            return $this->setCORSHeaders($response, $origin);
        }
        return $response;
    }

    /**
     * @param Response $response
     * @param string   $origin
     *
     * @return Response
     */
    private function setCORSHeaders(Response $response, string $origin): Response
    {
        foreach ($this->headers as $key => $value) {
            $response->header($key, $value);
        }
        if (\in_array($origin, $this->allow_origin, true)) {
            $response->header('Access-Control-Allow-Origin', $origin);
        } else {
            $response->header('Access-Control-Allow-Origin', '');
        }
        return $response;
    }
}

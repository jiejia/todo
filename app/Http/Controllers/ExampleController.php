<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index()
    {
        Redis::hset('test', 'test1', 1);
        Redis::hset('test', 'test2', 2);

        echo Redis::hget('test', 'test2');
    }

    //
}

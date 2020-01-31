<?php

namespace App\Common\Pojo;

/**
 * Class StatusCode
 * @package  App\Contents\Pojo
 *
 * @version  2019年4月11日
 * @author   jiejia <jiejia2009@gmail.com>
 * @license  PHP Version 7.2.10
 *
 */

//class StatusCode extends SplEnum
class StatusCode
{
    const DELETED = [
      'yes' => 1,
      'no' => 0
    ];

    const IS_DELETED = 1;
    const IS_NOT_DELETED = 0;

    const IS_DISABLED = 1;
    const IS_NOT_DISABLED = 0;

}
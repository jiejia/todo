<?php
namespace App\Common\Utils;

class MessageManager
{
    public static function getValidateErrors(array $errors)
    {
        $return = [];
        foreach ($errors as $k => $v) {
            $return [$k]= $v[0];
        }
        return $return;
    }
}

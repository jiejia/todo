<?php
namespace App\Common\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BaseService
{
    private $validator;

        /**
     * 默认的自定义属性
     *
     * @var array
     */
    private static $message = [
//        'required' => ':attribute 必填',
//        'min' => ':attribute 的最小值应该为 :min',
//        'max' => ':attribute 的最大值应该为 :max',
//        'between' => ':attribute 应该在 :min - :max 之间',
//        'same' => ':attribute 和属性 :other 必须一致.',
//        'size' => ':attribute 必须为 :size.',
//        'in' => ':attribute 是下面的值之一: :values',
//        'exists' => ':attribute 不存在',
//        'unique' => ':attribute 必须为唯一',
//        'boolean' => ':attribute 必须为bool类型',
//        'json' => ':attribute 必须为json格式',
//        'string' => ':attribute 必须为字符串类型',
//        'date' => ':attribute 必须为日期时间格式',
//        'array' => ':attribute 必须为array类型',
//        'numeric' => ':attribute 必须为数字',
//        'integer' => ':attribute 必须为整数'
    ];

    protected function validate(array $data, array $rules, array $messages = [], array $customAttributes = []): array
    {
        $this->validator = Validator::make($data, $rules, array_merge(self::$message, $messages), $customAttributes);
        if ($this->validator->fails()) {
            $this->throwValidationException($this->validator);
        }

        return $this->extractInputFromRules($data, $rules);
    }

    private function throwValidationException($validator): void
    {
        throw new ValidationException($validator);
    }

    private function extractInputFromRules(array $data, array $rules): array
    {
        return $this->only(collect($rules)->keys()->map(function ($rule) {
            return Str::contains($rule, '.') ? explode('.', $rule)[0] : $rule;
        })->unique()->toArray(), $data);
    }

    private function only(array $keys, array $input): array
    {
        $results = [];
        $placeholder = new \stdClass;
        foreach (\is_array($keys) ? $keys : \func_get_args() as $key) {
            $value = data_get($input, $key, $placeholder);
            if ($value !== $placeholder) {
                Arr::set($results, $key, $value);
            }
        }
        return $results;
    }
}

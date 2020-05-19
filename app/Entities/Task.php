<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{

    use SoftDeletes;

    /**
     * 指示是否自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'content', 'deadline', 'category_id', 'status', 'user_id', 'tags'
    ];

    /**
     *  模型的默认属性值。
     *
     * @var array
     */
    protected $attributes = [
        'status' => 0,
        'deadline' => 0
    ];

    /**
     * 模型日期列的存储格式。
     *
     * @var string
     */
    protected $dateFormat = 'U';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];

    public function setDeadlineAttribute($value)
    {
        if (empty($value)) {
            $value = time();
        }
        $this->attributes['deadline'] = $value;
    }

    public function getCategoryIdAttribute($value)
    {
        return (int)$value;
    }

    public function getCreatedAtAttribute($value)
    {
        return (int)$value;
    }

    public function getUpdatedAtAttribute($value)
    {
        return (int)$value;
    }

    public function getDeadlineAttribute($value)
    {
        return (int)$value;
    }

    public function getUserIdAttribute($value)
    {
        return (int)$value;
    }
}

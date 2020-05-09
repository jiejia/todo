<?php

namespace App\Entities\Task;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Entities\Task;

class Category extends Model
{

    use SoftDeletes;

    public $table = 'task_category';

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
        'name', 'user_id', 'status', 'color'
    ];

    /**
     *  模型的默认属性值。
     *
     * @var array
     */
    protected $attributes = [
        'status' => 0,

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

    public function getCreatedAtAttribute($value)
    {
        return (int)$value;
    }

    public function getUpdatedAtAttribute($value)
    {
        return (int)$value;
    }

    public function getUserIdAttribute($value)
    {
        return (int)$value;
    }

    public function setColorAttribute($value)
    {
        $this->attributes['color'] = $value;
    }

    public function tasks():HasMany
    {
        return $this->hasMany(Task::class, 'category_id', 'id');
    }

}

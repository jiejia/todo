<?php
namespace App\Repositories;

use Demo\Annotation\Deprecated;
use Prettus\Repository\Eloquent\BaseRepository;

class Repository extends BaseRepository
{

    public function model(): string{}

    protected function applyConditions(array $where)
    {
        foreach ($where as $field => $value) {
            if (is_array($value)) {
                list($field, $condition, $val) = $value;
                if ($condition == 'in') {
                    $this->model = $this->model->whereIn($field, $val);
                } else {
                    $this->model = $this->model->where($field, $condition, $val);
                }
            } else {
                $this->model = $this->model->where($field, '=', $value);
            }
        }
    }
}

<?php
namespace App\Repositories\Task;

use Demo\Annotation\Deprecated;
use Prettus\Repository\Eloquent\BaseRepository;
use App\Entities\Task\Category;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class CategoryRepository extends BaseRepository
{
    /**
     * @return string
     * @see    PHP 7.3.29
     * @author jiejia <jiejia2009@gmail.com>
     */
    public function model(): string
    {
        return Category::class;
    }


    public function condition(array $param)
    {
        return $this->pushCriteria(new class($param) implements CriteriaInterface
        {
            private $param;

            public function __construct(array $param)
            {
                $this->param = $param;
            }

            public function apply($model, RepositoryInterface $repository)
            {
                $data = $this->param;

                // 关键词
                if (isset($data['s']) && !empty($data['s'])) {
                    $data['s'] = addslashes($data['s']);
                    $model = $model->WhereRaw("POSITION('{$data['s']}' IN `name`)");
                }

                // 状态
                if (isset($data['status']) && !empty($data['status'])) {
                    $model = $model->where('status', '=', $data['status']);
                }

                // 所属用户
                if (isset($data['user_id']) && !empty($data['user_id'])) {
                    $model = $model->where('user_id', '=', $data['user_id']);
                }

                if (isset($data['order_by']) && !empty($data['order_by'])) {
                    switch ($data['order_by']) {
                        case 'created_at_desc':
                            $model = $model->orderBy('created_at', 'desc');
                            break;

                        case 'created_at_asc':
                            $model = $model->orderBy('created_at', 'asc');
                            break;

                        case 'updated_at_desc':
                            $model = $model->orderBy('updated_at', 'desc');
                            break;

                        case 'updated_at_asc':
                            $model = $model->orderBy('updated_at', 'asc');
                            break;
                        default:
                            $model = $model->orderBy('created_at', 'asc');
                            break;
                    }
                } else {
                    $model->orderBy('created_at', 'desc');
                }

                return $model;
            }
        });
    }
}

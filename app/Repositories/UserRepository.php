<?php
namespace App\Repositories;

use Demo\Annotation\Deprecated;
use Prettus\Repository\Eloquent\BaseRepository;
use App\Entities\User;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Exceptions\RepositoryException;

class UserRepository extends BaseRepository
{
    /**
     * 模型
     *
     * @return string
     * @see    PHP 7.3.29
     * @author jiejia <jiejia2009@gmail.com>
     */
    public function model(): string
    {
        return User::class;
    }

    /**
     * 搜索条件
     *
     * @param array $param
     * @return UserRepository
     * @throws RepositoryException
     *
     * @version  2019/10/7 8:45
     * @author   jiejia <jiejia2009@gmail.com>
     * @license  PHP Version 7.3.4
     */
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
                if (isset($data['keywords']) && !empty($data['keywords'])) {
                    $model = $model->WhereRaw("POSITION('{$data['keywords']}' IN `name`)");
                    $model = $model->orWhereRaw("POSITION('{$data['keywords']}' IN `signature`)");

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
                    }
                } else {
                    $model->orderBy('created_at', 'desc');
                }

                return $model;
            }
        });
    }
}

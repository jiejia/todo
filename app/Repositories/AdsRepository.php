<?php
namespace App\Repositories;

use App\Entities\AdsModel;
use Demo\Annotation\Deprecated;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;
use Prettus\Repository\Exceptions\RepositoryException;

class AdsRepository extends BaseRepository
{
    /**
     * @return string
     * @see    PHP 7.3.29
     * @author jiejia <jiejia2009@gmail.com>
     */
    public function model(): string
    {
        return AdsModel::class;
    }

    /**
     * 搜索条件
     *
     * @param array $param
     * @return QuoteRepository
     * @throws RepositoryException
     *
     * @version  2019/10/9 13:22
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
                if (isset($data['s']) && !empty($data['s'])) {
                    $data['s'] = addslashes($data['s']);
                    $model = $model->WhereRaw("POSITION('{$data['s']}' IN `content`)");
                }

                // 作者
                if (isset($data['author']) && !empty($data['author'])) {
                    $data['author'] = addslashes($data['author']);
                    $model = $model->WhereRaw("POSITION('{$data['author']}' IN `author`)");
                }

                // 来自
                if (isset($data['from']) && !empty($data['from'])) {
                    $data['from'] = addslashes($data['from']);
                    $model = $model->WhereRaw("POSITION('{$data['from']}' IN `quote_from`)");
                }

                // tag
                if (isset($data['tag']) && !empty($data['tag'])) {
                    $data['tag'] = addslashes($data['tag']);
                    $model = $model->WhereRaw("POSITION('{$data['tag']}' IN `tags`)");
                }

                // 类型
                if (isset($data['type']) && !empty($data['type'])) {
                    $model = $model->where('cate_id', '=', $data['type']);
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

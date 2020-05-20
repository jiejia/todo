<?php
namespace App\Repositories;

use Demo\Annotation\Deprecated;
use Prettus\Repository\Eloquent\BaseRepository;
use App\Entities\PasswordReset;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

class PasswordResetRepository extends BaseRepository
{
    /**
     * @return string
     * @see    PHP 7.3.29
     * @author jiejia <jiejia2009@gmail.com>
     */
    public function model(): string
    {
        return PasswordReset::class;
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

                return $model;
            }
        });
    }
}

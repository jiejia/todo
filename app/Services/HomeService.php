<?php
namespace App\Services;

use App\Common\BaseService;
use App\Services\QuoteService;

class HomeService extends BaseService
{
    protected $quoteService;

    public function __construct(QuoteService $quoteService)
    {
        $this->quoteService = $quoteService;
    }

    public function list(array $param)
    {
        return $this->quoteService->list($param);
    }
}

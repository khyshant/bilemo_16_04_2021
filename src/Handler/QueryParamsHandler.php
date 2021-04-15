<?php

namespace App\Handler;

use Symfony\Component\HttpFoundation\RequestStack;

class QueryParamsHandler extends AbstractHandler
{
    /**
     * @var RequestStack
     */
    private $request;
    /**
     * @param RequestStack $request
     */
    public function __construct(RequestStack $request)
    {
        $this->request = $request->getCurrentRequest();;
    }

    public function getLimit() {
        $queryParams = $this->request->query;
        $limit = (int)$queryParams->get('limit');
        if($limit<=20){
            $limit=1;
        }
        return $limit;
    }

    public function getOffset() {
        $queryParams = $this->request->query;
        $offset = (int)$queryParams->get('offset');
        if($offset==0){
            $offset=1;
        }
        return $offset;
    }
}
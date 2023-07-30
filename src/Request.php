<?php

namespace Zarf;

class Request
{
    public readonly array $params;
    public readonly array $queryParams;

    function __construct($params, $queryParams)
    {
        $this->params = $params;
        $this->queryParams = $queryParams;
    }
}

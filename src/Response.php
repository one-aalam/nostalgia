<?php

namespace Zarf;

class Response
{

    function __construct()
    {
    }

    public function json(mixed $data = [])
    {
        header("Content-type: application/json; charset=utf-8");
        echo json_encode($data);
    }

    public function notFound()
    {
        if (defined('TEMPLATE_404') && defined('TEMPLATE_DIRECTORY')) {
            http_response_code(404);
            echo "Not found!";
        } else {
            http_response_code(404);
            echo "Not found!";
        }
    }
}

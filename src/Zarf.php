<?php

namespace Zarf;

class Zarf extends Router
{
    function __construct()
    {
        parent::__construct();
    }

    function run(bool $debug = false)
    {
        if ($debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        }

        $uri = $_SERVER['REQUEST_URI'];
        $httpMethod = $_SERVER['REQUEST_METHOD'];

        $parsedUrl = parse_url($uri);
        $response = new Response;


        if (!isset($this->routes[$httpMethod])) {
            return $response->notFound();
        }


        foreach ($this->routes[$httpMethod] as $route) {
            if (!isset($route['type'])) {
                continue;
            }

            switch ($route['type']) {
                case 'function':
                    preg_match($route['matcher'], $parsedUrl['path'], $matches);
                    if (!count($matches)) {
                        break;
                    }
                    $params = [];
                    foreach ($route['vars'] as $index => $val) {
                        $match = $matches[$index + 1];
                        if ($match) {
                            if (strpos($val, '_') !== false) {
                                $matchParts = strpos($match, '-') !== false ? explode('-', $match) : (strpos($match, '.') !== false ? explode('.', $match) : [$match]);
                                $valParts = explode('_', $val);
                                if (count($valParts) === count($matchParts)) {
                                    foreach ($valParts as $index => $val) {
                                        $params[$val] = $matchParts[$index];
                                    }
                                }
                            } else {
                                $params[$val] = urldecode(
                                    strpos($match, $val . ':') === 0 ? str_replace($val . ':', '', $match) : (strpos($match, '.') !== false ? substr($match, strrpos($match, '.') + 1) : $match)
                                );
                            }
                        }
                    }

                    if (isset($parsedUrl['query'])) {
                        parse_str($parsedUrl['query'], $queryParams);
                    }

                    $request = new Request($params, $queryParams ?? []);

                    return $route['handler']($request, $response);
            }
        }

        return $response->notFound();
    }
}

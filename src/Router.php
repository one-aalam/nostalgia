<?php

namespace Zarf;

enum HttpMethod: string
{
    case Get = 'GET';
    case Post = 'POST';
    case Put = 'PUT';
    case Delete = 'DELETE';
};

const CONTROLLER_HTTP_METHODS = [
    'index' => HttpMethod::Get,
    'detail' => HttpMethod::Get,
    'create' => HttpMethod::Post,
    'update' => HttpMethod::Put,
    'delete' => HttpMethod::Delete,
];

abstract class Router
{

    protected function __construct(protected string $baseUrl = '', protected array $routes = [])
    {
    }

    function register(string $path, callable $handler, HttpMethod $httpMethod = HttpMethod::Get): Router
    {
        $baseUrl = $this->baseUrl ?: '';
        if (!empty($this->baseUrl)) {
            $path = $path != '/' ? $path : '';
        }

        $middlewares = [];
        [$regExp, $vars] = $this->getPathMeta($path);

        if (!array_key_exists($httpMethod->value, $this->routes)) {
            $this->routes[$httpMethod->value] = [];
        }

        $this->routes[$httpMethod->value][] = [
            'path' => $baseUrl . $path,
            'type' => 'function',
            'matcher' => '/^\/' . $regExp . '$/',
            'vars' => $vars,
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];

        // $this->lastAddedVerb = $method;
        return $this;
    }

    function controller(string $path, $ClsName)
    {
        $controller = (new Resolver)->resolve($ClsName);
        if ($controller instanceof Controller) {
            foreach (CONTROLLER_HTTP_METHODS as $controllerMethod => $httpVerb) {
                if (method_exists($controller, $controllerMethod)) {
                    $this->register($this->getPath($path, $controllerMethod), function (Request $request, Response $response) use ($controller, $controllerMethod) {
                        $controller->{$controllerMethod}($request, $response);
                    }, $httpVerb);
                }
            }
        }
    }

    private function getPath(string $path, string $controllerMethod)
    {
        return in_array($controllerMethod, ['detail', 'update', 'delete']) ? "{$path}/:id" : $path;
    }

    function get(string $path, callable $handler): Router
    {
        $this->register($path, $handler);
        return $this;
    }

    function post(string $path, callable $handler): Router
    {
        $this->register($path, $handler, HttpMethod::Post);
        return $this;
    }

    function put(string $path, callable $handler): Router
    {
        $this->register($path, $handler, HttpMethod::Put);
        return $this;
    }

    function del(string $path, callable $handler): Router
    {
        $this->register($path, $handler, HttpMethod::Delete);
        return $this;
    }

    private function getPathMeta(string $path): array
    {
        $parts = array_filter(explode("/", $path), function ($part) {
            return $part !== "";
        });
        $vars = [];
        $regExpParts = array_map(function ($part) use (&$vars) {
            if ($part[0] === ':') {
                if ($part[strlen($part) - 1] === '?') {
                    $part = substr($part, 0, -1);
                    $vars[] = substr($part, 1);
                    return '([a-zA-Z0-9_-]*)';
                } elseif (strpos($part, '.') !== false) {
                    $subParts = explode('.', $part);
                    if ($subParts[1][0] === ':') {
                        $vars[] = substr($subParts[0], 1) . '_' . substr($subParts[1], 1);
                        return '([a-zA-Z0-9_-]+.[a-zA-Z0-9_-]+)';
                    } else {
                        $vars[] = $subParts[0];
                        return '([a-zA-Z0-9_-]+.' . substr($subParts[1], 1) . ')';
                    }
                } elseif (strpos($part, '-') !== false) {
                    $subParts = explode('-', $part);
                    if ($subParts[1][0] === ':') {
                        $vars[] = substr($subParts[0], 1) . '_' . substr($subParts[1], 1);
                        return '([a-zA-Z0-9_-]+-[a-zA-Z0-9_-]+)';
                    } else {
                        $vars[] = substr($subParts[0], 1);
                        return '([a-zA-Z0-9_-]+-' . $subParts[1] . ')';
                    }
                } else {
                    $vars[] = substr($part, 1);
                    return '([a-zA-Z0-9_-]+)';
                }
            } elseif ($part[0] === '*') {
                $vars[] = substr($part, 1);
                return '(.*)';
            } elseif (strpos($part, '.') !== false) {
                $subParts = explode('.', $part);
                $vars[] = substr($subParts[1], 1);
                return '(' . $subParts[0] . '.[a-zA-Z0-9_-]+)';
            } elseif (strpos($part, '::') !== false) {
                $subParts = explode('::', $part);
                $vars[] = $subParts[0];
                return '(' . $subParts[0] . ':[a-zA-Z0-9_-]+)';
            } else {
                return $part;
            }
        }, $parts);

        $regExp = implode("\/", $regExpParts);
        return [$regExp, $vars];
    }



    public function getRoutes(): array
    {
        return !empty($this->routes && is_array($this->routes)) ?
            $this->routes : [];
    }
}

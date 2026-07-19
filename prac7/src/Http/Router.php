<?php
declare(strict_types=1);

namespace App\Http;

final class Route
{
    /**
     * @param string|array{0:class-string,1:string} $handler
     */
    public function __construct(
        public string $method,
        public string $path,
        public $handler
    ) {
    }
}

final class Router
{
    /** @var Route[] */
    private array $routes = [];

    /**
     * @param string|array{0:class-string,1:string} $handler
     */
    public function add(string $method, string $path, $handler): void
    {
        $this->routes[] = new Route(strtoupper($method), $path, $handler);
    }

    /**
     * @param string|array{0:class-string,1:string} $handler
     */
    public function get(string $path, $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    /**
     * @param string|array{0:class-string,1:string} $handler
     */
    public function post(string $path, $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    /**
     * @return array{handler:string,params:array}|null
     */
    public function match(string $method, string $path): ?array
    {
        $method = strtoupper($method);
        $path = '/' . trim($path, '/');

        foreach ($this->routes as $route) {
            if ($route->method !== $method && $route->method !== 'ANY') {
                continue;
            }

            $params = $this->matchPath($route->path, $path);
            if ($params !== null) {
                return ['handler' => $route->handler, 'params' => $params];
            }
        }

        return null;
    }

    /**
     * @return array<string,string>|null
     */
    private function matchPath(string $pattern, string $path): ?array
    {
        $pattern = '/' . trim($pattern, '/');
        $patternSegments = explode('/', $pattern);
        $pathSegments = explode('/', $path);

        if (count($patternSegments) !== count($pathSegments)) {
            return null;
        }

        $params = [];
        foreach ($patternSegments as $index => $segment) {
            if (str_starts_with($segment, '{') && str_ends_with($segment, '}')) {
                $name = trim($segment, '{}');
                $params[$name] = $pathSegments[$index];
                continue;
            }
            if ($segment !== $pathSegments[$index]) {
                return null;
            }
        }

        return $params;
    }
}

<?php
namespace Mopsis\Core;

use Aura\Web\Request;
use Psr\Log\LoggerInterface as Logger;

class Router
{
    protected $logger;

    protected $request;

    protected $route;

    public function __construct(Logger $logger, Request $request)
    {
        $this->logger  = $logger;
        $this->request = $request;
    }

    public function get()
    {
        $requestMethod = $this->request->method->get();
        $this->route   = urldecode($this->request->url->get(PHP_URL_PATH));

        if ($this->route === '/') {
            $this->route = '/home';
        }

        $validRules = '/^(' . $requestMethod . '|\*)\s+(?<path>\/(?:\{[^}]+\}|' . preg_quote(preg_replace('/^\/([^\/]+).*/', '$1', $this->route), '/') . ')\S*)\s+(.+)\n?$/i';

        foreach (preg_grep($validRules, file(APPLICATION_PATH . '/config/routes')) as $line) {
            if (!preg_match($validRules, $line, $rule)) {
                throw new \Exception('invalid route: ' . $line);
            }

            $test = preg_replace('/\\\{(.+?)\\\}/', '(?<$1>[^\/]*)', preg_quote($rule['path'], '/'));

            if (!preg_match('/^' . $test . '(?:\/(?<params>.*))?/', rtrim($this->route, '/'), $m)) {
                $this->logger->debug('Path "' . $this->route . '" does not match "' . $rule['path'] . '"');
                continue;
            }

            $m['method'] = $requestMethod;
            $class       = $this->getClass($rule[3], $m);

            if ($class === false) {
                continue;
            }

            $reflectionClass = new \ReflectionClass($class);
            $funcArgs        = $this->getFunctionArguments($reflectionClass->getMethod('__invoke'), $m);

            if ($funcArgs === false) {
                continue;
            }

            $this->logger->debug($this->route . ' ==> ' . $class);

            return App::get($class)->__invoke(...$funcArgs);
        }

        return false;
    }

    protected function getClass($path, $m)
    {
        $path = preg_replace_callback('/\{(.+?)\}/', function ($placeholder) use ($m) {
            return studly_case($m[$placeholder[1]]);
        }, $path);

        if (!preg_match('/^\w+\\\\\w+$/', $path)) {
            $this->logger->debug('Cannot parse "' . $path . '"');

            return false;
        }

        try {
            return App::build('Action', $path);
        } catch (\DomainException $e) {
            $this->logger->debug($path . ' => ' . $e->getMessage() . ' [' . $this->route . ']');

            return false;
        }
    }

    protected function getFunctionArguments(\ReflectionMethod $method, $m)
    {
        $m['params'] = isset($m['params']) ? explode('/', $m['params']) : [];
        $funcArgs    = [];

        foreach ($method->getParameters() as $param) {
            if ($param->isVariadic()) {
                return array_merge($funcArgs, $m['params']);
            }

            if (isset($m[$param->name])) {
                $funcArgs[] = urldecode($m[$param->name]);
            } elseif (count($m['params'])) {
                $funcArgs[] = urldecode(array_shift($m['params']));
            } elseif (!$param->isOptional()) {
                $this->logger->debug($method->name . ' => missing parameter "' . $param->name . '" [' . $this->route . ']');

                return false;
            }
        }

        if (count($m['params'])) {
            $this->logger->debug($method->name . ' => unexpected parameters "' . implode('", "', $m['params']) . '" [' . $this->route . ']');

            return false;
        }

        return $funcArgs;
    }
}

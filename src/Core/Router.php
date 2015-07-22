<?php namespace Mopsis\Core;

class Router
{
	protected $logger;
	protected $request;

	public function __construct(\Psr\Log\LoggerInterface $logger, \Aura\Web\Request $request)
	{
		$this->logger = $logger;
		$this->request = $request;
	}

	public function get()
	{
		$requestMethod = $this->request->method->get();
		$route         = urldecode($this->request->url->get(PHP_URL_PATH));

		if ($route === '/') {
			$route = '/home';
		}

		$validRules = '/^(' . $requestMethod . '|\*)\s+(?<path>\/(?:\{[^}]+\}|' . preg_quote(preg_replace('/^\/([^\/]+).*/', '$1', $route), '/') . ')\S*)\s+(.+)\n?$/i';

		foreach (preg_grep($validRules, file('config/routes')) as $line) {
			if (!preg_match($validRules, $line, $rule)) {
				throw new \Exception('invalid route: ' . $line);
			}

			$test = preg_replace('/\\\{(.+?)\\\}/', '(?<$1>[^\/]*)', preg_quote($rule['path'], '/'));
			if (!preg_match('/^' . $test . '(?:\/(?<params>.*))?/', rtrim($route, '/'), $m)) {
				$this->logger->debug($rule[3] . ' => path does not match [' . $route . ']');
				continue;
			}

			$m['method'] = $requestMethod;

			list($class, $method) = explode('::', 'App\\' . preg_replace_callback('/\{(.+?)\}/', function ($n) use ($m) {
				return camelCase($m[$n[1]]);
			}, $rule[3]));

			if (!class_exists($class)) {
				$this->logger->debug($rule[3] . ' => class "' . $class . '" not found [' . $route . ']');
				continue;
			}

			$reflectionClass = new \ReflectionClass($class);

			if (!$reflectionClass->hasMethod($method)) {
				$this->logger->debug($rule[3] . ' => method "' . $class . '->' . $method . '" not found [' . $route . ']');
				continue;
			}

			$funcArgs = $this->getFunctionArguments($reflectionClass->getMethod($method), $m);

			if ($funcArgs === false) {
				continue;
			}

			$this->logger->debug($route . ' ==> ' . $class . '->' . $method);
			return App::make($class)->__invoke($method, $funcArgs);
		}

		return false;
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
				$this->logger->debug($method->name . ' => missing parameter "' . $param->name . '" [' . $route . ']');

				return false;
			}
		}

		if (count($m['params'])) {
			$this->logger->debug($method->name . ' => unexpected parameters "' . implode('", "', $m['params']) . '" [' . $route . ']');

			return false;
		}

		return $funcArgs;
	}
}

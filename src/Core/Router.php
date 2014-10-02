<?php namespace Mopsis\Core;

class Router
{
	private $_logger;

	public function __construct(\Psr\Log\LoggerInterface $logger)
	{
		$this->_logger = $logger;
	}

	public function get($requestMethod, $route)
	{
		$validRules = '/^('.$requestMethod.'|\*)\s+(?<path>\/(?:\{[^}]+\}|'.preg_quote(preg_replace('/^\/([^\/]+).*/', '$1', $route), '/').')\S*)\s+(.+)\n?$/i';

		foreach (preg_grep($validRules, file('config/routes')) as $line) {
			if (!preg_match($validRules, $line, $rule)) {
				throw new \Exception('invalid route: '.$line);
			}

			$test = preg_replace('/\\\{(.+?)\\\}/', '(?<$1>[^\/]*)', preg_quote($rule['path'], '/'));
			if (!preg_match('/^'.$test.'(?:\/(?<params>.*))?/', rtrim($route, '/'), $m)) {
				$this->_logger->info($rule[3].' => path do not match ['.$route.']');
				continue;
			}

			$m['method'] = $requestMethod;

			list($controller, $method) = explode('.', preg_replace_callback('/\{(.+?)\}/', function ($n) use ($m) {
				return $m[$n[1]];
			}, $rule[3]));

			$controller = '\\Controllers\\'.ucfirst(preg_replace_callback('/-([a-z])/i', function ($n) {
				return strtoupper($n[1]);
			}, $controller));

			$method = preg_replace_callback('/-([a-z])/i', function ($n) {
				return strtoupper($n[1]);
			}, $method);

			if (!class_exists($controller)) {
				$this->_logger->info($rule[3].' => controller "'.$controller.'" not found ['.$route.']');
				continue;
			}

			if (!method_exists($controller, $method)) {
				$this->_logger->info($rule[3].' => method "'.$method.'" not found ['.$route.']');
				continue;
			}

			$rm          = (new \ReflectionClass($controller))->getMethod($method);
			$m['params'] = isset($m['params']) ? explode('/', $m['params']) : [];
			$funcArgs    = [];

			foreach ($rm->getParameters() as $param) {
				if (in_array($param->name, ['controller', 'action', 'method'])) {
					throw new \Exception('cannot use reserved keyword "'.$param->name.'" as function parameter: '.$controller.'->'.$method.'()');
				}

				if (isset($m[$param->name])) {
					$funcArgs[] = urldecode($m[$param->name]);
				} elseif (count($m['params'])) {
					$funcArgs[] = urldecode(array_shift($m['params']));
				} elseif (!$param->isOptional()) {
					$this->_logger->info($rule[3].' => missing parameter "'.$param->name.'" ['.$route.']');
					continue 2;
				}
			}

			if (count($m['params'])) {
				$this->_logger->info($rule[3].' => unexpected parameters "'.implode('", "', $m['params']).'" ['.$route.']');
				continue;
			}

			$result = \App::make($controller)->callMethod($method, $funcArgs);

			if ($result === false) {
				throw new \Exception('method call failed: '.$controller.'->'.$method.'()');
			}

			return $result;
		}
	}
}

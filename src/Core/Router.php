<?php namespace Mopsis\Core;

class Router
{
	protected $logger;
	protected $request;

	public function __construct(\Psr\Log\LoggerInterface $logger, \Aura\Web\Request $request)
	{
		$this->logger  = $logger;
		$this->request = $request;
	}

	public function get()
	{
		$requestMethod = $this->request->method->get();
		$route         = $this->request->url->get(PHP_URL_PATH);

		if ($route === '/') {
			$route = '/home';
		}

		$validRules = '/^('.$requestMethod.'|\*)\s+(?<path>\/(?:\{[^}]+\}|'.preg_quote(preg_replace('/^\/([^\/]+).*/', '$1', $route), '/').')\S*)\s+(.+)\n?$/i';

		foreach (preg_grep($validRules, file('config/routes')) as $line) {
			if (!preg_match($validRules, $line, $rule)) {
				throw new \Exception('invalid route: '.$line);
			}

			$test = preg_replace('/\\\{(.+?)\\\}/', '(?<$1>[^\/]*)', preg_quote($rule['path'], '/'));
			if (!preg_match('/^'.$test.'(?:\/(?<params>.*))?/', rtrim($route, '/'), $m)) {
				$this->logger->info($rule[3].' => path does not match ['.$route.']');
				continue;
			}

			$m['method'] = $requestMethod;

			list($domain, $action) = explode('.', preg_replace_callback('/\{(.+?)\}/', function ($n) use ($m) {
				return $m[$n[1]];
			}, $rule[3]));

			$class = $this->getActionClass($domain, $action);

			if (!class_exists($class)) {
				$this->logger->info($rule[3].' => action "'.$class.'" not found ['.$route.']');
				continue;
			}

			$funcArgs = $this->getFunctionArguments($class, $m, $rule[3]);

			if ($funcArgs === false) {
				continue;
			}

			return App::make($class)->__invoke(...$funcArgs);
		}

		return false;
	}

	protected function getFunctionArguments($class, $m, $function)
	{
		$rm          = (new \ReflectionClass($class))->getMethod('__invoke');
		$m['params'] = isset($m['params']) ? explode('/', $m['params']) : [];
		$funcArgs    = [];

		foreach ($rm->getParameters() as $param) {
			if (in_array($param->name, ['controller', 'action', 'method'])) {
				throw new \Exception('cannot use reserved keyword "'.$param->name.'" as function parameter: '.$class.'->'.$action.'()');
			}

			if ($param->isVariadic()) {
				return array_merge($funcArgs, $m['params']);
			}

			if (isset($m[$param->name])) {
				$funcArgs[] = urldecode($m[$param->name]);
			} elseif (count($m['params'])) {
				$funcArgs[] = urldecode(array_shift($m['params']));
			} elseif (!$param->isOptional()) {
				$this->logger->info($function.' => missing parameter "'.$param->name.'" ['.$route.']');
				return false;
			}
		}

		if (count($m['params'])) {
			$this->logger->info($function.' => unexpected parameters "'.implode('", "', $m['params']).'" ['.$route.']');
			return false;
		}

		return $funcArgs;
	}

	protected function getActionClass($domain, $action)
	{
		$camelCase = function ($string) {
			return ucfirst(preg_replace_callback('/-([a-z])/i', function ($n) {
				return strtoupper($n[1]);
			}, strtolower($string)));
		};

		return sprintf('\App\%1$s\Action\%1$s%2$sAction', $camelCase(str_singular($domain)), $camelCase($action));
	}
}

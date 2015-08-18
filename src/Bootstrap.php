<?php namespace Mopsis;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\GlobAsset;
use Mopsis\Core\App;
use Mopsis\Core\Cache;

class Bootstrap
{
	public function kickstart($flushMode = null)
	{
		if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/@info') {
			return phpinfo();
		}

		$this->initialize();
		$this->updateCache($flushMode);

		include 'app/initialize.php';

		$response = $this->doRouting();
		$sender = new \Aura\Web\ResponseSender($response);

		$sender->__invoke();
	}

	public function initialize()
	{
		setlocale(LC_CTYPE, 'de_DE.UTF8');
		setlocale(LC_TIME, 'de_DE.UTF8');
		session_start();

		if (!defined('APPLICATION_PATH')) {
			define('APPLICATION_PATH', realpath($_SERVER['DOCUMENT_ROOT'] . '/..'));
		}

		$builder = new \DI\ContainerBuilder;
		$builder->addDefinitions(__DIR__ . '/config.php');
		$builder->addDefinitions(APPLICATION_PATH . '/config/definitions.php');

		Core\Registry::load(
			APPLICATION_PATH . '/config/environment.php',
			APPLICATION_PATH . '/config/credentials.php'
		);

		App::initialize($builder->build());
		App::make('Database');
		App::make('ErrorHandler');
	}

	protected function updateCache($flushMode)
	{
		if ($flushMode === 'all' || $flushMode === 'app') {
			$adapter = new \CacheTool\Adapter\FastCGI('127.0.0.1:9000');
			$cache   = \CacheTool\CacheTool::factory($adapter);

			$cache->apc_clear_cache('both');
			$cache->opcache_reset();
		}

		if ($flushMode === 'all') {
			Cache::flush();
		}

		if ($flushMode === 'all' || $flushMode === 'assets') {
			Cache::set('css.version', time());
			Cache::set('javascript.version', time());
		}

		if ($flushMode === 'all' || $flushMode === 'views') {
			App::make('Renderer')->clearCacheFiles();
		}
	}

	protected function doRouting()
	{
		$response = App::make('Mopsis\Core\Router')->get();

		if ($response instanceof \Aura\Web\Response) {
			return $response;
		}

		if ($response === null) {
			return $this->buildResponse(502, 'Bad Gateway');
		}

		if ($response !== false) {
			return $this->buildResponse(203, $response);
		}

		App::make('Logger')
		   ->error('file not found: ' . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'] . ' [' . $_SERVER['HTTP_USER_AGENT'] . ']');

		return $this->buildResponse(404, file_get_contents(CORE_STATUS_404));
	}

	private function buildResponse($code, $content)
	{
		$response = App::make('Aura\Web\Response');
		$response->status->setCode($code);
		$response->content->set($content);

		return $response;
	}
}

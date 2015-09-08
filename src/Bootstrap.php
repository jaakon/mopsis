<?php namespace Mopsis;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\GlobAsset;
use Mopsis\Core\App;
use Mopsis\Core\Cache;
use Mopsis\Extensions\Aura\Web\ResponseSender;

class Bootstrap
{
	public function kickstart($flushMode = null)
	{
		if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/@info') {
			return phpinfo();
		}

		$this->initialize();
		$this->updateCache($flushMode);

		include APPLICATION_PATH . '/app/initialize.php';

		$response = $this->executeRoute();
		(new ResponseSender($response))->__invoke();
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
		$builder->addDefinitions(__DIR__ . '/definitions.php');
		$builder->addDefinitions(APPLICATION_PATH . '/config/definitions.php');

		App::initialize($builder->build());

		App::get('config')->load(
			APPLICATION_PATH . '/config/config.php',
			APPLICATION_PATH . '/config/credentials.php'
		);

		App::get('Database');
		App::get('ErrorHandler');
	}

	protected function updateCache($flushMode)
	{
		if ($flushMode === 'all') {
			Cache::flush();
		}

		if ($flushMode === 'all' || $flushMode === 'app') {
			App::get('CacheTool')->opcache_reset();
		}

		if ($flushMode === 'all' || $flushMode === 'assets') {
			Cache::clear('css.version');
			Cache::clear('javascript.version');
		}

		if ($flushMode === 'all' || $flushMode === 'views') {
			App::get('Renderer')->clearCacheFiles();
		}

		Cache::get('css.version', function() {
			return filemtime(APPLICATION_PATH . '/public/css') ?: time();
		});

		Cache::get('javascript.version', function() {
			return filemtime(APPLICATION_PATH . '/public/js') ?: time();
		});
	}

	protected function executeRoute()
	{
		$response = App::get('Mopsis\Core\Router')->get();

		if ($response instanceof \Aura\Web\Response) {
			return $response;
		}

		if ($response === null) {
			return $this->buildResponse(502, static_page(502));
		}

		if ($response !== false) {
			return $this->buildResponse(203, $response);
		}

		App::get('Logger')
		   ->error('file not found: ' . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'] . ' [' . $_SERVER['HTTP_USER_AGENT'] . ']');

		return $this->buildResponse(404, static_page(404));
	}

	private function buildResponse($code, $content)
	{
		$response = App::get('Aura\Web\Response');

		$response->status->setCode($code);
		$response->content->set($content);

		return $response;
	}
}

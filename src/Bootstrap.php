<?php namespace Mopsis;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\GlobAsset;
use Mopsis\Core\App;

class Bootstrap
{
	public function kickstart()
	{
		if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/@info') {
			return phpinfo();
		}

		$this->initialize();
		$this->updateCache($_GET['flush']);

		$response = $this->doRouting();

		$sender = new \Aura\Web\ResponseSender($response);
		$sender->__invoke();
	}

	public function initialize()
	{
		setlocale(LC_CTYPE, 'de_DE.UTF8');
		setlocale(LC_TIME, 'de_DE.UTF8');
		session_start();

		$builder = new \DI\ContainerBuilder;
		$builder->addDefinitions(__DIR__.'/config.php');

		Core\Registry::load('config/config.php', 'config/credentials.php');

		App::initialize($builder->build());
		App::make('Database');
		App::make('ErrorHandler');
	}

	protected function updateCache($flushMode)
	{
		if ($flushMode === 'all' || $flushMode === 'data') {
			App::make('Cache')->flush();
		}

		if ($flushMode === 'all' || $flushMode === 'app') {
			$adapter = new \CacheTool\Adapter\FastCGI('127.0.0.1:9000');
			$cache   = \CacheTool\CacheTool::factory($adapter);

			$cache->apc_clear_cache('both');
			$cache->opcache_reset();
		}

		$this->cacheAssets(
			$flushMode === 'css' || !file_exists('public/static/main.css'),
			$flushMode === 'js' || !file_exists('public/static/main.js')
		);

		include 'app/initialize.php';

		if ($flushMode === 'all' || $flushMode === 'app') {
			App::make('Renderer')->clearCacheFiles();
		}
	}

	protected function cacheAssets($refreshCss, $refreshJs)
	{
		$cache = App::make('Cache');

		$item = $cache->getItem('files/css/version');
		if (!$item->get() || $refreshCss) {
			$assets = new AssetCollection([
				new GlobAsset('public/css/*.css'),
				new GlobAsset('public/css/*.less', [new \Assetic\Filter\LessphpFilter]),
				new GlobAsset('public/css/*.scss', [new \Assetic\Filter\ScssphpFilter])
			], [
				new \Assetic\Filter\CssMinFilter
			]);

			file_put_contents('public/static/main.css', $assets->dump());
			$item->set(time());
		}

		$item = $cache->getItem('files/javascript/version');
		if (!$item->get() || $refreshJs) {
				$assets = new AssetCollection([
					new GlobAsset('public/js/plugins/*.js'),
					new GlobAsset('public/js/scripts/*.js')
				]);

				file_put_contents('public/static/main.js', $assets->dump());
			$item->set(time());
		}
	}

	protected function doRouting()
	{
		$response = App::make('Mopsis\Core\Router')->get();

		if ($response instanceof \Aura\Web\Response) {
			return $response;
		}

		App::make('Logger')->error('file not found: '.$_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'].' ['.$_SERVER['HTTP_USER_AGENT'].']');

		$response = App::make('Aura\Web\Response');
		$response->status->setCode(400);
		$response->content->set(file_get_contents(CORE_STATUS_400));

		return $response;
	}
}

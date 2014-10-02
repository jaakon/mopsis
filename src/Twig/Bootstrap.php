<?php namespace Mopsis\Twig;

class Bootstrap extends \Twig_Extension
{
	public function getName()
	{
		return 'bootstrap';
	}

	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction('generateId', [$this, 'generateId']),
			new \Twig_SimpleFunction('addModal', [$this, 'addModal']),
			new \Twig_SimpleFunction('getModals', [$this, 'getModals'])
		];
	}

	public function generateId($uri)
	{
		return preg_match('/^\/(\w+)\/([a-z\-]+)/i', $uri, $m) ? $m[2].ucfirst($m[1]) : 'modal_'.mt_rand();
	}

	public function addModal($id, $title, $size)
	{
		\Mopsis\Core\Registry::set('modals/'.$id, ['id' => $id, 'title' => $title, 'size' => $size]);
	}

	public function getModals()
	{
		return \Mopsis\Core\Registry::get('modals') ?: [];
	}
}

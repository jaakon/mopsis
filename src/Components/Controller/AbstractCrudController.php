<?php namespace Mopsis\Components\Controller;

use Mopsis\Contracts\Model;
use Mopsis\Core\Auth;
use Mopsis\Core\Registry;

abstract class AbstractCrudController extends AbstractController
{
	protected function _create($formId, Model $instance, $ancestor = null)
	{
		if ($ancestor === false) {
			throw new \Exception('invalid or missing ancestor object');
		}

		$this->view->assign(['ancestorToken' => $ancestor->token]);

		$route  = $this->findRoute();
		$status = $this->handleFormAction($formId, $instance, $route);

		if ($status !== 200) {
			return $this->getResponseObject($status, $instance);
		}

		if ($ancestor) {
			$instance->set(strtolower(class_basename($ancestor)), $ancestor);
		}

		$instance->import($this->filter->getResult())->save();

		if (class_exists('\App\Models\Event')) {
			\App\Models\Event::add($instance, Auth::user(), $route);
		}

		return $this->getResponseObject(201, $instance);
	}

	protected function _update($formId, Model $instance)
	{
		$route  = $this->findRoute();
		$status = $this->handleFormAction($formId, $instance, $route);

		if ($status !== 200) {
			return $this->getResponseObject($status, $instance);
		}

		$oldData = $instance->toArray();
		$instance->import($this->filter->getResult());
		$newData = $instance->toArray();

		if ($instance->hasProperty('uri')) {
			$instance->set('uri', null)->uri;
		}

		if (class_exists('\App\Models\Event')) {
			\App\Models\Event::add($instance, Auth::user(), $route, array_diff_values($oldData, $newData));
		}

		return $this->getResponseObject(205, $instance);
	}

	protected function _delete(Model $instance)
	{
		if (!$instance) {
			throw new \Exception('invalid or missing object');
		}

		if (!$instance->hasProperty('deleted')) {
			$instance->delete();
			return $this->getResponseObject(204, $instance);
		}

		$instance->deleted = true;

		if (class_exists('\App\Models\Event')) {
			\App\Models\Event::add($instance, Auth::user(), $this->findRoute());
		}

		return $this->getResponseObject(204, $instance);
	}

	protected function _set(Model $instance, $key, $value)
	{
		if (!$instance) {
			throw new \Exception('invalid or missing object');
		}

		$instance->{$key} = $value;

		if (class_exists('\App\Models\Event')) {
			\App\Models\Event::add($instance, Auth::user(), $this->findRoute(), [$key => $value]);
		}

		return $this->getResponseObject(205, $instance);
	}

	protected function findRoute()
	{
		return class_basename($this) . '.' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[2]['function'];
	}

	protected function handleFormAction($formId, Model $instance)
	{
		if (!$instance) {
			throw new \Exception('invalid or missing object');
		}

		$this->view->setFormValues($formId, $instance->toArray(true));

		if ($instance->exists) {
			$this->view->assign(['token' => $instance->token]);
		}

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			return 202;
		}

		if ($this->filter->isValid($formId)) {
			return 200;
		}

		$this->view->prefillForm($formId, $this->filter);

		return 422;
	}

	private function getResponseObject($code, $entity)
	{
		return (object)[
			'status'   => $code,
			'instance' => $entity,
			'success'  => $code !== 202 && $code !== 422
		];
	}
}

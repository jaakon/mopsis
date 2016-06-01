<?php namespace Mopsis\Components\Controller;

use Mopsis\Core\Registry;
use Mopsis\Extensions\Eloquent\Model;

abstract class AbstractCrudController extends AbstractController
{
	protected function _create(Model $model)
	{
		$model->fill($this->_getAcceptedData($model))->save();

		return $model;
	}

	protected function _update(Model $model)
	{
		$model->update($this->_getAcceptedData($model));

		return $model;
	}

	protected function _delete(Model $model)
	{
		$model->delete();

		return $model;
	}

	protected function _set(Model $model, $key, $value)
	{
		$model->setAttribute($key, $value);
		$model->save();

		return $model;
	}

	protected function _getAcceptedData(Model $model)
	{
		return array_intersect_key(
			$this->facade->getCleanRequest()->toArray(),
			array_flip($model->getFillableAttributes())
		);
	}

	protected function _getRoute($additionalLevels = 0)
	{
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[1 + $additionalLevels];
		return str_replace('App\\Controllers\\', '', $backtrace['class']).'.'.$backtrace['function'];
	}

	protected function _handleFormAction(Model $model, $route = null)
	{
		if (!$model) {
			throw new \Exception('invalid or missing object');
		}

		if ($route === null) {
			$route = $this->_getRoute(1);
		}

		if (!Registry::has('forms/'.$route)) {
			throw new \Exception('cannot find form for route "'.$route.'"');
		}

		$formId = Registry::get('forms/'.$route);

		$this->view->setFormValues($formId, $model->toArray());

		if ($model->exists) {
			$this->view->assign(['token' => $model->token]);
		}

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			return false;
		}

		if (!$this->facade->isValid()) {
			$this->view->prefillForm($formId, $this->facade);
			return false;
		}

		return true;
	}
}

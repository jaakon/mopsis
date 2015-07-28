<?php namespace Mopsis\ADR;

abstract class AbstractCrudController extends AbstractController
{
	protected function createModel(\Mopsis\Eloquent\Model $model)
	{
		$model->fill($this->getAcceptedData($model))->save();

		return $model;
	}

	protected function updateModel(\Mopsis\Eloquent\Model $model)
	{
		$model->update($this->getAcceptedData($model));

		return $model;
	}

	protected function deleteModel(\Mopsis\Eloquent\Model $model)
	{
		$model->delete();

		return $model;
	}

	protected function setModelAttribute(\Mopsis\Eloquent\Model $model, $key, $value)
	{
		$model->setAttribute($key, $value);
		$model->save();

		return $model;
	}

	protected function getAcceptedData($model)
	{
		return array_intersect_key($this->_facade->getCleanRequest()->toArray(), array_flip($model->getColumns()));
	}

	protected function getRoute($additionalLevels = 0)
	{
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[1 + $additionalLevels];

		return str_replace('Controllers\\', '', $backtrace['class']) . '.' . $backtrace['function'];
	}

	protected function handleFormAction(\Mopsis\Eloquent\Model $model, $route = null)
	{
		if (!$model) {
			throw new \Exception('invalid or missing object');
		}

		if ($route === null) {
			$route = $this->getRoute(1);
		}

		if (!\Mopsis\Core\Registry::has('forms/' . $route)) {
			throw new \Exception('cannot find form for route "' . $route . '"');
		}

		$formId = \Mopsis\Core\Registry::get('forms/' . $route);

		$this->_view->setFormValues($formId, $model->toArray());

		if ($model->isBound()) {
			$this->_view->assign(['token' => $model->token]);
		}

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			return false;
		}

		if (!$this->_facade->isValid()) {
			$this->_view->prefillForm($formId, $this->_facade);

			return false;
		}

		return true;
	}
}

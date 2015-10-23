<?php namespace Mopsis\Components\Controller;

use App\Models\Event;
use Mopsis\Contracts\Model;
use Mopsis\Core\Auth;

abstract class AbstractCrudController extends AbstractController
{
	protected function createModel($formId, Model $instance, Model $ancestor = null)
	{
		$this->view->assign(['ancestorToken' => $ancestor->token]);

		$status = $this->handleFormAction($formId, $instance);

		if ($status !== 200) {
			return $this->getResponseObject($status, $instance);
		}

		if ($ancestor) {
			$instance->set(strtolower(class_basename($ancestor)), $ancestor);
		}

		$instance->update($this->filter->getResult()); // newQuery == PROBLEMS?
		//$instance->fill($this->filter->getResult())->save();

		return $this->getResponseObject(201, $instance);
	}

	protected function updateModel($formId, Model $instance)
	{
		$status = $this->handleFormAction($formId, $instance);

		if ($status !== 200) {
			return $this->getResponseObject($status, $instance);
		}

		$instance->update($this->filter->getResult());

		return $this->getResponseObject(205, $instance);
	}

	protected function deleteModel(Model $instance)
	{
		if ($instance->hasProperty('deleted')) {
			$instance->deleted = true;

			return $this->getResponseObject(204, $instance);
		}

		$instance->delete();

		return $this->getResponseObject(204, $instance);
	}

	protected function setModelProperty(Model $instance, $key, $value)
	{
		$instance->setAttribute($key, $value);

		return $this->getResponseObject(205, $instance);
	}

	protected function handleFormAction($formId, Model $instance)
	{
		$this->view
			->setFormValues($formId, $instance->toArray(true)) // true --> usePrettyValues
			->assign(['formId' => $formId]);

		if ($instance->exists) {
			$this->view->assign(['token' => $instance->token]);
		}

		if (!$this->request->method->isPost()) {
			return 202;
		}

		if ($instance->exists && $this->filter->forUpdate($formId, $this->request->post->get())) {
			return 200;
		}

		if (!$instance->exists && $this->filter->forInsert($formId, $this->request->post->get())) {
			return 200;
		}

		$this->view->prefillForm($formId, $this->filter);

		return 422;
	}

	private function getResponseObject($code, $entity)
	{
		return (object) [
			'status'   => $code,
			'instance' => $entity,
			'success'  => $code !== 202 && $code !== 422
		];
	}
}

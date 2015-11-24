<?php namespace Mopsis\Components\Controller;

use App\Models\Event;
use Mopsis\Contracts\Model;
use Mopsis\Core\Auth;
use Mopsis\Extensions\Eloquent\Model as Eloquent;

abstract class AbstractCrudController extends AbstractController
{
	protected function createModel($formId, Model $instance)
	{
		$status = $this->handleFormAction($formId, $instance);

		if ($status !== 200) {
			return $this->getResponseObject($status, $instance);
		}

		if ($instance instanceof Eloquent) {
			$instance->fill($this->filter->getResult())->save();
		} else {
			$instance->update($this->filter->getResult());
		}

		return $this->getResponseObject(201, $instance);
	}

	protected function createChildModel($formId, Model $instance, Model $ancestor)
	{
		$this->view->assign(['ancestorToken' => $ancestor->token]);

		$status = $this->handleFormAction($formId, $instance);

		if ($status !== 200) {
			return $this->getResponseObject($status, $instance);
		}

		if ($instance instanceof Eloquent) {
			$instance->ancestor()->associate($ancestor);
			$instance->fill($this->filter->getResult())->save();
		} else {
			$instance->set(strtolower(class_basename($this)), $ancestor);
			$instance->update($this->filter->getResult());
		}

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
			->setFormValues($formId, $instance->toFormData())
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

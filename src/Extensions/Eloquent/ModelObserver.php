<?php
namespace Mopsis\Extensions\Eloquent;

use Cviebrock\EloquentSluggable\SluggableInterface;
use Mopsis\Contracts\Loggable;
use Mopsis\Contracts\Traceable;

class ModelObserver
{
	public function saving($model)
	{
		if ($model instanceof SluggableInterface) {
			$model->sluggify();
		}
	}

	public function creating($model)
	{
		if ($model instanceof Traceable) {
			$model->setCreatingUser();
		}
	}

	public function created($model)
	{
		if ($model instanceof Loggable) {
			$model->logChanges(class_basename($model) . '.created');
		}
	}

	public function updating($model)
	{
		if ($model instanceof Traceable) {
			$model->setUpdatingUser();
		}
	}

	public function updated($model)
	{
		if ($model instanceof Loggable) {
			$model->logChanges(class_basename($model) . '.updated');
		}
	}

	public function deleted($model)
	{
		if ($model instanceof Loggable) {
			$model->logChanges(class_basename($model) . '.deleted');
		}
	}
}

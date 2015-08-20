<?php
namespace Mopsis\Extensions\Eloquent;

class ModelObserver
{
	public function saving($model)
	{
		if ($model instanceof \Cviebrock\EloquentSluggable\SluggableInterface) {
			$model->sluggify();
		}
	}

	public function creating($model)
	{
		if ($model instanceof \Mopsis\Contracts\Traceable) {
			$model->setCreatingUser();
		}
	}

	public function created($model)
	{
		if ($model instanceof \Mopsis\Contracts\Loggable) {
			$model->logChanges(getClassName($model) . '.created');
		}
	}

	public function updating($model)
	{
		if ($model instanceof \Mopsis\Contracts\Traceable) {
			$model->setUpdatingUser();
		}
	}

	public function updated($model)
	{
		if ($model instanceof \Mopsis\Contracts\Loggable) {
			$model->logChanges(getClassName($model) . '.updated');
		}
	}

	public function deleted($model)
	{
		if ($model instanceof \Mopsis\Contracts\Loggable) {
			$model->logChanges(getClassName($model) . '.deleted');
		}
	}
}

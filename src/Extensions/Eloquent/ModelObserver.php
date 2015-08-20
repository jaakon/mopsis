<?php
namespace Mopsis\Mopsis\Eloquent;

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
		if ($model instanceof \Mopsis\Extensions\iTraceable) {
			$model->setCreatingUser();
		}
	}

	public function created($model)
	{
		if ($model instanceof \Mopsis\Extensions\iLoggable) {
			$model->logChanges(getClassName($model) . '.created');
		}
	}

	public function updating($model)
	{
		if ($model instanceof \Mopsis\Extensions\iTraceable) {
			$model->setUpdatingUser();
		}
	}

	public function updated($model)
	{
		if ($model instanceof \Mopsis\Extensions\iLoggable) {
			$model->logChanges(getClassName($model) . '.updated');
		}
	}

	public function deleted($model)
	{
		if ($model instanceof \Mopsis\Extensions\iLoggable) {
			$model->logChanges(getClassName($model) . '.deleted');
		}
	}
}

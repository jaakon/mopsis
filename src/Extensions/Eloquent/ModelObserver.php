<?php
namespace Mopsis\Extensions\Eloquent;

use Cviebrock\EloquentSluggable\SluggableInterface;
use Mopsis\Contracts\Loggable;
use Mopsis\Contracts\Traceable;

class ModelObserver
{
//    public function restoring(Model $model)

//    public function restored(Model $model)

    public function created(Model $model)
    {
        if ($model instanceof Loggable) {
            $model->logChanges(class_basename($model) . '.created');
        }
    }

    public function creating(Model $model)
    {
        if ($model instanceof Traceable) {
            $model->setCreatingUser();
        }
    }

//    public function saved(Model $model)

//    public function deleting(Model $model)

    public function deleted(Model $model)
    {
        if ($model instanceof Loggable) {
            $model->logChanges(class_basename($model) . '.deleted');
        }
    }

    public function saving(Model $model)
    {
        if ($model instanceof SluggableInterface) {
            $model->sluggify();
        }
    }

    public function updated(Model $model)
    {
        if ($model instanceof Loggable) {
            $model->logChanges(class_basename($model) . '.updated');
        }
    }

    public function updating(Model $model)
    {
        if ($model instanceof Traceable) {
            $model->setUpdatingUser();
        }
    }
}

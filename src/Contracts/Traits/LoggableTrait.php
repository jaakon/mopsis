<?php namespace Mopsis\Contracts\Traits;

use Mopsis\Core\App;
use Mopsis\Core\Auth;

trait LoggableTrait
{
	public function logChanges($message = null)
	{
		if ($this->exists && !count($this->getDirty())) {
			return $this;
		}

		$eventClass = App::get('Event');

		$event = new $eventClass([
			'message' => $message ?: $this->traceAction(),
			'values'  => json_encode($this->getDiff())
		]);

		$event->user()->associate(Auth::user());
		$this->events()->save($event);

		return $this;
	}

	protected function getDiff()
	{
		$diff = [];

		foreach ($this->getDirty() as $field => $newValue) {
			$oldValue = $this->getOriginal($field);
			if ($oldValue !== $newValue) {
				$diff[$field] = $newValue;
			}
		}

		return array_diff_key($diff, array_fill_keys([
			$this->getKeyName(),
			static::CREATED_AT,
			static::UPDATED_AT,
			static::DELETED_AT
		], null));
	}

	protected function traceAction()
	{
		foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $frame) {
			if (preg_match('/^Controllers\\\\(\w+)/', $frame['class'], $m)) {
				return $m[1] . '.' . $frame['function'];
			}
		}

		return null;
	}
/*
		if (class_exists('\App\Models\Event')) {
			Event::add($instance, Auth::user(), $this->findRoute(), array_diff_values($oldData, $newData));
		}

		if (class_exists('\App\Models\Event')) {
			Event::add($instance, Auth::user(), $this->findRoute());
		}

		if (class_exists('\App\Models\Event')) {
			Event::add($instance, Auth::user(), $this->findRoute(), [$key => $value]);
		}

		protected function findRoute()
		{
			return class_basename($this) . '.' . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[2]['function'];
		}

		if ($instance->hasProperty('uri')) {
			$instance->set('uri', null)->uri;
		}

*/

}

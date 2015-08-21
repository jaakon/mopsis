<?php namespace Mopsis\Contracts;

interface Loggable
{
	public function logChanges();
}

trait LoggableTrait
{
	public function logChanges($message = null)
	{
		if ($this->exists && !count($this->getDirty())) {
			return $this;
		}

		$event = new \Models\Event([
			'message' => $message ?: $this->traceAction(),
			'values'  => json_encode($this->getDiff())
		]);

		$event->user()->associate(\Mopsis\Auth::user());
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
	}
}

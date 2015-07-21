<?php namespace Mopsis\Extensions;

trait LoggableTrait
{
	public function logChanges($message = null)
	{
		if ($this->exists && !count($this->getDirty())) {
			return $this;
		}

		$event = new \Models\Event([
			'message' => $message ?: $this->_traceAction(),
			'values'  => json_encode($this->_getDiff())
		]);

		$event->user()->associate(\Mopsis\Auth::user());
		$this->events()->save($event);

		return $this;
	}

	private function _getDiff()
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

	private function _traceAction()
	{
		foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $frame) {
			if (preg_match('/^Controllers\\\\(\w+)/', $frame['class'], $m)) {
				return $m[1] . '.' . $frame['function'];
			}
		}
	}
}

interface LoggableInterface
{
	public function logChanges();
}

<?php
namespace Mopsis\Contracts\Traits;

use Mopsis\Core\App;
use Mopsis\Core\Auth;

trait LoggableTrait
{
    public function logChanges($message = null)
    {
        if ($this->exists && !count($this->getDirty())) {
            return $this;
        }

        $event = App::make('Event', [
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

        /**
         * @noinspection PhpUndefinedClassConstantInspection
         */
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

        return;
    }
}

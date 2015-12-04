<?php namespace Mopsis\Extensions;

class Flash
{
	private $key    = 'flash_messages';
	private $levels = ['info', 'success', 'error', 'warning'];

	public function __construct()
	{
		if (!is_array($_SESSION[$this->key])) {
			$_SESSION[$this->key] = [];
		}
	}

	public function __call($name, $arguments)
	{
		$this->message($name, ...$arguments);

		return $this;
	}

	public function message($level, $message, $url = null)
	{
		if (!in_array($level, $this->levels)) {
			throw new \InvalidArgumentException('unknown level: ' . $level);
		}

		$this->addMessage($level, $message, $url);

		return $this;
	}

	protected function addMessage($level, $text, $url = null)
	{
		if (empty($text)) {
			return;
		}

		$_SESSION[$this->key][] = ['level' => $level, 'text' => $text, 'url' => $url];
	}

	public function flush()
	{
		$messages             = $_SESSION[$this->key];
		$_SESSION[$this->key] = [];

		return $messages;
	}
}

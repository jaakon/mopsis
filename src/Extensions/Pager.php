<?php namespace Mopsis\Extensions;

class Pager
{
	protected $options = ['cycle', 'delimiter', 'directoryRendering', 'directoryRenderingSelected', 'firstAvailable', 'firstNotAvailable', 'lastAvailable', 'lastNotAvailable', 'previousAvailable', 'previousNotAvailable', 'nextAvailable', 'nextNotAvailable',];
	protected $args = ['cycle' => false];
	protected $result = ['data', 'pos', 'pages', 'directory', 'first', 'last', 'previous', 'next',];

	public function __construct(array $data, $pageSize, $currentPage, array $args)
	{
		$this->result['pages'] = ceil(count($data) / $pageSize);
		$this->result['pos']   = min(max($currentPage, 1), $this->result['pages']);

		foreach ($this->options as $option) {
			if (isset($args[$option])) {
				$this->args[$option] = $args[$option];
			}
		}

		$this->result['first'] = $this->getHtml($this->result['pos'] > 1 ? 'firstAvailable' : 'firstNotAvailable', 1);
		$this->result['last']  = $this->getHtml($this->result['pos'] < $this->result['pages'] ? 'lastAvailable' : 'lastNotAvailable', $this->result['pages']);

		if ($this->args['cycle']) {
			$this->result['previous'] = $this->getHtml('previousAvailable', $this->result['pos'] > 1 ? $this->result['pos'] - 1 : $this->result['pages']);
			$this->result['next']     = $this->getHtml('nextAvailable', $this->result['pos'] < $this->result['pages'] ? $this->result['pos'] + 1 : 1);
		} else {
			$this->result['previous'] = $this->getHtml($this->result['pos'] > 1 ? 'previousAvailable' : 'previousNotAvailable', $this->result['pos'] - 1);
			$this->result['next']     = $this->getHtml($this->result['pos'] < $this->result['pages'] ? 'nextAvailable' : 'nextNotAvailable', $this->result['pos'] + 1);
		}

		$directory = [];

		foreach (range(1, $this->result['pages']) as $pos) {
			$directory[] = $this->getHtml($pos == $this->result['pos'] ? 'directoryRenderingSelected' : 'directoryRendering', $pos);
		}

		$this->result['directory'] = implode($this->args['delimiter'], $directory);
		$this->result['data']      = array_slice($data, ($this->result['pos'] - 1) * $pageSize, $pageSize);
	}

	protected function getHtml($option, $pos)
	{
		return str_replace(['{PAGE}'], [$pos], $this->args[$option]);
	}

	public function __get($key)
	{
		if (!isset($this->result[$key])) {
			throw new \Exception('invalid property');
		}

		return $this->result[$key];
	}
}

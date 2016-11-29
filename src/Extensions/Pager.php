<?php
namespace Mopsis\Extensions;

class Pager
{
    protected $args = [
        'infinite' => false
    ];

    protected $options = [
        'infinite',
        'delimiter',
        'directoryRendering',
        'directoryRenderingSelected',
        'firstAvailable',
        'firstNotAvailable',
        'lastAvailable',
        'lastNotAvailable',
        'previousAvailable',
        'previousNotAvailable',
        'nextAvailable',
        'nextNotAvailable'
    ];

    protected $result = [];

    public function __construct(array $data, $pageSize, $currentPage, array $args)
    {
        $this->setArguments($args);

        $firstPage   = 1;
        $lastPage    = ceil(count($data) / $pageSize);
        $currentPage = min(max($currentPage, 1), $lastPage);

        $this->result = $this->getArgForPage($currentPage, $firstPage, $lastPage, $this->args['infinite']);

        $this->result['pages']     = $lastPage;
        $this->result['pos']       = $currentPage;
        $this->result['directory'] = getDirectory($currentPage, $firstPage, $lastPage, $this->args['delimiter']);
        $this->result['data']      = array_slice($data, ($currentPage - 1) * $pageSize, $pageSize);
    }

    public function __get($key)
    {
        if (!isset($this->result[$key])) {
            throw new \Exception('invalid property');
        }

        return $this->result[$key];
    }

    public function getDirectory($currentPage, $firstPage, $lastPage, $delimiter)
    {
        $directory = [];

        foreach (range($firstPage, $lastPage) as $page) {
            $directory[] = $this->getArg($page == $currentPage ? 'directoryRenderingSelected' : 'directoryRendering', $page);
        }

        return implode($delimiter, $directory);
    }

    protected function getArg($option, $pos = null)
    {
        return str_replace('{PAGE}', $pos, $this->args[$option]);
    }

    protected function getArgForFirstPage($currentPage, $firstPage, $lastPage, bool $infinite)
    {
        return [
            'first'    => $this->getArg('firstNotAvailable', $firstPage),
            'last'     => $this->getArg('lastAvailable', $lastPage),
            'previous' => $this->getArg($infinite ? 'previousAvailable' : 'previousNotAvailable', $lastPage),
            'next'     => $this->getArg('nextAvailable', $currentPage + 1)
        ];
    }

    protected function getArgForLastPage($currentPage, $firstPage, $lastPage, bool $infinite)
    {
        return [
            'first'    => $this->getArg('firstAvailable', $firstPage),
            'last'     => $this->getArg('lastNotAvailable', $lastPage),
            'previous' => $this->getArg('previousAvailable', $currentPage - 1),
            'next'     => $this->getArg($infinite ? 'nextAvailable' : 'nextNotAvailable', $firstPage)
        ];
    }

    protected function getArgForMiddlePage($currentPage, $firstPage, $lastPage)
    {
        return [
            'first'    => $this->getArg('firstAvailable', $firstPage),
            'last'     => $this->getArg('lastAvailable', $lastPage),
            'previous' => $this->getArg('previousAvailable', $currentPage - 1),
            'next'     => $this->getArg('nextAvailable', $currentPage + 1)
        ];
    }

    protected function getArgForPage($currentPage, $firstPage, $lastPage, bool $infinite)
    {
        switch ($currentPage) {
            case $firstPage:
                return $this->getArgForFirstPage($currentPage, $firstPage, $lastPage, $infinite);
                break;
            case $lastPage:
                return $this->getArgForLastPage($currentPage, $firstPage, $lastPage, $infinite);
                break;
            default:
                return $this->getArgForMiddlePage($currentPage, $firstPage, $lastPage);
        }
    }

    protected function setArguments(array $args)
    {
        foreach ($this->options as $option) {
            if (isset($args[$option])) {
                $this->args[$option] = $args[$option];
            }
        }
    }
}

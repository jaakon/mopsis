<?php
namespace Mopsis\Extensions;

class Pagination
{
    protected $currentPage;

    protected $dataSlice;

    protected $defaults = [
        'infinite'                   => false,
        'limit-before'               => -1,
        'limit-after'                => -1,
        'separator'                  => '',
        'directoryRendering'         => '',
        'directoryRenderingSelected' => '',
        'firstAvailable'             => '',
        'firstNotAvailable'          => '',
        'lastAvailable'              => '',
        'lastNotAvailable'           => '',
        'previousAvailable'          => '',
        'previousNotAvailable'       => '',
        'nextAvailable'              => '',
        'nextNotAvailable'           => ''
    ];

    protected $firstPage;

    protected $lastPage;

    protected $settings = [];

    public function __construct(array $options)
    {
        foreach ($this->defaults as $key => $value) {
            $this->settings[$key] = $options[$key] ?? $value;
        }
    }

    public function __get($key)
    {
        switch ($key) {
            case 'first':
                return $this->getLinkToFirstPage();
            case 'last':
                return $this->getLinkToLastPage();
            case 'previous':
                return $this->getLinkToPreviousPage();
            case 'next':
                return $this->getLinkToNextPage();
            case 'directory':
                return $this->getLinksForPages();
            case 'data':
                return $this->dataSlice;
            case 'currentPage':
                return $this->currentPage;
            case 'totalPages':
                return $this->lastPage;
        }

        throw new \Exception('invalid property "' . $key . '"');
    }

    public function __toString()
    {
        return $this->first + $this->previous + $this->directory + $this->next + $this->last;
    }

    public function setState(array $data, int $pageSize, int $currentPage = null)
    {
        $this->firstPage   = 1;
        $this->lastPage    = ceil(count($data) / $pageSize);
        $this->currentPage = min(max($currentPage, $this->firstPage), $this->lastPage);
        $this->dataSlice   = array_slice($data, ($this->currentPage - 1) * $pageSize, $pageSize, true);

        return $this;
    }

    protected function getHtml($rendering, $page = null)
    {
        return str_replace('#PAGE#', $page, $this->settings[$rendering]);
    }

    protected function getLinkToFirstPage()
    {
        if ($this->currentPage === $this->firstPage) {
            return $this->getHtml('firstNotAvailable');
        }

        return $this->getHtml('firstAvailable', $this->firstPage);
    }

    protected function getLinkToLastPage()
    {
        if ($this->currentPage === $this->lastPage) {
            return $this->getHtml('lastNotAvailable');
        }

        return $this->getHtml('lastAvailable', $this->lastPage);
    }

    protected function getLinkToNextPage()
    {
        if ($this->currentPage < $this->lastPage) {
            return $this->getHtml('nextAvailable', $this->currentPage + 1);
        }

        if ($this->settings['infinite']) {
            return $this->getHtml('nextAvailable', $this->firstPage);
        }

        return $this->getHtml('nextNotAvailable');
    }

    protected function getLinkToPreviousPage()
    {
        if ($this->currentPage > $this->firstPage) {
            return $this->getHtml('previousAvailable', $this->currentPage - 1);
        }

        if ($this->settings['infinite']) {
            return $this->getHtml('previousAvailable', $this->lastPage);
        }

        return $this->getHtml('previousNotAvailable');
    }

    protected function getLinksForPages()
    {
        $directory = [];

        foreach ($this->getRange() as $page) {
            $rendering   = $this->currentPage === $page ? 'directoryRenderingSelected' : 'directoryRendering';
            $directory[] = $this->getHtml($rendering, $page);
        }

        return implode($this->settings['separator'], $directory);
    }

    protected function getRange()
    {
        $start = $this->firstPage;
        $end   = $this->lastPage;

        if ($this->settings['limit-before'] > -1) {
            $start = max($this->currentPage - $this->settings['limit-before'], $this->firstPage);
        }

        if ($this->settings['limit-after'] > -1) {
            $end = min($this->currentPage + $this->settings['limit-after'], $this->lastPage);
        }

        return range($start, $end);
    }
}

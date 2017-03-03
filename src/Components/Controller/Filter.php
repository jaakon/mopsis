<?php
namespace Mopsis\Components\Controller;

use Mopsis\Components\Domain\AbstractFilter;

class Filter extends AbstractFilter
{
    public function getResult($key = null)
    {
        return $key ? $this->result[$key] : $this->result;
    }
}

<?php
namespace Mopsis\Extensions\SimpleXML;

use Illuminate\Support\Collection;

class SimpleXMLElementArray extends Collection
{
    public function attr($name, $namespace = null)
    {
        $result = new self();

        foreach ($this->items as $item) {
            $result[] = $item->attr($name, $namespace);
        }

        return $result;
    }
}

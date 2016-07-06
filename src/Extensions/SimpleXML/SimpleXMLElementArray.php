<?php
namespace Mopsis\Extensions\SimpleXML;

use Illuminate\Support\Collection;

class SimpleXMLElementArray extends Collection
{
    public function attr($name, $type = 'string', $namespace = null)
    {
        $result = new self();

        foreach ($this->items as $item) {
            $result[] = $item->attr($name, $type, $namespace);
        }

        return $result;
    }
}

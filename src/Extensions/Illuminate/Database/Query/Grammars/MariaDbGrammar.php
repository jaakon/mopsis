<?php
namespace Mopsis\Extensions\Illuminate\Database\Query\Grammars;

use Illuminate\Database\Query\Grammars\MySqlGrammar;

class MariaDbGrammar extends MySqlGrammar
{
    protected function wrapJsonSelector($value)
    {
        $path = explode('->', $value);

        $field = $this->wrapValue(array_shift($path));

        return sprintf('JSON_UNQUOTE(JSON_EXTRACT(%s, \'$.%s\'))', $field, collect($path)->map(function ($part) {
            return '"' . $part . '"';
        })->implode('.'));
    }
}

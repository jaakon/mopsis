<?php
namespace Mopsis\Extensions\Illuminate\Database;

use Illuminate\Database\MySqlConnection;
use Mopsis\Extensions\Illuminate\Database\Query\Grammars\MariaDbGrammar as QueryGrammar;

class MariaDbConnection extends MySqlConnection
{
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new QueryGrammar());
    }
}

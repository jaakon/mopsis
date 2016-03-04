<?php
namespace Mopsis\Contracts\Traits;

trait TranslatableTrait
{
    public function __($key, array $replace = [])
    {
        return __($key, $replace);
    }
}

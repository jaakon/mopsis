<?php
namespace Mopsis\Contracts\Traits;

trait Translatable
{
    public function __($key, array $replace = [])
    {
        return __($key, $replace);
    }
}

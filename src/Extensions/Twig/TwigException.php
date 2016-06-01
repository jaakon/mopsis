<?php
namespace Mopsis\Extensions\Twig;

use Exception as BaseException;

class TwigException extends BaseException
{
    public function setFile($file)
    {
        $this->file = $file;
    }

    public function setLine($line)
    {
        $this->line = $line;
    }
}

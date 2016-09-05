<?php
namespace Mopsis\Extensions\Carbon;

use Carbon\Carbon as CarbonLib;

class Carbon extends CarbonLib
{
    const ISO8601 = 'Y-m-d';

    private $dateToStrftime = [
        'D' => '%a',
        'l' => '%A',
        'F' => '%B',
        'M' => '%b'
    ];

    public function __get($name)
    {
        if (defined('static::' . $name)) {
            return $this->format(constant('static::' . $name));
        }

        return parent::__get($name);
    }

    public function __invoke($modify)
    {
        return $this->cloned()->modify($modify);
    }

    public function __isset($name)
    {
        return defined('static::' . $name) ?: defined('self::' . $name);
    }

    public function cloned()
    {
        return clone $this;
    }

    public function endOfInterval($size)
    {
        switch ($size) {
            case 'day':
                return $this->endOfDay();
            case 'week':
                return $this->endOfWeek();
            case 'month':
                return $this->endOfMonth();
            case 'year':
                return $this->endOfYear();
        }

        throw new \Exception('invalid interval size: ' . $size);
    }

    public function format($format)
    {
        if (defined('static::' . $format)) {
            return parent::format(constant('static::' . $format));
        }

        if (defined('self::' . $format)) {
            return parent::format(constant('self::' . $format));
        }

        $result = '';

        while (preg_match('/^(.*?)~([DlFM])/', $format, $m)) {
            $format = str_replace($m[0], '', $format);
            $result .= parent::format($m[1]) . $this->formatLocalized($this->dateToStrftime[$m[2]]);
        }

        return $result . parent::format($format);
    }

    public function formatLocalized($format)
    {
        return utf8_encode(parent::formatLocalized($format));
    }

    public function startOfInterval($size)
    {
        switch ($size) {
            case 'day':
                return $this->startOfDay();
            case 'week':
                return $this->startOfWeek();
            case 'month':
                return $this->startOfMonth();
            case 'year':
                return $this->startOfYear();
        }

        throw new \Exception('invalid interval size: ' . $size);
    }

    public function toDeDateString()
    {
        return $this->format('d.m.Y');
    }
}

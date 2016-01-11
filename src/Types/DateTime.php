<?php namespace Mopsis\Types;

class DateTime extends \DateTime
{
	const DE_SHORT = 'd.m.Y';
//	const DE_SHORT		= 'Y-m-d';
	const DE_LONG = 'd.m.Y H:i';
//	const DE_LONG		= 'Y-m-d H:i';
	const DE_FULL = 'd.m.Y - H:i:s \U\h\r';
	const TIME_SHORT = 'H:i';
	const TIME_LONG = 'H:i:s';
	const TIME_FULL = 'H:i:s \U\h\r';
	const DATETIME = 'Y-m-d\TH:i';
	const TIMESTAMP = 'U';
	const ISO8601 = 'Y-m-d';
	private static $_localStrings = [
		'l' => [
			'Montag',
			'Dienstag',
			'Mittwoch',
			'Donnerstag',
			'Freitag',
			'Samstag',
			'Sonntag'
		],
		'D' => [
			'Mo',
			'Di',
			'Mi',
			'Do',
			'Fr',
			'Sa',
			'So'
		],
		'F' => [
			'Januar',
			'Februar',
			'März',
			'April',
			'Mai',
			'Juni',
			'Juli',
			'August',
			'September',
			'Oktober',
			'November',
			'Dezember'
		],
		'M' => [
			'Jan',
			'Feb',
			'Mär',
			'Apr',
			'Mai',
			'Jun',
			'Jul',
			'Aug',
			'Sep',
			'Okt',
			'Nov',
			'Dez'
		],
	];

	public function __construct($time = 'now', $timezone = null)
	{
		if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{2}?\d{2})$/', $time, $m)) {
			$time = $m[3] . '-' . $m[2] . '-' . $m[1];
		}

		parent::__construct($time, $timezone ?: new \DateTimeZone(date_default_timezone_get() ?: 'Europe/Berlin'));
	}

	public static function create($time = 'now', $timezone = null)
	{
		return new static($time, $timezone);
	}

	public static function max($date1, $date2)
	{
		if (!($date1 instanceof \DateTime)) {
			$date1 = new static($date1);
		}

		if (!($date2 instanceof \DateTime)) {
			$date2 = new static($date2);
		}

		return $date1 > $date2 ? $date1 : $date2;
	}

	public static function min($date1, $date2)
	{
		if (!($date1 instanceof \DateTime)) {
			$date1 = new static($date1);
		}

		if (!($date2 instanceof \DateTime)) {
			$date2 = new static($date2);
		}

		return $date1 < $date2 ? $date1 : $date2;
	}

	public function __get($key)
	{
		return $this->format(constant('static::' . $key) ?: constant('\DateTime::' . $key) ?: 'Y-m-d H:i:s');
	}

	public function format($format = 'Y-m-d')
	{
		if (defined('static::' . $format)) {
			return date_format($this, constant('static::' . $format));
		}

		if (defined('\DateTime::' . $format)) {
			return date_format($this, constant('\DateTime::' . $format));
		}

		$result = '';

		while (preg_match('/^(.*?)~([DlFM])/', $format, $m)) {
			$format = str_replace($m[0], '', $format);
			$result .= date_format($this, $m[1]) . self::$_localStrings[$m[2]][date_format($this, $m[2] === 'D' || $m[2] === 'l' ? 'N' : 'n') - 1];
		}

		return $result . date_format($this, $format);
	}

	public function __invoke($modifier)
	{
		$date = clone $this;

		return $date->modify($modifier);
	}

	public function __isset($key)
	{
		return constant('static::' . $key) ?: constant('\DateTime::' . $key);
	}

	public function __toString()
	{
		return $this->format('Y-m-d H:i:s');
	}

	public function add($interval)
	{
		return parent::add($interval instanceof \DateInterval ? $interval : \DateInterval::createFromDateString($interval));
	}

	public function cloned()
	{
		return clone $this;
	}

	public function equals(DateTime $date, $accuracy = '%a')
	{
		return !$this->diff($date)->format($accuracy);
	}

	public function untilNow()
	{
		return $this->format('U') - time();
	}
}

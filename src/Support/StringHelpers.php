<?php namespace Mopsis\Support;

use Mopsis\Core\App;

class StringHelpers
{
	public static function camelCase($string)
	{
		return ucfirst(preg_replace_callback('/-([a-z])/i', function ($match) {
			return strtoupper($match[1]);
		}, strtolower($string)));
	}

	public static function getClosestMatch($input, $words)
	{
		$shortest	= PHP_INT_MAX;
		$closest	= null;

		foreach ($words as $word) {
			$distance = levenshtein($input, $word);

			if ($distance == 0) {
				return $word;
			}

			if ($distance < $shortest) {
				$closest  = $word;
				$shortest = $distance;
			}
		}

		return $closest;
	}

	public static function isUtf8($string)
	{
		return preg_match('%(?:
			[\xC2-\xDF][\x80-\xBF]				# non-overlong 2-byte
			|\xE0[\xA0-\xBF][\x80-\xBF]			# excluding overlongs
			|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}	# straight 3-byte
			|\xED[\x80-\x9F][\x80-\xBF]			# excluding surrogates
			|\xF0[\x90-\xBF][\x80-\xBF]{2}		# planes 1-3
			|[\xF1-\xF3][\x80-\xBF]{3}			# planes 4-15
			|\xF4[\x80-\x8F][\x80-\xBF]{2}		# plane 16
		)+%xs', $string);
	}

	public static function justify($string, $length, $char = ' ')
	{
		$strlen = mb_strlen($string, 'UTF-8');

		if ($strlen > abs($length)) {
			return mb_substr($string, 0, abs($length), 'UTF-8');
		}

		$padding = str_repeat($char, abs($length) - $strlen);
		return $length > 0 ? $padding.$string : $string.$padding;
	}

	public static function utf8Encode($string)
	{
		return static::isUtf8($string) ? $string : utf8_encode($string);
	}

	public static function utf8Decode($string)
	{
		return static::isUtf8($string) ? utf8_decode($string) : $string;
	}

	public static function pluralize($count, $singular, $plural = null)
	{
		if ($plural === null) {
			switch (App::make('translator.locale')) {
				case 'de':
					$plural = $singular . 'e';
					break;
				default:
					$plural = str_plural($singular);
			}
		}

		return sprintf('%s %s', str_replace('.', ',', $count), abs($count) == 1 ? $singular : $plural);
	}

	public static function stripInvalidChars($string, $charlist = null)
	{
		setlocale(LC_CTYPE, 'de_DE.UTF8');
		return preg_replace('/[^\w'.preg_quote($charlist, '/').']+/', '-', iconv('utf-8', 'ascii//TRANSLIT', $string));
	}

	public static function vnsprintf($format, array $args)
	{
		// Find %n$n.
		preg_match_all('#\\%[\\d]*\\$[bcdeufFosxX]#', $format, $matches);

		// Weed out the dupes and count how many there are.
		$counts = count(array_unique($matches[0]));

		// Count the number of %n's and add it to the number of %n$n's.
		$countf = preg_match_all('#\\%[bcdeufFosxX]#', $format, $matches) + $counts;

		// Count the number of replacements.
		$counta = count($args);

		if ($countf > $counta) {
			// Pad $args if there's not enough elements.
			$args = array_pad($args, $countf, "&nbsp;");
		}

		return vsprintf($format, $args);
	}
}

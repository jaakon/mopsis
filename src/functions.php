<?php

use Mopsis\Core\App;

function __($key, array $replace = [])
{
	return App::make('i18n')->get($key, $replace);
}

function app($type)
{
	return App::make($type);
}

function array_concat(array $array, ...$values)
{
	foreach ($values as $value) {
		switch (gettype($value)) {
			case 'array':
				$array = array_merge_recursive($array, $value);
				break;
			case 'object':
				if ($value instanceof \ArrayObject) {
					$array = array_merge_recursive($array, $value->getArrayCopy());
					break;
				}
				if (method_exists($value, 'toArray')) {
					$array = array_merge_recursive($array, $value->toArray());
				}
			// no break
			default:
				$array[] = $value;
		}
	}

	return $array;
}

function camelCase($string)
{
	return ucfirst(preg_replace_callback('/-([a-z])/i', function ($match) {
		return strtoupper($match[1]);
	}, strtolower($string)));
}

function debug(...$args)
{
	echo '<pre class="debug">';
	foreach ($args as $i => $arg) {
		echo $i > 0 ? '<hr>' : '';
		print_r($arg);
	}
	echo '</pre>';
}

function getClassName($class)
{
	$temp = explode('\\', is_object($class) ? get_class($class) : $class);

	return end($temp);
}

function object2array($object)
{
	if (is_array($object)) {
		return $object;
	}

	if (is_null($object)) {
		return [];
	}

	if (!is_object($object)) {
		throw new \Exception('cannot cast given object to array');
	}

	if ($object instanceof \ArrayObject) {
		return $object->getArrayCopy();
	}

	if (method_exists($object, 'toArray')) {
		return $object->toArray();
	}

	return get_object_vars($object);
}

function pluralize($count, $singular, $plural = null)
{
	if ($plural === null) {
		$plural = $singular . 'e';
	}

	return sprintf('%s %s', str_replace('.', ',', $count), abs($count) == 1 ? $singular : $plural);
}

function redirect($url = null, $responseCode = 302)
{
	if (preg_match('/^(ht|f)tps?:\/\//', $url) === 0) {
		$url = ($_SERVER['REQUEST_SCHEME'] ?: 'http') . '://' . $_SERVER['HTTP_HOST'] . resolvePath(preg_replace('/\/+$/', '', $_SERVER['REQUEST_URI']) . '/' . $url);
	}

	if (!headers_sent($file, $line)) {
		http_response_code($responseCode);
		header('Location: ' . $url);
		exit;
	}

	echo 'ERROR: Headers already sent in ' . $file . ' on line ' . $line . "!<br/>\n";
	echo 'Cannot redirect, please click <a href="' . $url . '">[this link]</a> instead.';
	exit;
}

function resolvePath($path)
{
	// replace backslashes with forward slashes
	$path = str_replace('\\', '/', $path);

	// resolve /a/b//c => /c
	$path = preg_replace('/.*\/\//', '/', $path);

	// resolve /a/b/./c => /a/b/c
	$path = preg_replace('/(\/\.)+\//', '/', $path);

	// resolve /a/b/../c => /a/c
	$path = preg_replace('/\/\w+\/\.\.\//', '/', $path);

	return $path;
}

/*
function array_diff_values(array $array1, array $array2)
{
	$diff = [];

	foreach (array_unique(array_merge(array_keys($array1), array_keys($array2))) as $key) {
		if (is_array($array1[$key]) && is_array($array2[$key])) {
			$diff[$key] = array_diff_values($array1[$key], $array2[$key]);
		} elseif (is_object($array1[$key]) && is_object($array2[$key])) {
			$diff[$key] = array_diff_values(object2array($array1[$key]), object2array($array2[$key]));
		} elseif ((string) $array1[$key] !== (string) $array2[$key]) {
			$diff[$key] = [(string) $array1[$key], (string) $array2[$key]];
		}
	}

	return array_filter($diff);
}

function array_implode($array, $glue = '=', $delimiter = ';')
{
	if (!count($array)) {
		return null;
	}

	foreach ($array as $key => $value) {
		$result .= $key.$glue.$value.$delimiter;
	}

	return rtrim($result, $delimiter);
}

function array_key_position($haystack, $needle)
{
	if ($haystack instanceof \Mopsis\Core\iCollection) {
		$haystack = $haystack->toArray();
	}

	return intval(array_search($needle, array_keys($haystack)));
}

function array_merge_recursive_distinct(array &$array1, array &$array2)
{
	$merged = $array1;

	foreach ($array2 as $key => &$value) {
		if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
			$merged[$key] = array_merge_recursive_distinct($merged[$key], $value);
			continue;
		}

		$merged[$key] = $value;
	}

	return $merged;
}

function array_move_element(&$array, $from, $to)
{
	$out = array_splice($array, $from, 1);
	array_splice($array, $to, 0, $out);
}

function array_shuffle($array)
{
	$keys = array_keys($array);
	shuffle($keys);
	return array_merge(array_flip($keys), $array);
}

function array_value($array, $key)
{
	return $array[$key];
}

function convertArrayToObject($input)
{
	return is_array($input) ? (object) array_map(__FUNCTION__, $input) : $input;
}

function convertObjectToArray($input)
{
	if (is_object($input)) {
		$input = get_object_vars($input);
	}

	return is_array($input) ? array_map(__FUNCTION__, $input) : $input;
}

function escape_html($string)
{
	return htmlspecialchars($string, ENT_COMPAT | ENT_HTML5, 'UTF-8', false);
}

function getClosestMatch($input, $words)
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

function getFilesInDirectory($directory, $addDirectoryToFile = false, $sortOrder = null)
{
	if (!is_dir($directory)) {
		return [];
	}

	$files = preg_grep('/[^\.]+/', scandir($directory, $sortOrder));

	if (!$addDirectoryToFile) {
		return $files;
	}

	return array_map(function ($file) use ($directory) {
		return $directory.'/'.$file;
	}, $files);
}

function getNamespace($class)
{
	$temp = array_filter(explode('\\', is_object($class) ? get_class($class) : $class));
	return reset($temp);
}

function implode_objects($object, $prefix = '', $glue = '.')
{
	if (!is_object($object)) {
		return [$prefix => $object];
	}

	$data = [];

	foreach (get_object_vars($object) as $key => $value) {
		if (is_object($value)) {
			$data = array_merge($data, implode_objects($value, $prefix.$glue.$key, $glue));
		} else {
			$data[$prefix.$glue.$key] = $value;
		}
	}

	return $data;
}

function is_assoc(array $array)
{
	for (reset($array); is_int(key($array)); next($array));
	return !is_null(key($array));
}

function is_utf8($string)
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

function json_fix_and_decode($json, $assoc = false)
{
	$json = str_replace('\'', '"', $json);
	$json = str_replace(["\n", "\r"], '', $json);
	$json = preg_replace('/([{,]+)(\s*)([^"]+?)\s*:/', '$1"$3":', '{'.$json.'}');

	return json_decode($json, $assoc);
}

function justify($string, $length, $char = ' ')
{
	$strlen = mb_strlen($string, 'UTF-8');

	if ($strlen > abs($length)) {
		return mb_substr($string, 0, abs($length), 'UTF-8');
	}

	$padding = str_repeat($char, abs($length) - $strlen);
	return $length > 0 ? $padding.$string : $string.$padding;
}

function mb_utf8_encode($string)
{
	return is_utf8($string) ? $string : utf8_encode($string);
}

function mb_utf8_decode($string)
{
	return is_utf8($string) ? utf8_decode($string) : $string;
}

function now()
{
	return date('Y-m-d H:i:s');
}

function object_merge(stdClass $object1, stdClass $object2)
{
	$result = clone $object1;

	foreach (array_slice(func_get_args(), 1) as $i => $object) {
		if (!($object instanceof stdClass)) {
			throw new Exception('Argument '.($i + 2).' passed to '.__FUNCTION__.'() must be an instance of stdClass');
		}

		foreach (get_object_vars($object) as $key => $value) {
			if (!isset($result->{$key}) || gettype($result->{$key}) !== gettype($value)) {
				$result->{$key} = $value;
				continue;
			}

			switch (gettype($value)) {
				case 'array':
					$result->{$key} = array_merge_recursive($result->{$key}, $value);
					break;
				case 'object':
					$result->{$key} = object_merge($result->{$key}, $value);
					break;
				default:
					$result->{$key} = $value;
					break;
			}
		}
	}

	return $result;
}

function param_decode($string)
{
	return base64_decode(str_replace('~', '/', $string));
}

function param_encode($string)
{
	return str_replace('/', '~', base64_encode($string));
}

function plainText($string)
{
	return htmlspecialchars(trim(strip_tags($string)));
}

function preg_grep_keys($pattern, $input, $flags = 0)
{
	return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
}

function realurl($url, $scheme, $host, $path = '/')
{
	return preg_match('/^(ht|f)tps?:\/\/|(mailto:|javascript:|#)/i', $url) > 0 ? $url : $scheme.'://'.$host.resolvePath($path.$url);
}

function send_http_request($method, $url, $data = [], $referer = null, $timeout = 10)
{
	$ch = curl_init();

	if (strtolower($method) === 'get' && is_array($data) && count($data) > 0) {
		$url .= (strpos($url, '?') === false ? '?' : '&').http_build_query($data);
	}

	$parts	= parse_url($url);
	$url	= $parts['scheme'].'://'.$parts['host'].$parts['path'].'?'.$parts['query'];

	curl_setopt_array($ch, [
		CURLOPT_URL				=> $url,
		CURLOPT_AUTOREFERER		=> true,
		CURLOPT_RETURNTRANSFER	=> true,
		CURLOPT_FOLLOWLOCATION	=> true,
		CURLOPT_MAXREDIRS		=> 3,
		CURLOPT_TIMEOUT			=> $timeout,
		CURLOPT_REFERER			=> $referer,
		CURLOPT_USERAGENT		=> 'Mozilla/5.0 (Windows NT 6.1; rv:2.0) Gecko/20110319 Firefox/4.0',
//		CURLOPT_SSL_VERIFYPEER	=> false,
//		CURLOPT_SSL_VERIFYHOST	=> 2,
	]);

	if (isset($parts['user']) & isset($parts['pass'])) {
		curl_setopt($ch, CURLOPT_USERPWD, $parts['user'].':'.$parts['pass']);
	}

	if (strtolower($method) === 'post') {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	}

	$content	= curl_exec($ch);
	$header		= curl_getinfo($ch);
	curl_close($ch);

	return [$header, $content];
}

function strip_invalid_chars($string, $toLower = true, $charlist = null)
{
	setlocale(LC_CTYPE, 'de_DE.UTF8');
	$string = preg_replace('/[^\w'.preg_quote($charlist, '/').']+/', '-', iconv('utf-8', 'ascii//TRANSLIT', $string));
	return $toLower ? strtolower($string) : $string;
}

function today()
{
	return date('Y-m-d');
}

function var_name(&$var, $scope = 0)
{
	$old = $var;

	if (($key = array_search($var = 'unique'.rand().'value', !$scope ? $GLOBALS : $scope)) && $var = $old) {
		return $key;
	}
}

function vnsprintf($format, array $args)
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

function wget($url, $data = [], $referer = null, $timeout = 10)
{
	$result = send_http_request('GET', $url, $data, $referer, $timeout);
	return $result[0]['http_code'] === 200 ? $result[1] : false;
}
*/

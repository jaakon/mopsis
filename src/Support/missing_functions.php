<?php

class MissingFunctions
{
	public static function array_implode($array, $glue = '=', $delimiter = ';')
	{
		if (!count($array)) {
			return null;
		}

		foreach ($array as $key => $value) {
			$result .= $key.$glue.$value.$delimiter;
		}

		return rtrim($result, $delimiter);
	}

	public static function array_key_position($haystack, $needle)
	{
		if ($haystack instanceof \Mopsis\Core\iCollection) {
			$haystack = $haystack->toArray();
		}

		return intval(array_search($needle, array_keys($haystack)));
	}

	public static function array_merge_recursive_distinct(array &$array1, array &$array2)
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

	public static function array_move_element(&$array, $from, $to)
	{
		$out = array_splice($array, $from, 1);
		array_splice($array, $to, 0, $out);
	}

	public static function array_shuffle($array)
	{
		$keys = array_keys($array);
		shuffle($keys);
		return array_merge(array_flip($keys), $array);
	}

	public static function convertArrayToObject($input)
	{
		return is_array($input) ? (object) array_map(__FUNCTION__, $input) : $input;
	}

	public static function convertObjectToArray($input)
	{
		if (is_object($input)) {
			$input = get_object_vars($input);
		}

		return is_array($input) ? array_map(__FUNCTION__, $input) : $input;
	}

	public static function escape_html($string)
	{
		return htmlspecialchars($string, ENT_COMPAT | ENT_HTML5, 'UTF-8', false);
	}

	public static function getFilesInDirectory($directory, $addDirectoryToFile = false, $sortOrder = null)
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

	public static function getNamespace($class)
	{
		$temp = array_filter(explode('\\', is_object($class) ? get_class($class) : $class));
		return reset($temp);
	}

	public static function implode_objects($object, $prefix = '', $glue = '.')
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

	public static function is_assoc(array $array)
	{
		for (reset($array); is_int(key($array)); next($array));
		return !is_null(key($array));
	}

	public static function json_fix_and_decode($json, $assoc = false)
	{
		$json = str_replace('\'', '"', $json);
		$json = str_replace(["\n", "\r"], '', $json);
		$json = preg_replace('/([{,]+)(\s*)([^"]+?)\s*:/', '$1"$3":', '{'.$json.'}');

		return json_decode($json, $assoc);
	}

	public static function param_decode($string)
	{
		return base64_decode(str_replace('~', '/', $string));
	}

	public static function param_encode($string)
	{
		return str_replace('/', '~', base64_encode($string));
	}

	public static function plainText($string)
	{
		return htmlspecialchars(trim(strip_tags($string)));
	}

	public static function preg_grep_keys($pattern, $input, $flags = 0)
	{
		return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
	}

	public static function realurl($url, $scheme, $host, $path = '/')
	{
		return preg_match('/^(ht|f)tps?:\/\/|(mailto:|javascript:|#)/i', $url) > 0 ? $url : $scheme.'://'.$host.resolve_path($path.$url);
	}

	public static function redirect($url = null, $responseCode = 302)
	{
		if (preg_match('/^(ht|f)tps?:\/\//', $url) === 0) {
			$url = ($_SERVER['REQUEST_SCHEME'] ?: 'http').'://'.$_SERVER['HTTP_HOST'].resolve_path(preg_replace('/\/+$/', '', $_SERVER['REQUEST_URI']).'/'.$url);
		}

		if (!headers_sent($file, $line)) {
			http_response_code($responseCode);
			header('Location: '.$url);
			exit;
		}

		echo 'ERROR: Headers already sent in '.$file.' on line '.$line."!<br/>\n";
		echo 'Cannot redirect, please click <a href="'.$url.'">[this link]</a> instead.';
		exit;
	}

	public static function send_http_request($method, $url, $data = [], $referer = null, $timeout = 10)
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

	public static function var_name(&$var, $scope = 0)
	{
		$old = $var;

		if (($key = array_search($var = 'unique'.rand().'value', !$scope ? $GLOBALS : $scope)) && $var = $old) {
			return $key;
		}
	}

	public static function wget($url, $data = [], $referer = null, $timeout = 10)
	{
		$result = send_http_request('GET', $url, $data, $referer, $timeout);
		return $result[0]['http_code'] === 200 ? $result[1] : false;
	}


	public static function array_implode($array, $glue = '=', $delimiter = ';')
	{
		if (!count($array)) {
			return null;
		}

		foreach ($array as $key => $value) {
			$result .= $key.$glue.$value.$delimiter;
		}

		return rtrim($result, $delimiter);
	}

	public static function array_key_position($haystack, $needle)
	{
		if ($haystack instanceof \Mopsis\Core\iCollection) {
			$haystack = $haystack->toArray();
		}

		return intval(array_search($needle, array_keys($haystack)));
	}

	public static function array_merge_recursive_distinct(array &$array1, array &$array2)
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

	public static function array_move_element(&$array, $from, $to)
	{
		$out = array_splice($array, $from, 1);
		array_splice($array, $to, 0, $out);
	}

	public static function array_shuffle($array)
	{
		$keys = array_keys($array);
		shuffle($keys);
		return array_merge(array_flip($keys), $array);
	}

	public static function convertArrayToObject($input)
	{
		return is_array($input) ? (object) array_map(__FUNCTION__, $input) : $input;
	}

	public static function convertObjectToArray($input)
	{
		if (is_object($input)) {
			$input = get_object_vars($input);
		}

		return is_array($input) ? array_map(__FUNCTION__, $input) : $input;
	}

	public static function escape_html($string)
	{
		return htmlspecialchars($string, ENT_COMPAT | ENT_HTML5, 'UTF-8', false);
	}

	public static function getFilesInDirectory($directory, $addDirectoryToFile = false, $sortOrder = null)
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

	public static function getNamespace($class)
	{
		$temp = array_filter(explode('\\', is_object($class) ? get_class($class) : $class));
		return reset($temp);
	}

	public static function implode_objects($object, $prefix = '', $glue = '.')
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

	public static function is_assoc(array $array)
	{
		for (reset($array); is_int(key($array)); next($array));
		return !is_null(key($array));
	}

	public static function json_fix_and_decode($json, $assoc = false)
	{
		$json = str_replace('\'', '"', $json);
		$json = str_replace(["\n", "\r"], '', $json);
		$json = preg_replace('/([{,]+)(\s*)([^"]+?)\s*:/', '$1"$3":', '{'.$json.'}');

		return json_decode($json, $assoc);
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

	public static function param_decode($string)
	{
		return base64_decode(str_replace('~', '/', $string));
	}

	public static function param_encode($string)
	{
		return str_replace('/', '~', base64_encode($string));
	}

	public static function plainText($string)
	{
		return htmlspecialchars(trim(strip_tags($string)));
	}

	public static function preg_grep_keys($pattern, $input, $flags = 0)
	{
		return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
	}

	public static function realurl($url, $scheme, $host, $path = '/')
	{
		return preg_match('/^(ht|f)tps?:\/\/|(mailto:|javascript:|#)/i', $url) > 0 ? $url : $scheme.'://'.$host.resolvePath($path.$url);
	}

	public static function var_name(&$var, $scope = 0)
	{
		$old = $var;

		if (($key = array_search($var = 'unique'.rand().'value', !$scope ? $GLOBALS : $scope)) && $var = $old) {
			return $key;
		}
	}

	public static function redirect($url = null, $responseCode = 302)
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

	public static function array_implode($array, $glue = '=', $delimiter = ';')
	{
		if (!count($array)) {
			return null;
		}

		foreach ($array as $key => $value) {
			$result .= $key.$glue.$value.$delimiter;
		}

		return rtrim($result, $delimiter);
	}

	public static function array_key_position($haystack, $needle)
	{
		if ($haystack instanceof \Mopsis\Core\iCollection) {
			$haystack = $haystack->toArray();
		}

		return intval(array_search($needle, array_keys($haystack)));
	}

	public static function array_merge_recursive_distinct(array &$array1, array &$array2)
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

	public static function array_move_element(&$array, $from, $to)
	{
		$out = array_splice($array, $from, 1);
		array_splice($array, $to, 0, $out);
	}

	public static function array_shuffle($array)
	{
		$keys = array_keys($array);
		shuffle($keys);
		return array_merge(array_flip($keys), $array);
	}

	public static function convertArrayToObject($input)
	{
		return is_array($input) ? (object) array_map(__FUNCTION__, $input) : $input;
	}

	public static function convertObjectToArray($input)
	{
		if (is_object($input)) {
			$input = get_object_vars($input);
		}

		return is_array($input) ? array_map(__FUNCTION__, $input) : $input;
	}

	public static function escape_html($string)
	{
		return htmlspecialchars($string, ENT_COMPAT | ENT_HTML5, 'UTF-8', false);
	}

	public static function getFilesInDirectory($directory, $addDirectoryToFile = false, $sortOrder = null)
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

	public static function getNamespace($class)
	{
		$temp = array_filter(explode('\\', is_object($class) ? get_class($class) : $class));
		return reset($temp);
	}

	public static function implode_objects($object, $prefix = '', $glue = '.')
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

	public static function is_assoc(array $array)
	{
		for (reset($array); is_int(key($array)); next($array));
		return !is_null(key($array));
	}

	public static function json_fix_and_decode($json, $assoc = false)
	{
		$json = str_replace('\'', '"', $json);
		$json = str_replace(["\n", "\r"], '', $json);
		$json = preg_replace('/([{,]+)(\s*)([^"]+?)\s*:/', '$1"$3":', '{'.$json.'}');

		return json_decode($json, $assoc);
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

	public static function param_decode($string)
	{
		return base64_decode(str_replace('~', '/', $string));
	}

	public static function param_encode($string)
	{
		return str_replace('/', '~', base64_encode($string));
	}

	public static function plainText($string)
	{
		return htmlspecialchars(trim(strip_tags($string)));
	}

	public static function preg_grep_keys($pattern, $input, $flags = 0)
	{
		return array_intersect_key($input, array_flip(preg_grep($pattern, array_keys($input), $flags)));
	}

	public static function realurl($url, $scheme, $host, $path = '/')
	{
		return preg_match('/^(ht|f)tps?:\/\/|(mailto:|javascript:|#)/i', $url) > 0 ? $url : $scheme.'://'.$host.resolvePath($path.$url);
	}

	public static function var_name(&$var, $scope = 0)
	{
		$old = $var;

		if (($key = array_search($var = 'unique'.rand().'value', !$scope ? $GLOBALS : $scope)) && $var = $old) {
			return $key;
		}
	}
}

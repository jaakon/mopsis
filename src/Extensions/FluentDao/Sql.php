<?php namespace Mopsis\Extensions\FluentDao;

use PDO;

class Sql
{
	const REQUIRED_VALUE = 1;
	const UNIQUE_VALUE   = 2;

	private static $_configs  = [];
	private static $_instance = [];

	private $_config;
	private $_pdo;

//=== PUBLIC STATIC FUNCTIONS ==================================================

	private function __construct($key = 'default')
	{
		$this->_config = $key;
	}

	public static function db($key = 'default')
	{
		if (!isset(self::$_configs[$key])) {
			throw new \Exception('configuration for connection [' . $key . '] not found');
		}

		if (!isset(self::$_instance[$key])) {
			self::$_instance[$key] = new self($key);
		}

		return self::$_instance[$key];
	}

	public static function addConfig($driver, $host, $database, $user, $password, $options = null, $key = 'default')
	{
		if (isset(self::$_configs[$key])) {
			throw new \Exception('configuration for connection [' . $key . '] is already defined');
		}

		self::$_configs[$key] = [
			'dsn'      => $driver . ':dbname=' . $database . ';host=' . $host,
			'database' => $database,
			'user'     => $user,
			'password' => $password,
			'options'  => $options ? $options : [
				PDO::ATTR_PERSISTENT         => true,
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'
			]
		];
	}

	public static function in($field, Array $values)
	{
		if (count($values) === 0) {
			return 'FALSE';
		}

		return count($values) > 1 ? $field . ' IN (' . implode(',', array_fill(0, count($values), '?')) . ')' : $field . '=?';
	}

	public static function expandQuery($query, $values)
	{
		if (!is_array($query)) {
			return [
				$query,
				$values
			];
		}

		return [
			'`' . implode('`=? AND `', array_keys($query)) . '`=?',
			array_values($query)
		];
	}

//=== PUBLIC METHODS ===========================================================

	public static function buildQuery(array $args)
	{
		$args     = array_filter($args);
		$required = [
			'select' => 'SELECT',
			'from'   => 'FROM'
		];
		$optional = [
			'join'    => 'LEFT JOIN',
			'where'   => 'WHERE',
			'orderBy' => 'ORDER BY',
			'limit'   => 'LIMIT'
		];

		if (count(array_intersect_key($required, $args)) < count($required)) {
			throw new \Exception('required fields not set');
		}

		$query = '';

		foreach (array_merge($required, $optional) as $key => $param) {
			if (is_array($args[$key])) {
				$args[$key] = '(' . implode(') AND (', $args[$key]) . ')';
			}

			if (empty($args[$key])) {
				continue;
			}

			$query .= $param . ' ' . $args[$key] . ' ';
		}

		return trim($query);
	}

	public function insert($table, $data)
	{
		$this->query('INSERT INTO ' . $table . ' (`' . implode('`, `', array_keys($data)) . '`) VALUES (' . substr(str_repeat(',?', count($data)), 1) . ')', array_values($data));

		return $this->_pdo->lastInsertId();
	}

	public function query($query, $values = [])
	{
		if ($this->_pdo === null) {
			$config     = self::$_configs[$this->_config];
			$this->_pdo = new PDO($config['dsn'], $config['user'], $config['password'], $config['options']);
		}

		if (!is_array($values)) {
			$values = [$values];
		}

		$stmt = $this->_pdo->prepare($query);

		if ($stmt === false) {
			throw new \Exception('<b>SQL-ERROR (PREPARE):</b> #' . $this->_pdo->errorCode() . ' ' . print_r($this->_pdo->errorInfo(), true) . '<br/><b>STATEMENT:</b> ' . $query);
		}

		$success = $stmt->execute($values);

		if ($success === false) {
			throw new \Exception('<b>SQL-ERROR (EXECUTE):</b> #' . $stmt->errorCode() . ' ' . print_r($stmt->errorInfo(), true) . '<br/><b>STATEMENT:</b> ' . $query);
		}

		return $stmt;
	}

	public function update($table, $where, $values, $data)
	{
		return $this->query('UPDATE ' . $table . ' SET `' . implode('`=?, `', array_keys($data)) . '`=? WHERE ' . $where, array_merge(array_values($data), is_array($values) ? $values : [$values]))->rowCount();
	}

	public function delete($table, $where, $values = [])
	{
		return $this->query('DELETE FROM ' . $table . ' WHERE ' . $where, $values)->rowCount();
	}

	public function replace($table, $data)
	{
		$this->query('REPLACE INTO ' . $table . ' (`' . implode('`, `', array_keys($data)) . '`) VALUES (' . substr(str_repeat(',?', count($data)), 1) . ')', array_values($data));

		return $this->_pdo->lastInsertId();
	}

	public function getBlob($query, $values = [])
	{
		$stmt = $this->query($query, $values);
		$stmt->bindColumn(1, $blob, PDO::PARAM_LOB);
		$stmt->fetch(PDO::FETCH_BOUND);

		return stream_get_contents($blob);
	}

	public function exists($table, $where = '1=1', $values = [])
	{
		return $this->getValue('SELECT COUNT(*) FROM ' . $table . ' WHERE ' . $where, $values) > 0;
	}

	public function getValue($query, $values = [], $default = null)
	{
		$result = $this->query($query, $values)->fetch(PDO::FETCH_NUM);

		return isset($result[0]) ? $result[0] : $default;
	}

	public function getEnumValues($table, $column)
	{
		$result = $this->getRow('SHOW COLUMNS FROM ' . $table . ' WHERE field=?', [$column], true);

		if (is_array($result) && isset($result['Type'])) {
			preg_match_all("/'(.*?)'/", $result['Type'], $hits);

			return $hits[1];
		}

		return false;
	}

	public function getRow($query, $values = [], $default = null)
	{
		$result = $this->query($query, $values)->fetch(PDO::FETCH_ASSOC);

		return $result !== false ? $result : $default;
	}

	public function getDefaults($table)
	{
		$types       = [];
		$constraints = [];
		$defaults    = [];
		$values      = [];

		foreach ($this->getAll('SHOW COLUMNS FROM ' . $table) as $column) {
			switch (true) {
				case preg_match('/^tinyint\(1\)$/', $column['Type']):
					$types[$column['Field']] = 'boolean';
					break;
				case preg_match('/^(tiny|small|big)?int\(\d+\)( unsigned)?$/', $column['Type']):
					$types[$column['Field']] = 'integer';
					break;
				case preg_match('/^varchar\(\d+\)$/', $column['Type']):
					$types[$column['Field']] = 'string';
					break;
				case preg_match('/^decimal\(\d+,\d+\)( unsigned)?$/', $column['Type']):
					$types[$column['Field']] = 'decimal';
					break;
				case preg_match('/^enum\((.+)\)$/', $column['Type'], $m):
					$types[$column['Field']]  = 'enum';
					$values[$column['Field']] = explode("','", substr($m[1], 1, -1));
					break;
				case preg_match('/^text$/', $column['Type']):
					list($subtype, $param1, $param2) = explode(':', $this->getValue('SELECT column_comment FROM information_schema.columns WHERE table_schema=? AND table_name=? AND column_name=?', [
						self::$_configs[$this->_config]['database'],
						$table,
						$column['Field']
					]));

					switch ($subtype) {
						case 'enum':
							$types[$column['Field']]  = 'enum';
							$values[$column['Field']] = $this->getCol('SELECT DISTINCT `' . $param2 . '` FROM ' . $param1);
							break;
						case 'json':
							$types[$column['Field']] = 'json';
							break;
						case 'model':
							$types[$column['Field']]  = 'model';
							$values[$column['Field']] = explode(',', $param1);
							break;
						default:
							$types[$column['Field']] = 'string';
							break;
					}
					break;
				default:
					$types[$column['Field']] = $column['Type'];
					break;
			}

			$defaults[$column['Field']] = $column['Default'];

			if ($column['Null'] === 'NO' && ($column['Type'] === 'datetime' || $column['Type'] === 'date') && $column['Default'] == null) {
				$defaults[$column['Field']] = now();
			}

			if ($column['Null'] === 'NO' && $column['Extra'] !== 'auto_increment') {
				$constraints[$column['Field']] = self::REQUIRED_VALUE;
			}

			if ($column['Key'] === 'UNI') {
				if (!isset($constraints[$column['Field']])) {
					$constraints[$column['Field']] = 0;
				}

				$constraints[$column['Field']] += self::UNIQUE_VALUE;
			}
		}

		return [
			'types'       => $types,
			'constraints' => $constraints,
			'defaults'    => $defaults,
			'values'      => $values,
		];
	}

	public function getAll($query, $values = [])
	{
		return $this->query($query, $values)->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getCol($query, $values = [], $col = 0)
	{
		return $this->query($query, $values)->fetchAll(PDO::FETCH_COLUMN, $col);
	}

	public function getOutboundReferences($table, $filter = null)
	{
		$database   = self::$_configs[$this->_config]['database'];
		$references = [];

		foreach ($this->getAll("SELECT column_name, referenced_table_name, referenced_column_name FROM information_schema.key_column_usage WHERE table_schema='" . $database . "' AND referenced_table_schema='" . $database . "' AND table_name=?", $table) as $entry) {
			if (is_array($filter) && !in_array($entry['referenced_column_name'], $filter)) {
				continue;
			}

			$references[$entry['column_name']] = [
				'table'  => $entry['referenced_table_name'],
				'column' => $entry['referenced_column_name'],
			];
		}

		return $references;
	}

	public function getInboundReferences($table, $filter = null)
	{
		$database   = self::$_configs[$this->_config]['database'];
		$references = [];

		foreach ($this->getAll("SELECT table_name, column_name, referenced_column_name FROM information_schema.key_column_usage WHERE table_schema='" . $database . "' AND referenced_table_schema='" . $database . "' AND referenced_table_name=?", $table) as $entry) {
			if (preg_match('/^(\w+)_x_(\w+)$/', $entry['table_name'])) {
				continue;
			}

			if (is_array($filter) && !in_array($entry['referenced_column_name'], $filter)) {
				continue;
			}

			$references[$entry['table_name']] = [
				'reference' => $entry['column_name'],
				'column'    => $entry['referenced_column_name'],
			];
		}

		return $references;
	}

//=== PRIVATE METHODS ==========================================================

	public function getCrossboundReferences($table)
	{
		$database   = self::$_configs[$this->_config]['database'];
		$references = [];

		foreach ($this->getAll("SELECT table_name, column_name, referenced_table_name, referenced_column_name FROM information_schema.key_column_usage WHERE table_schema='" . $database . "' AND referenced_table_schema='" . $database . "' AND (table_name LIKE ? OR table_name LIKE ? OR table_name LIKE ?)", [
			$table . '_x_%',
			'%_x_' . $table . '_x_%',
			'%_x_' . $table
		]) as $entry) {
			$references[$entry['table_name']][$entry['referenced_table_name']] = [
				'reference' => $entry['column_name'],
				'column'    => $entry['referenced_column_name'],
			];
		}

		return $references;
	}
}

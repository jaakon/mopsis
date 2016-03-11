<?php
namespace Mopsis\Extensions\FluentDao;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mopsis\Contracts\Model as ModelInterface;
use Mopsis\Core\Cache;
use Mopsis\Extensions\Stringifier;
use Mopsis\Security\Token;
use Mopsis\Types\JSON;
use UnexpectedValueException;

abstract class Model implements ModelInterface
{
    protected $cache = [];

    protected $config;

    protected $data = [];

    protected $stringifier;

    public function __call($name, $args)
    {
        if (static::isJson($this->data[$name])) {
            $this->data[$name]->{$args[0]}
            = $args[1];
            $this->{$name}
            = $this->data[$name]; // triggers saving

            return $this;
        }

        throw new \Exception('unknown function "' . $name . '"');
    }

    public function __construct($id = null)
    {
        $this->config = ModelFactory::getConfig(get_called_class());

        if ($id === null) {
            $this->data = $this->config['defaults'];

            return;
        }

        if (!ctype_digit((string) $id)) {
            throw new \InvalidArgumentException('invalid id "' . $id . '" for class "' . get_called_class() . '"');
        }

        if (!($data = Sql::db()->getRow('SELECT * FROM ' . $this->config['table'] . ' WHERE id=?', $id))) {
            throw new \LengthException('no data found for "' . get_called_class() . ':' . $id . '"');
        }

        foreach ($data as $key => $value) {
            $this->data[$key] = TypeFactory::cast($value, $this->config['types'][$key]);
        }
    }

    public function __get($key)
    {
        if ($key === 'exists') {
            return !!$this->data['id'];
        }

        if ($key === 'ancestor' && method_exists($this, 'ancestor')) {
            return $this->ancestor();
        }

        $method = 'get' . ucfirst($key) . 'Attribute';

        if (method_exists($this, $method)) {
            if ($this->cache[$key] === null) {
                $this->cache[$key] = $this->$method();
            }

            return $this->cache[$key];
        }

        return $this->getAttribute($key);
    }

    public function __invoke($key, $value = null)
    {
        if (func_num_args() === 1) {
            return $this->$key;
        }

        $this->$key = $value;

        return $this;
    }

    public function __isset($key)
    {
        if ($key === 'exists') {
            return true;
        }

        if ($key === 'ancestor') {
            return method_exists($this, 'ancestor');
        }

        if (array_key_exists($key, $this->data)) {
            return true;
        }

        if (method_exists($this, 'get' . ucfirst($key) . 'Attribute')) {
            return true;
        }

        if (ModelFactory::getConnections(get_called_class())[$key]) {
            return true;
        }

        return false;
    }

    public function __set($key, $value)
    {
        unset($this->cache[$key]);

        $method = 'set' . ucfirst($key) . 'Attribute';

        if (method_exists($this, $method)) {
            $this->$method($value);
        }

        $this->setAttribute($key, $value);
    }

    public function __toString()
    {
        return class_basename($this) . ':' . ($this->id ?: 0);
    }

    public static function count($query = null, $values = [])
    {
        list($query, $values) = Sql::expandQuery($query, $values);

        return intval(Sql::db()->getValue(Sql::buildQuery(static::_getDefaultQuery('COUNT(*)', $query, null)), $values));
    }

    public static function create(array $data = [])
    {
        return (new static())->import($data);
    }

    public function delete()
    {
        if (!$this->exists) {
            throw new \Exception('object is not bound');
        }

        if ($this->hasProperty('deleted')) {
            $this->deleted = true;

            return $this;
        }

        foreach (ModelFactory::getConnections(get_called_class()) as $class => $connection) {
            if ($connection['type'] !== 'mixed_inbound') {
                continue;
            }

            foreach ($this->{$class} as $reference) {
                $reference->delete();
            }
        }

        if (!Sql::db()->delete($this->config['table'], 'id=?', $this->data['id'])) {
            throw new \Exception('object "' . get_called_class() . '(' . $this->data['id'] . ')" could not be deleted');
        }

        $this->data['id'] = null;

        return $this;
    }

    public static function find($query = null, $values = [], $orderBy = null, $useCache = true)
    {
        list($query, $values) = Sql::expandQuery($query, $values);

        return ModelFactory::load(get_called_class(), (ctype_digit((string) $query) ? $query : static::get('id', $query, $values, $orderBy)), $useCache);
    }

    public static function findAll($query = null, $values = [], $orderBy = null)
    {
        list($query, $values) = Sql::expandQuery($query, $values);
        $collection           = str_replace('Models', 'Collections', get_called_class());

        return $collection::loadRawData(Sql::db()->getAll(Sql::buildQuery(static::_getDefaultQuery('*', $query, $orderBy)), $values));
    }

    public static function findOrFail($id)
    {
        $instance = static::find($id);

        if ($instance) {
            return $instance;
        }

        throw new ModelNotFoundException();
    }

    public static function get($attribute, $query = null, $values = [], $orderBy = null)
    {
        list($query, $values) = Sql::expandQuery($query, $values);

        return TypeFactory::cast(
            Sql::db()->getValue(
                Sql::buildQuery(static::_getDefaultQuery($attribute, $query, $orderBy)),
                $values
            ),
            ModelFactory::getConfig(get_called_class())['types'][$attribute]
        );
    }

    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $connections = ModelFactory::getConnections(get_called_class());

        if (!($connection = $connections[strtolower($key)])) {
            throw new UnexpectedValueException('property [' . $key . '] is undefined');
        }

        $result = null;

        switch ($connection['type']) {
            case 'outbound':
                $result = ModelFactory::load($connection['class'], $this->data[$connection['attribute']]);
                break;
            case 'inbound':
                $class  = ModelFactory::findClass(strtolower($key));
                $result = $class::findAll($connection['query'], $this->id);
                break;
            case 'mixed_inbound':
                $class  = ModelFactory::findClass($key);
                $result = $class::findAll($connection['query'], (string) $this);
                break;
            case 'crossbound':
                $collection = str_replace('Models', 'Collections', ModelFactory::findClass($key));
                $result     = $collection::load(Sql::db()->getCol(Sql::buildQuery([
                    'select' => $connection['identifier'],
                    'from'   => $connection['pivot'],
                    'where'  => $connection['query']
                ]), $this->id));
                break;
            default:
                throw new \Exception('connection type [' . $connection['type'] . '] is invalid');
        }

        return $this->cache[$key] = $result;
    }

    public static function getCol($attribute, $query = null, $values = [], $orderBy = null)
    {
        list($query, $values) = Sql::expandQuery($query, $values);

        return Sql::db()->getCol(Sql::buildQuery(static::_getDefaultQuery($attribute, $query, $orderBy)), $values);
    }

    public function getHashAttribute()
    {
        return new Token($this);
    }

    public function getSortOrder()
    {
        /**
         * @noinspection PhpParamsInspection
         */
        if (isset($this->orderBy) && is_array($this->orderBy) && count($this->orderBy)) {
            return implode(',', $this->orderBy);
        }

        return 'id';
    }

    public function getTokenAttribute()
    {
        return new Token($this, session_id());
    }

    public static function getValuesFor($attribute)
    {
        $config = ModelFactory::getConfig(get_called_class());

        if ($config['types'][$attribute] !== 'enum') {
            throw new \Exception('property [' . $attribute . '] is undefined or not an enumeration');
        }

        return $config['values'][$attribute];
    }

    public function hasProperty($key)
    {
        return array_key_exists($key, $this->data);
    }

    public function import($import)
    {
        $import = object_to_array($import);

        if (!count($import)) {
            return $this;
        }

        unset($import['id']);

        foreach (array_diff(array_keys($this->data), ['id']) as $key) {
            if (array_key_exists($key, $import) && $import[$key] === null) {
                $this->$key = null;
                unset($import[$key]);
                continue;
            }

            if (isset($import[$key])) {
                $this->$key = $import[$key];
                unset($import[$key]);
                continue;
            }

            if (preg_match('/^(\w+)Id$/', $key, $m) && is_object($import[$m[1]])) {
                $this->{$m[1]}
                = $import[$m[1]];
                unset($import[$m[1]]);
                continue;
            }

            if (static::isJson($this->data[$key])) {
                foreach (preg_filter('/^' . preg_quote($key, '/') . '\.(.+)$/', '$1', array_keys($import)) as $importKey) {
                    $this->data[$key]->{$importKey}
                    = $import[$key . '.' . $importKey];
                    unset($import[$key . '.' . $importKey]);
                }

                $this->$key = $this->data[$key]; // triggers saving
                continue;
            }
        }

        foreach ($import as $key => $value) {
            if ($value !== null && method_exists($this, 'set' . ucfirst($key) . 'Attribute')) {
                $this->$key = $value;
            }
        }

        return $this;
    }

    public function inject($import)
    {
        $import           = object_to_array($import);
        $id               = $this->data['id'] ?: $import['id'];
        $this->data['id'] = null;

        $this->import($import);
        $this->data['id'] = (int) $id;

        return $this;
    }

    public static function isJson($var)
    {
        return $var instanceof JSON;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public static function lists($attribute, $key = 'id', $query = null, $values = [], $orderBy = null)
    {
        list($query, $values) = Sql::expandQuery($query, $values);
        $result               = [];

        foreach (Sql::db()->getAll(Sql::buildQuery(static::_getDefaultQuery([
            $attribute,
            $key
        ], $query, $orderBy)), $values) as $entry) {
            $result[$entry[$key]] = $entry[$attribute];
        }

        return $result;
    }

    public function save($throwBoundException = true)
    {
        if ($this->exists) {
            if ($throwBoundException) {
                throw new \Exception('object is already bound');
            }

            return $this;
        }

        foreach ($this->config['constraints'] as $key => $value) {
            if ($value & Sql::REQUIRED_VALUE && $this->data[$key] === null) {
                throw new \Exception('required property [' . $key . '] is not set');
            }

//            if ($value & Sql::UNIQUE_VALUE && Sql::db()->exists($this->config['table'], $key.'=?', $this->data[$key]))
            //                throw new \Exception('value of property ['.$key.'] is not unique: "'.$this->data[$key].'"');
        }

        foreach ($this->data as &$value) {
            if ($value === false) {
                $value = 0;
            }
        }

        $this->data['id'] = Sql::db()->insert($this->config['table'], $this->data);

        if (!$this->data['id']) {
            throw new \Exception('saving failed (could not insert data)');
        }

        return $this;
    }

    public function set($key, $value)
    {
        $this->$key = $value;

        return $this;
    }

    public function setAttribute($key, $value)
    {
        if ($key === 'id') {
            throw new \Exception('property [id] is readonly');
        }

        if (array_key_exists($key, $this->data)) {
            if ($value === null && !($this->config['constraints'][$key] & Sql::REQUIRED_VALUE)) {
                $this->data[$key] = null;

                if ($this->exists) {
                    Sql::db()->update($this->config['table'], 'id=?', $this->data['id'], [$key => null]);
                }

                return true;
            }

            $type = $this->config['types'][$key];

            if ($type === 'enum' && !in_array($value, $this->config['values'][$key])) {
                throw new \Exception('"' . $value . '" is an invalid value for property [' . $key . ']');
            }

            if ($type === 'model') {
                switch (gettype($value)) {
                    case 'object':
                        $baseClass = __CLASS__;

                        if (!($value instanceof $baseClass) || !in_array(class_basename($value), $this->config['values'][$key]) || !$value->exists) {
                            throw new \Exception('given object is not an allowed instance: [' . implode(', ', $this->config['values'][$key]) . ']');
                        }

                        break;
                    case 'string':
                        if (!preg_match('/^([a-z]+):(\d+)$/i', $value, $m) || !in_array($m[1], $this->config['values'][$key])) {
                            throw new \Exception('"' . $value . '" is an invalid value for property [' . $key . ']');
                        }

                        break;
                    default:
                        throw new \Exception('"' . gettype($value) . '" is not an valid type for property [' . $key . ']');
                        break;
                }
            }

            $this->data[$key] = TypeFactory::cast($value, $type);

            if ($this->exists) {
                Sql::db()->update($this->config['table'], 'id=?', $this->data['id'], [$key => $this->data[$key]]);
            }

            return true;
        }

        if (!($connection = ModelFactory::getConnections(get_called_class())[$key])) {
            throw new \Exception('property [' . $key . '] is undefined');
        }

        if ($value === null) {
            if (isset($this->config['constraints'][$key]) && ($this->config['constraints'][$key] & Sql::REQUIRED_VALUE)) {
                throw new \Exception('required property [' . $key . '] cannot be set to null');
            }

            $this->cache[$key] = null;
            $this->{$connection['attribute']}
            = null;

            return true;
        }

        if (!is_object($value)) {
            throw new \Exception('given value is not an object');
        }

        if ($connection['type'] !== 'outbound') {
            throw new \Exception('connection types other than "outbound" are not supported');
        }

        $model = '\\App\\Models\\' . $connection['class'];

        if (!($value instanceof $model)) {
            throw new \Exception('given object is not an instance of ' . $model);
        }

        $this->cache[$key] = $value;
        $this->{$connection['attribute']}
        = $value->id;

        return true;
    }

    public function stringify()
    {
        return $this->stringifier ?: $this->stringifier = new Stringifier($this);
    }

    public function toArray()
    {
        $data = [];

        foreach (array_keys($this->data) as $key) {
            if (static::isJson($this->$key) && count($this->$key->toArray())) {
                foreach ($this->$key->toArray() as $k => $v) {
                    $data[$key . '.' . $k] = $v;
                }

                continue;
            }

            $data[$key] = $this->$key;
        }

        return $data;
    }

    public function toFormData()
    {
        return $this->stringify()->toArray();
    }

    public static function unpack($token)
    {
        $instance = Token::extract($token);

        if (is_a($instance, get_called_class())) {
            return $instance;
        }

        throw new ModelNotFoundException('Token "' . $token . '" is invalid or outdated.');
    }

    public function update($data)
    {
        return $this->import($data)->save(false);
    }

    protected function _clearCache($key = null)
    {
        if ($key === null) {
            $this->cache = [];
        } else {
            unset($this->cache[$key]);
        }
    }

    protected function _getCachedAttribute($attribute, callable $callback, $ttl = null)
    {
        return Cache::get([
            (string) $this,
            $attribute
        ], $callback, $ttl);
    }

    protected static function _getDefaultQuery($attribute, $query, $orderBy)
    {
        $class = get_called_class();

        return [
            'select'  => implode(', ', array_wrap($attribute)),
            'from'    => ModelFactory::findTable($class),
            'where'   => $query,
            'orderBy' => $orderBy ?: (new $class())->getSortOrder()
        ];
    }

    protected static function _stringToClass($value, $class)
    {
        return $value instanceof $class ? $value : new $class($value);
    }
}

<?php
namespace Mopsis\Types;

class JSON
{
    protected $data;

    public function __construct($data)
    {
        switch (gettype($data)) {
            case 'array':
                $this->data = $data;
                break;
            case 'object':
                $this->data = method_exists($data, 'toArray') ? $data->toArray() : get_object_vars($data);
                break;
            case 'string':
                $this->data = json_decode($data, true) ?: [];
                break;
            default:
                $this->data = [];
                break;
        }
    }

    public function __get($key)
    {
        return $this->data[$key];
    }

    public function __isset($key)
    {
        return array_key_exists($key, $this->data);
    }

    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function __toString()
    {
        return count($this->data) ? json_encode($this->data) : '';
    }

    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    public function length()
    {
        return count($this->data);
    }

    public function toArray()
    {
        return $this->data;
    }
}

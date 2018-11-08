<?php

namespace Draguo\Opensearch\Supports;

use ArrayAccess;

class Config implements ArrayAccess
{
    /**
     * @var array
     */
    protected $items;
    /**
     * Config constructor.
     *
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $items = $this->items;
        if (is_null($key)) {
            return null;
        }
        if (isset($items[$key])) {
            return $items[$key];
        }
        if (false === strpos($key, '.')) {
            return $default;
        }
        foreach (explode('.', $key) as $segment) {
            if (!is_array($items) || !array_key_exists($segment, $items)) {
                return $default;
            }
            $items = $items[$segment];
        }
        return $items;
    }

    public function set($key, $value)
    {
        $this->items[$key] = $value;
    }

    public function only(array $keys)
    {
        $return = [];
        foreach ($keys as $key) {
            $value = $this->get($key);
            if (!is_null($value)) {
                $return[$key] = $value;
            }
        }
        return $return;
    }

    /**
     * Whether a offset exists.
     *
     * @see  http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return bool true on success or false on failure.
     *              </p>
     *              <p>
     *              The return value will be casted to boolean if non-boolean was returned
     *
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }
    /**
     * Offset to retrieve.
     *
     * @see  http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types
     *
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
    /**
     * Offset to set.
     *
     * @see  http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        if (isset($this->items[$offset])) {
            $this->items[$offset] = $value;
        }
    }
    /**
     * Offset to unset.
     *
     * @see  http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        if (isset($this->items[$offset])) {
            unset($this->items[$offset]);
        }
    }

}
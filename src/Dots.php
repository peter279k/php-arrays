<?php

namespace MadeSimple\Arrays;

/**
 * Class Dots
 *
 * @package MadeSimple\Arrays
 */
class Dots implements \ArrayAccess
{
    /**
     * @var array
     */
    private $array;

    /**
     * DotArr constructor.
     *
     * @param array  $array
     */
    function __construct(array $array = [])
    {
        $this->setArray($array);
    }

    /**
     * Store an array.
     *
     * @param array  $array
     */
    public function setArray(array $array)
    {
        if (Arr::accessible($array)) {
            $this->array = $array;
        }
    }

    /**
     * Store an array as a reference.
     *
     * @param array  $array
     */
    public function setReference(&$array)
    {
        if (Arr::isAssoc($array)) {
            $this->array = &$array;
        }
    }


    public function offsetExists($offset)
    {
        return ArrDots::has($this->array, $offset);
    }

    public function offsetGet($offset)
    {
        return ArrDots::get($this->array, $offset);
    }

    public function offsetSet($offset, $value)
    {
        ArrDots::set($this->array, $offset, $value);
    }

    public function offsetUnset($offset)
    {
        ArrDots::remove($this->array, $offset);
    }
}
<?php

namespace MadeSimple\Arrays;

use ArrayAccess;

/**
 * Class ArrDots
 *
 * @package MadeSimple\Arrays
 */
class ArrDots
{
    /**
     * Implode a multi-dimensional associative array into a single level dots array.
     *
     * @param array  $array
     * @param string $prepend
     *
     * @return array
     */
    public static function implode($array, $prepend = '')
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, static::implode($value, $prepend.$key.'.'));
            } else {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }

    /**
     * Explode a single level dots array into a multi-dimensional associative array.
     *
     * @param array $array
     *
     * @return array
     */
    public static function explode($array)
    {
        $results = [];

        foreach ($array as $key => $value) {
            static::set($results, $key, $value);
        }

        return $results;
    }

    /**
     * @param ArrayAccess|array $array
     * @param array|string      $keys
     *
     * @return void
     */
    public static function remove(&$array, $keys)
    {
        $original = &$array;
        $keys     = (array) $keys;

        if (!Arr::accessible($array)) {
            return;
        }
        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            if (Arr::exists($array, $key)) {
                unset($array[$key]);

                continue;
            }

            // Clean up before each pass
            $array = &$original;
            $parts = explode('.', $key);

            while (count($parts) > 1) {
                $part = array_shift($parts);
                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }

    /**
     * Get an item from a multi-dimensional associative array using "dots" notation.
     *
     * @param ArrayAccess|array $array
     * @param string            $key
     * @param mixed             $default
     *
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        if (!Arr::accessible($array)) {
            return $default;
        }

        if (null === $key) {
            return $array;
        }

        if (Arr::exists($array, $key)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (Arr::accessible($array) && Arr::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * Determine if an item or items exist in an multi-dimensional associative array using "dots" notation.
     *
     * @param ArrayAccess|array $array
     * @param string|string[]   $keys
     * @param null|string       $wildcard
     *
     * @return bool
     */
    public static function has($array, $keys, $wildcard = null)
    {
        if (null === $keys) {
            return false;
        }

        $keys = (array) $keys;

        if (!$array) {
            return false;
        }

        foreach ($keys as $key) {
            $subKeyArray = $array;

            if (Arr::exists($array, $key)) {
                continue;
            }

            $keySegments = explode('.', $key);
            foreach ($keySegments as $k => $segment) {
                if (Arr::accessible($subKeyArray) && Arr::exists($subKeyArray, $segment)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else if ($segment === $wildcard && !empty($subKeyArray)) {
                    if ($k+1 === count($keySegments)) {
                        return true;
                    }
                    $subSubKey = implode('.', array_slice($keySegments, $k+1));
                    foreach ($subKeyArray as $subSubKeyArray) {
                        if (static::has($subSubKeyArray, $subSubKey, $wildcard)) {
                            return true;
                        }
                    }

                } else {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get a value from the array and remove it.
     *
     * @param ArrayAccess|array $array
     * @param string            $key
     * @param mixed             $default
     *
     * @return mixed
     */
    public static function pull(&$array, $key, $default = null)
    {
        $value = static::get($array, $key, $default);
        static::remove($array, $key);

        return $value;
    }

    /**
     * Set an multi-dimensional associative array item to $value using "dots" notation.
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $value
     *
     * @return array
     */
    public static function set(&$array, $key, $value)
    {
        if (null === $key) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Get a subset of items from an multi-dimensional associative $array using "dots" notation for $keys.
     *
     * @param array           $array
     * @param string|string[] $keys
     *
     * @return array
     */
    public static function only($array, $keys)
    {
        $imploded = static::implode($array);
        $only     = Arr::only($imploded, $keys);
        return static::explode($only);
    }
}
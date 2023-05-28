<?php

namespace Surveyforge\Surveyforge\Utils;

use Illuminate\Support\Collection;

class ArrayUtils
{
    /**
     * Runs through subnodes of an array recursively and calls a callback function on each node, while updating each node by reference
     * @param $arr
     * @param callable $callback
     * @return void
     */
    public static function updateNodeRecursive(&$arr, callable $callback)
    {
        foreach ($arr as $key => &$value) {
            if (is_array($value) || $value instanceof Collection) {
                $callback($value, $key);
                if (is_array($value) || $value instanceof Collection) {
                    self::updateNodeRecursive($value, $callback);
                }
            }
        }
        unset($value);
    }


    public static function removeNodesRecursive(&$arr, callable $callbackCondition)
    {
        foreach ($arr as $key => &$value) {
            if (is_array($value) || $value instanceof Collection) {
                $shouldRemove=$callbackCondition($value, $key);
                if ($shouldRemove) {
                    unset($arr[$key]);
                }else{
                    if (is_array($value) || $value instanceof Collection) {
                        self::removeNodesRecursive($value, $callbackCondition);
                    }
                }
            }
        }
        unset($value);
    }


}

<?php


if (!function_exists('toBoolean'))
{
    /**
     * Converts a value into a boolean.
     * @param $value
     * @return bool
     */
    function to_boolean($value)
    {
        if(is_numeric($value))
        {
            return (bool)$value;
        }
        elseif(is_string($value))
        {
            switch($value)
            {
                case 'yes':
                case 'true':
                case 'on':
                    return true;
                default:
                    return false;
            }
        }
        elseif(is_bool($value))
        {
            return $value;
        }
    }
}
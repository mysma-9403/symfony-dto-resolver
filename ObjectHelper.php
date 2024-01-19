<?php
declare(strict_types=1);

namespace App\Utils;

class ObjectHelper
{
    public static function trimInObject(object $obj): object
    {
        foreach (get_object_vars($obj) as $key => $var) {
            if (is_string($var)) {
                $obj->{$key} = \trim($var);
            }
        }

        return $obj;
    }
}

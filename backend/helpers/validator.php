<?php

class Validator {

    /*
    |--------------------------------------------------------------------------
    | REQUIRED
    |--------------------------------------------------------------------------
    */

    public static function required($value, $fieldName) {

        if(empty(trim($value))) {

            throw new Exception(
                $fieldName . " wajib diisi"
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NUMERIC
    |--------------------------------------------------------------------------
    */

    public static function numeric($value, $fieldName) {

        if(!is_numeric($value)) {

            throw new Exception(
                $fieldName . " harus berupa angka"
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MIN VALUE
    |--------------------------------------------------------------------------
    */

    public static function min($value, $min, $fieldName) {

        if($value < $min) {

            throw new Exception(
                $fieldName .
                " minimal " .
                $min
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MAX LENGTH
    |--------------------------------------------------------------------------
    */

    public static function maxLength($value, $max, $fieldName) {

        if(strlen($value) > $max) {

            throw new Exception(
                $fieldName .
                " maksimal " .
                $max .
                " karakter"
            );
        }
    }
}
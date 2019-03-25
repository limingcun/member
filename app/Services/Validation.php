<?php

namespace App\Services;

use Illuminate\Validation\Validator;

class Validation extends Validator{
    // 验证字符和数字组合
    public function ValidateNumstr($attribute, $value, $parameters){
        return preg_match('/^[0-9a-zA-Z]+$/', $value);
    }
    
    // 验证两位浮点数字
    public function ValidateFloat($attribute, $value, $parameters) {
        return preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $value);
    }
    
    // 验证多个值判断(true过,false不过)
    public function ValidateFieldif($attribute, $value, $parameters) {
        $arr = \request()->all();
        $flag = false;
        for($i = 0; $i<count($parameters); $i+=2) {
            if ($arr[$parameters[$i]] != $parameters[$i+1]) {
                $flag = true;
            }
        }
        if (!$flag) {
            if (!$value) {
                return false;
            }
        }
        return true;
    }
}
<?php

namespace Core;

class Validator
{

    /**
     * Проверка правильности e-mail
     * @param $str
     * @return bool
     */
    public static function email($str)
    {
        return filter_var($str, FILTER_VALIDATE_EMAIL);
    }


    /**
     * Проверка правильности ИНН
     * @param $str
     * @return bool
     */
    public static function inn($str)
    {
        if (preg_match('/\D/', $str)) return false;

        $str = (string)$str;
        $len = strlen($str);

        if ($len === 10) {
            return $str[9] === (string)(((
                        2 * $str[0] + 4 * $str[1] + 10 * $str[2] +
                        3 * $str[3] + 5 * $str[4] + 9 * $str[5] +
                        4 * $str[6] + 6 * $str[7] + 8 * $str[8]
                    ) % 11) % 10);
        } elseif ($len === 12) {
            $num10 = (string)(((
                        7 * $str[0] + 2 * $str[1] + 4 * $str[2] +
                        10 * $str[3] + 3 * $str[4] + 5 * $str[5] +
                        9 * $str[6] + 4 * $str[7] + 6 * $str[8] +
                        8 * $str[9]
                    ) % 11) % 10);
            $num11 = (string)(((
                        3 * $str[0] + 7 * $str[1] + 2 * $str[2] +
                        4 * $str[3] + 10 * $str[4] + 3 * $str[5] +
                        5 * $str[6] + 9 * $str[7] + 4 * $str[8] +
                        6 * $str[9] + 8 * $str[10]
                    ) % 11) % 10);
            return $str[11] === $num11 && $str[10] === $num10;
        }

        return false;
    }


}
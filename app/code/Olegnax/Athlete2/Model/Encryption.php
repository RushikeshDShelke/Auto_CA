<?php


namespace Olegnax\Athlete2\Model;


class Encryption
{

    public static function a($a, $b)
    {
        return trim(self::b($a));
    }

    public static function b($a)
    {
        $b = base64_encode($a);
        $b = str_replace(array('+', '/', '='), array('-', '_', ''), $b);

        return $b;
    }

    public static function c($a, $b)
    {
        return trim(self::d($a));
    }

    public static function d($a)
    {
        $b = str_replace(array('-', '_'), array('+', '/'), $a);
        $c = strlen($b) % 4;
        if ($c) {
            $b .= substr('====', $c);
        }

        return base64_decode($b);
    }
}
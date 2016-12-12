<?php

namespace T4\Extensions\Captcha;

use T4\Core\Session;

class Extension
    extends \T4\Core\Extension
{

    const KEYSTRING_KEY = 'captcha_keystring';



    public function init()
    {
        $assets = $this->app->assets;
        $assets->publish($this->assetsPath . '/src/fonts');

        require_once __DIR__ . DS . 'src' . DS . 'kcaptcha.php';
    }

    public function generateImage($config = null)
    {
        $captcha = new \KCAPTCHA();
        Session::set(self::KEYSTRING_KEY, strtolower($captcha->getKeyString()));
        die;
    }

    public function checkKeyString($string)
    {
        return Session::get(self::KEYSTRING_KEY) == strtolower($string);
    }

}
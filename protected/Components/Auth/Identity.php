<?php

namespace App\Components\Auth;

use App\Models\User;
use App\Models\UserSession;
use T4\Mvc\Application;
use T4\Core\Session;

class Identity
    extends \T4\Auth\Identity
{

    const  ERROR_INVALID_e_mail = 105;
    const  ERROR_INVALID_CAPTCHA = 102;
    const ERROR_INVALID_CODE = 103;
    const ERROR_INVALID_TIME = 104;
    const AUTH_COOKIE_NAME = 'T4auth';

    public function authenticate($data)
    {
        $errors = new MultiException();

        if (empty($data->email)) {
            $errors->add('Не введен e-mail', self::ERROR_INVALID_EMAIL);
        }
        if ( empty($data->password)) {
            $errors->add('Не введен пароль', self::ERROR_INVALID_PASSWORD);
        }

        if (!$errors->isEmpty())
            throw $errors;

        $user = User::findByEmail($data->email);
        if (empty($user)) {
            $errors->add('Пользователь с e-mail ' . $data->email . ' не существует', self::ERROR_INVALID_EMAIL);
        }

        if (!$errors->isEmpty())
            throw $errors;

        if (!\T4\Crypt\Helpers::checkPassword($data->password, $user->password)) {
            $errors->add('Неверный пароль', self::ERROR_INVALID_PASSWORD);
        }

        if (!$errors->isEmpty())
            throw $errors;

        $this->login($user);
        Application::getInstance()->user = $user;
        return $user;
    }

    public function getUser()
    {
        if (!\T4\Http\Helpers::issetCookie(self::AUTH_COOKIE_NAME))
            return null;

        $hash = \T4\Http\Helpers::getCookie(self::AUTH_COOKIE_NAME);
        $session = UserSession::findByHash($hash);
        if (empty($session)) {
            \T4\Http\Helpers::unsetCookie(self::AUTH_COOKIE_NAME);
            return null;
        }

        if ($session->userAgentHash != md5($_SERVER['HTTP_USER_AGENT'])) {
            $session->delete();
            \T4\Http\Helpers::unsetCookie(self::AUTH_COOKIE_NAME);
            return null;
        }

        return $session->user;
    }

    public function register($data)
    {
        $errors = new MultiException();

        if (empty($data->email)) {
            $errors->add('Не введен e-mail', self::ERROR_INVALID_EMAIL);
        }
        /*
        if (0==preg_match_all('#([a-z0-9\-\.\_]+@[a-z0-9\-]+\.[a-z]+$)#isU', $_POST['email'], $matches)){
            $errors->add('Введен не e-mail', self::ERROR_INVALID_e_mail);
        }
  */
        if (empty($data->password)) {
            $errors->add('Не введен пароль', self::ERROR_INVALID_PASSWORD);
        }

        if (empty($data->password2)) {
            $errors->add('Не введено подтверждение пароля', self::ERROR_INVALID_PASSWORD);
        }

        if ( $data->password2 != $data->password) {
            $errors->add('Введенные пароли не совпадают', self::ERROR_INVALID_PASSWORD);
        }

        if (Application::getInstance()->config->extensions->captcha->register) {
            if (!Application::getInstance()->extensions->captcha->checkKeyString($data->captcha)) {
                $errors->add('Не правильно введены символы с картинки', self::ERROR_INVALID_CAPTCHA);
            }
        }



        $user = User::findByEmail($data->email);
        if (!empty($user)) {
            $errors->add('Такой e-mail уже зарегистрирован', self::ERROR_INVALID_e_mail);
        }

        if (!$errors->isEmpty())
            throw $errors;

        $user = new User();
        $user->email = $data->email;
        $user->password = \T4\Crypt\Helpers::hashPassword($data->password);
        $user->save();

        return $user;
    }

    public function restorePassword($data)
    {
        $errors = new MultiException();

        if (empty($data->email)) {
            $errors->add('Не введен e-mail', self::ERROR_INVALID_EMAIL);
        }

        $user = User::findByEmail($data->email);
        if (empty($user)) {
            $errors->add('Пользователь с e-mail ' . $data->email . ' не существует', self::ERROR_INVALID_EMAIL);
        }

        if ($data->step == 1) {

            if (Session::get('controlstring')['controlstring']['string'] != $data->code) {
                $errors->add('Неправильный код', self::ERROR_INVALID_CODE);
            }

            if (time() - Session::get('controlstring')['controlstring']['start_time'] > 240) {
                $errors->add('Истекло время ожидания', self::ERROR_INVALID_TIME);
            }

        }
        if (!$errors->isEmpty())
            throw $errors;

        if ($data->step == 2) {
            if (empty($data->password)) {
                $errors->add('Не введен пароль', self::ERROR_INVALID_PASSWORD);
            }

            if (empty($data->password2)) {
                $errors->add('Не введено подтверждение пароля', self::ERROR_INVALID_PASSWORD);
            }

            if ($data->password2 != $data->password) {
                $errors->add('Введенные пароли не совпадают', self::ERROR_INVALID_PASSWORD);
            }

            if (!$errors->isEmpty())
                throw $errors;

            $user = new User();
            $user->email = $data->email;
            $user->password = \T4\Crypt\Helpers::hashPassword($data->password);
            $user->save();

            return $user;
        }
    }

    /**
     * @param \App\Models\User $user
     */
    public function login($user)
    {
        $app = Application::getInstance();
        $expire = isset($app->config->auth) && isset($app->config->auth->expire) ?
            time() + $app->config->auth->expire :
            0;
        $hash = md5(time() . $user->password);

        \T4\Http\Helpers::setCookie(self::AUTH_COOKIE_NAME, $hash, $expire);

        $session = new UserSession();
        $session->hash = $hash;
        $session->userAgentHash = md5($_SERVER['HTTP_USER_AGENT']);
        $session->user = $user;
        $session->save();
    }

    public function logout()
    {
        if (!\T4\Http\Helpers::issetCookie(self::AUTH_COOKIE_NAME))
            return;

        $hash = \T4\Http\Helpers::getCookie(self::AUTH_COOKIE_NAME);
        $session = UserSession::findByHash($hash);
        if (empty($session)) {
            \T4\Http\Helpers::unsetCookie(self::AUTH_COOKIE_NAME);
            return;
        }

        $session->delete();
        \T4\Http\Helpers::unsetCookie(self::AUTH_COOKIE_NAME);

        $app = Application::getInstance();
        $app->user = null;
    }

}
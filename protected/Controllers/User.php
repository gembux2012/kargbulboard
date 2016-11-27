<?php

namespace App\Controllers;

use App\Components\Auth\Identity;
use T4\Mvc\Controller;
use T4\Orm\Exception;
use T4\Core;
use T4\Mail\Sender;




class User
    extends Controller
{

    public function actiontLogin($login = null)
    {
        if (null !== $login) {
            try {
                $identity = new Identity();
                $user = $identity->authenticate($login);
                $this->data->login=true;
                $this->app->flash->message = 'Добро пожаловать, ' . $user->email . '!';
               $this->redirect('/');
            } catch (\App\Components\Auth\MultiException $e) {


                    }

            }
            $this->data->email = $login->email;
    }

    public function actionGetLogin($login = null)
    {
        $err = false;
        if (null !== $login) {
            $user = \App\Models\User::findByEmail($login->email);
            if (empty($user)) {
                $this->data->errors = ['errlogin' => 'Пользователь c таким e-mail не зарегестрирован! '];
                $err = true;
            } else if (!\T4\Crypt\Helpers::checkPassword($login->password, $user->password)) {
                $this->data->errors = ['errpassword' => 'Неверный пароль!'];
                $err = true;
            }

            if (!$err) {
                $identity = new Identity();
                $identity->login($user);
                Application::getInstance()->user = $user;

            }
        }
    }



    public function actionLogout()
    {
        $identity = new Identity();
        $identity->logout();
        $this->redirect('/');
    }

    public function actionRegister($register = null)
    {
        if (null !== $register) {
            try {
                $identity = new Identity();
                $user = $identity->register($register);
                $identity->login($user);
                $this->app->flash->message = 'Добро пожаловать, ' . $user->email . '!';
                $this->redirect('/');
            } catch (\App\Components\Auth\MultiException $e) {
                $this->data->errors = $e;
            }
            $this->data->email = $register->email;
        }
    }

    public function  actionRestorePassword()
    {
        $email=$this->app->request->post->email;
        $user = \App\Models\User::findByEmail($email);
        if (empty($user)) {
            $this->data->errors="Пользователь с таки e-mail не зарегестрирован";
        } else {
            $newpassword=\T4\Crypt\Helpers::newPassword();
            $user->password=\T4\Crypt\Helpers::hashPassword($newpassword);
            $user->save();
            $mail = new  Sender();
            try {
                $mail->sendMail($email, 'Восстановление пароля', "Ваш пароль для доступа к сайту {{app.config.domain}}:". $newpassword);
            } catch (\phpmailerException $e) {
                $this->data->errors = $e;

            }
        }

    }

    /*
public function actionRestorePassword($restore = null)
{
    if (null !== $restore) {
        try {
            $identity = new Identity();
            $user = $identity->restorePassword($restore);
            if (null == Session::get('controlstring')) {
                $controlstring = ['controlstring' => ['string' => User::getRandomString(6, 'all'), 'start_time' => time()]];
                Session::set('controlstring', $controlstring);
                $mail = new Sender();
                $mail->sendMail($restore->email, 'Востановление пароля', 'Для восстановления пароля введите этот код: ' .
                    $controlstring['controlstring']['string']);
                $this->data->step = 1;
                $this->data->email = $restore->email;
            } else if ($restore->step == 2) {
                $identity->login($user);
                $this->app->flash->message = 'Добро пожаловать, ' . $user->email . '!';
                $this->redirect('/');
            } else {
                $this->data->step = 2;
                $this->data->email = $restore->email;
            }

        } catch (\App\Components\Auth\MultiException $e) {

            $this->data->errors = $e;
            $this->data->email = $restore->email;
            foreach ($e as $error) {
                switch ($error->getCode()) {
                    case 103: {
                        $this->data->step = 1;
                        break;
                    }
                    case 104: {
                        $this->data->step = 100;
                        break;
                    }
                    case 101: {
                        $this->data->step = 2;
                        break;
                    }
                }
            }
        }
    } else {
        Session::clear('controlstring');
        $this->data->step = 0;
    }
}
*/

}
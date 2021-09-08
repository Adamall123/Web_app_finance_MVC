<?php

namespace App\Controllers;
use \App\Models\User;
use \App\Models\UserDB;
use \Core\View;

/**
 * Signup controller
 *
 * PHP version 7.0
 */
class Signup extends \Core\Controller
{

    /**
     * Show the index page
     *
     * @return void
     */
    public function newAction()
    {
        View::renderTemplate('Signup/new.html');
    }

    public function createAction()
    {
        $user = new User($_POST);
        $userDB = new UserDB();
        if ($userDB->save($user)) {
            $userDB->sendActivationEmail($user);
            $this->redirect('/signup/success');
        }else {
            View::renderTemplate('Signup/new.html', [
                'user' => $user
            ]);
        }
    }
    public function successAction()
    {
        View::renderTemplate('Signup/success.html');
    }
    public function activateAction()
    {
        UserDB::activate($this->route_params['token']);
         $this->redirect('/signup/activated');
    }
    public function activatedAction()
    {
        View::renderTemplate('Signup/activated.html');
    }
}

<?php

namespace App\Controllers; 
use Core\View;
use App\Models\User;
use \App\Auth; 
use \App\Flash;

class Login extends \Core\Controller{

    public function newAction(){
        View::renderTemplate('Login/new.html');
    }

    public function createAction()
    {       
            $user = User::authenticate($_POST['email'], $_POST['password']);
            $rememberMe = isset($_POST['remember_me']);
            if($user){
                Auth::login($user, $rememberMe);
                Flash::addMessage('Login successful.');
                $this->redirect(Auth::getReturnToPage());
            }else{
                Flash::addMessage('Login unsuccesful, please try again.', Flash::WARNING);
                View::renderTemplate('Login/new.html',[
                   'email' => $_POST['email'],
                   'remember_me' => $rememberMe
               ]);
            }
    }
    public function destroyAction()
    {
        Auth::logout();
        $this->redirect('/login/show-logout-message');
    }

    public function showLogoutMessageAction()
    {
        Flash::addMessage('Logout succesful.');
        $this->redirect('/');
    }
}
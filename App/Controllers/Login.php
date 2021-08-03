<?php

namespace App\Controllers; 
use Core\View;
use App\Models\User;
class Login extends \Core\Controller{

    public function newAction(){
        View::renderTemplate('Login/new.html');
    }

    public function createAction()
    {
            $user = User::authenticate($_POST['email'], $_POST['password']);
            if($user){
                session_regenerate_id(true); 
                $_SESSION['user_id'] = $user->id;
                $this->redirect('/');
            }else{
               View::renderTemplate('Login/new.html',[
                   'email' => $_POST['email'],
               ]);
            }
    }
    public function destroyAction()
    {
        //echo $_SESSION['user_id'];
        // Unset all of the session variables.
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        // Finally, destroy the session.
        session_destroy();
        $this->redirect('/');
    }
}
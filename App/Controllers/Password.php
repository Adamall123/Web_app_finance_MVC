<?php 

namespace App\Controllers; 
use Core\View;
use App\Models\UserDB;
use App\Models\User;
class Password extends \Core\Controller 
{
    public function forgotAction()
    {
        View::renderTemplate('Password/forgot.html');
    }
    public function requestResetAction()
    {
        UserDB::sendPasswordReset($_POST['email']);
        View::renderTemplate('Password/reset-requested.html');
    }
   public function resetAction()
   {
       $token = $this->route_params['token'];

       $user = $this->getUserOrExit($token);

       View::renderTemplate('Password/reset.html', [
        'token' => $token
    ]);
   }
   public function resetPasswordAction()
   {
        $token =  $_POST['token'];
        
        $user = $this->getUserOrExit($token);
        $userDB = new UserDB();
        
        if ($userDB->resetPassword($_POST['password'], $user)) {
            View::renderTemplate('Password/reset_success.html');
        } else {
            View::renderTemplate('Password/reset.html', [
                'token' => $token,
                'user' => $user
            ]);
        }
   }
   protected function getUserOrExit($token)
   {
         $user = UserDB::findByPasswordReset($token);
         if ($user) {
             return $user; 
         } else {
             View::renderTemplate('Password/token_expired.html');
             exit;
         }
   }
}
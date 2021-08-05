<?php 

namespace App;
use \App\Models\User;
class Auth 
{
    public static function login($user)
    {
        session_regenerate_id(true); 
        $_SESSION['user_id'] = $user->id;
    }
    public static function logout()
    {   
        //echo $_SESSION['user_id'];
        // Unset all of the session variables.
        //return session to variable and return it to the message
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
        // // Finally, destroy the session.
         session_destroy();
    }
    
    public static function getUser()
    {
        if (isset($_SESSION['user_id'])) {
            return User::findByID($_SESSION['user_id']);
        }
    }
    public static function rememberRequestedPage()
    {
        $_SESSION['return_to'] = $_SERVER['REQUEST_URI'];
    }
    public static function getReturnToPage()
    {
       return $_SESSION['return_to'] ?? '/';
    }
    
}
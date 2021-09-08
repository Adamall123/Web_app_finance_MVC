<?php

namespace App\Models;

use PDO;
use \App\Token;
use  \App\Mail;
use Core\View;
use \Core\Model;

class Walidator
{
    public function validate($user)
    {

        if ($user->name == '') {
            $user->errors[] = 'Name is required';
        }
        if (filter_var($user->email, FILTER_VALIDATE_EMAIL) === false) {
            $user->errors[] = 'Invalid email';
        }
        if($user->emailExists($user->email, $user->id ?? null)){
            $user->errors[] = 'email already taken';
        }
        if (isset($user->password)) {
            if (strlen($user->password) < 6) {
                $user->errors[] = 'Please enter at least 6 characters for the password';
            }
            if (preg_match('/.*[a-z]+.*/i', $user->password) === 0) {
                $user->errors[] = 'Password need at least one letter';
            }
            if (preg_match('/.*\d+.*/i', $user->password) === 0) {
                $user->errors[] = 'Password need at least one number';
            }
        }
    }
}

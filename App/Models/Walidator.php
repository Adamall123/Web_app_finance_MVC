<?php

namespace App\Models;


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
    public function validateLengthOfCategory($text)
    {
        if (strlen($text) > 20 || strlen($text) < 3) {
            $this->errors[] = "A category name must have characters between 3 and 20.";
            return 0;
        }
        return 1;
    }
    public function validateAmountAndComment($params)
    {   
        if (! preg_match('/^[0-9]+(?:\.[0-9]{0,2})?$/', $_POST['amount']))
            {
                $this->errors[] = 'Number is required';
            } 
            if (!preg_match('/^[.]{0,30}$/', $_POST['comment']))
            {
                $this->errors['comment'] = 'Comment length can not be longer than 30 characters.'; 
            }     
    }
}

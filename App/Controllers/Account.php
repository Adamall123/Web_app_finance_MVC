<?php

namespace App\Controllers;

use \App\Models\User;

class Account extends \Core\Controller
{
    public function validateEmailAction()
    {
        $isValid = ! User::emailExists($_GET['email']);
        header('Content-Type: application/json');
        echo json_encode($isValid);
    }
}
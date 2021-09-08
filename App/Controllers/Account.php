<?php

namespace App\Controllers;

use \App\Models\User;
use \App\Models\UserDB;
class Account extends \Core\Controller
{
    public function validateEmailAction()
    {
        
        $isValid = ! UserDB::emailExists($_GET['email'], $_GET['ignore_id'] ?? null);
        header('Content-Type: application/json');
        echo json_encode($isValid);
    }
}
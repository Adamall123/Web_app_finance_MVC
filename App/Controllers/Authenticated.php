<?php 

namespace App\Controllers;
use App\Models\User;
abstract class Authenticated extends \Core\Controller
{
    protected function before()
    {
        //require login - all controllers which will inherited this class will be required to being logged in.
        
        $this->requireLogin();
    }
}
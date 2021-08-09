<?php

namespace App\Controllers;

use Core\View;
use App\Models\User;
use \App\Auth; 
use \App\Flash;


class Balance extends Authenticated
{
    protected $value=1;
    protected function before()
    {
        parent::before();
        $this->user = Auth::getUser();
    }
    public function showAction()
    {
        if (isset($_POST['dateCategory'])){
            $this->value = $_POST['dateCategory'];
        }
        $this->allIncomesOfUser = User::fillIncomesOfUser($this->user->id, $this->value );
        $this->allExpensesOfUser = User::fillExpensesOfUser($this->user->id, $this->value);
        View::renderTemplate('Balance/show.html', [
            'allIncomesOfUser' => $this->allIncomesOfUser,
            'allExpensesOfUser' => $this->allExpensesOfUser,
            'user' => $this->user,
            'selected' => $this->value
        ]);
    }

    public function changeAction()
    {
        
    }
    
}

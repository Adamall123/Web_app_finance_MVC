<?php

namespace App\Controllers;

use Core\View;
use App\Models\User;
use \App\Auth; 
use \App\Flash;


class Balance extends Authenticated
{
   
    protected function before()
    {
        parent::before();
        $this->user = Auth::getUser();
    }
    public function showAction()
    {
        $this->allIncomesOfUser = User::fillIncomesOfUser($this->user->id);
        $this->allExpensesOfUser = User::fillExpensesOfUser($this->user->id);
        View::renderTemplate('Balance/show.html', [
            'allIncomesOfUser' => $this->allIncomesOfUser,
            'allExpensesOfUser' => $this->allExpensesOfUser
        ]);
    }
    
}

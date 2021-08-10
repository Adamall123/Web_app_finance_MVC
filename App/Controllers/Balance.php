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
        View::renderTemplate('Balance/show.html', [
            'user' => $this->user
        ]);
    }

    public function changeAction()
    {
        if (isset($_POST['date_id'])){
            $this->value = $_POST['date_id'];
        }
        $this->allIncomesOfUser = User::fillIncomesOfUser($this->user->id, $this->value );   
        $this->allExpensesOfUser = User::fillExpensesOfUser($this->user->id, $this->value); 
        $this->sumFromIncomesAndExpenses = User::sumFromIncomesAndExpenses($this->user->id, $this->value);
        echo json_encode(array("allIncomesOfUser" => $this->allIncomesOfUser,
                                "allExpensesOfUser" => $this->allExpensesOfUser,
                                "sumFromIncomesAndExpenses" => $this->sumFromIncomesAndExpenses));
    }
}

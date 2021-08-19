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
        View::renderTemplate('Balance/show.html', [
            'user' => $this->user
        ]);
    }
    public function changeAction()
    {
        if (isset($_POST['start'])){
            $this->startDate = $_POST['start'];
            $this->endDate = $_POST['end'];
        }
        
        echo json_encode(array("allIncomesOfUser" => $this->user->getSumSpendMoneyOnEachIncomeOfUser($this->startDate, $this->endDate),
                                "allExpensesOfUser" => $this->user->getSumSpendMoneyOnEachExpenseOfUser($this->startDate, $this->endDate),
                                "sumFromIncomesAndExpenses" => $this->user->sumFromIncomesAndExpenses($this->startDate, $this->endDate) ));
    }
}

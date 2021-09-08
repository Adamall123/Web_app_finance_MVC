<?php

namespace App\Controllers;

use Core\View;
use App\Models\User;
use \App\Auth; 
use App\Models\IncomesDB;
use App\Models\ExpenseDB;

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
        $incomeDB = new IncomesDB();
        $expenseDB = new ExpenseDB();
        if (isset($_POST['start'])){
            $this->startDate = $_POST['start'];
            $this->endDate = $_POST['end'];
        }
        
        echo json_encode(array("allIncomesOfUser" => $incomeDB->getSumSpendMoneyOnEachIncomeOfUser($this->startDate, $this->endDate, $this->user),
                                "allExpensesOfUser" => $expenseDB->getSumSpendMoneyOnEachExpenseOfUser($this->startDate, $this->endDate, $this->user),
    /*  "sumFromIncomesAndExpenses" => 120 */));
    }
}

//$this->user->sumFromIncomesAndExpenses($this->startDate, $this->endDate)
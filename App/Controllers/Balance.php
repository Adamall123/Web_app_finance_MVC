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
        echo json_encode(array("allIncomesOfUser" => $this->user->getSumSpendMoneyOnEachIncomeOfUser($this->value),
                                "allExpensesOfUser" => $this->user->getSumSpendMoneyOnEachExpenseOfUser($this->value),
                                "sumFromIncomesAndExpenses" => $this->user->sumFromIncomesAndExpenses($this->user->id, $this->value)));
    }
}

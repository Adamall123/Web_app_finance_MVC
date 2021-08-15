<?php

namespace App\Controllers;

use Core\View;
use App\Models\User;
use \App\Auth; 
use \App\Flash;

class Expense extends Authenticated
{
   
    protected function before()
    {
        parent::before();
        $this->user = Auth::getUser();
    }
    public function indexAction()
    {
        View::renderTemplate('Expense/index.html', [
            'userExpenses' => $this->user->getExpensesCategoryAssignedToUser(),
            'userPaymentMethods' => $this->user->getPaymentMethodsAssignedToUser()
        ]);
    }
    public function addAction()
    {
        if ($this->user->saveExpense($_POST)) {
            Flash::addMessage('A new expense has been added succesfuly to your account.');
            $this->redirect('/Expense/index');

        } else {
            Flash::addMessage('Failed.');
            View::renderTemplate('Income/index.html', 'WARNING');
        }
    }
    public function changeAction()
    {
        echo json_encode(array("MonthlyCostsOfEachExpenseFromSelectedDate" =>  $this->user->MonthlyCostsOfEachExpenseFromSelectedDate( $_POST['expense_id'], $_POST['date'])));
    }
    public function getAction()
    {
         if (isset($_GET['expense_category_id'])){
             $this->value = $_GET['expense_category_id'];
         }
         echo json_encode(array("MonthlyCostsOfEachExpenseFromSelectedDate" =>  $this->user->MonthlyCostsOfEachExpenseFromSelectedDate($_GET['expense_category_id'], $_GET['date'])));
    }
}

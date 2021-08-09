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
        $this->userExpenses = User::getExpensesCategoryAssignedToUser($this->user->id);
        $this->userPaymentMethods = User::getPaymentMethodsAssignedToUser($this->user->id);
        View::renderTemplate('Expense/index.html', [
            'userExpenses' => $this->userExpenses,
            'userPaymentMethods' => $this->userPaymentMethods
        ]);
    }
    public function addAction()
    {
        if ($this->user->saveExpense($this->user->id, $_POST)) {
            Flash::addMessage('A new expense has been added succesfuly to your account.');
            $this->redirect('/Expense/index');

        } else {
            Flash::addMessage('Failed.');
            View::renderTemplate('Income/index.html', 'WARNING');
        }
    }
}

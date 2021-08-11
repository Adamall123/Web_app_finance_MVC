<?php 

namespace App\Controllers;

use Core\View;
use \App\Auth;
use App\Models\User;
use \App\Flash;

class Settings extends Authenticated
{
    protected function before()
    {
        parent::before();
        $this->user = Auth::getUser();
    }
    public function showAction()
    {
        View::renderTemplate('Settings/show.html', [
            'user' => $this->user
        ]);
    }

    public function editAction()
    {
        View::renderTemplate('Settings/edit.html', [
            'user' => $this->user
        ]);
    }

    public function updateAction()
    {
        
        if ($this->user->updateProfile($_POST)) {

            Flash::addMessage('Changes saved');

            $this->redirect('/Settings/show');
        } else {
            View::renderTemplate('Settings/edit.html', [
                'user' => $this->user
            ]);
        }
    }
    public function displayAction()
    {
        echo json_encode(array("getIncomesCategoryAssignedToUser" => $this->user->getIncomesCategoryAssignedToUser(),
        "getExpensesCategoryAssignedToUser" => $this->user->getExpensesCategoryAssignedToUser(),
        "getPaymentMethodsAssignedToUser" => $this->user->getPaymentMethodsAssignedToUser()
        ));

        
    }
    public function addnewincomeAction()
    {
        if ($this->user->addNewIncomeCategory($_POST['income'])){
            Flash::addMessage('Added new income.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($this->user->errors[0],  Flash::WARNING );
            View::renderTemplate('Settings/show.html', [
                'user' => $this->user
            ]);
        }
    }
    public function addnewexpenseAction()
    {
        if ($this->user->addNewExpenseCategory($_POST['expense'])){
            Flash::addMessage('Added new expense.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($this->user->errors[0],  Flash::WARNING );
            View::renderTemplate('Settings/show.html', [
                'user' => $this->user
            ]);
        }
    }
    public function addnewpaymentmethodAction()
    {
        if ($this->user->addNewPaymentMethodCategory($_POST['payment'])){
            Flash::addMessage('Added new payment method.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($this->user->errors[0],  Flash::WARNING );
            View::renderTemplate('Settings/show.html', [
                'user' => $this->user
            ]);
        }
    }
}

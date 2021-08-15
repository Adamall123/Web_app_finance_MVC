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
        $this->defaultCategory = "Another";
        $this->defaultCategoryOfPaymentMethods = 'Cash';
    }
    public function showAction()
    {
       
        View::renderTemplate('Settings/show.html', [
            'user' => $this->user,
            'defaultCategory' => $this->defaultCategory,
            'defaultCategoryOfPaymentMethods' => $this->defaultCategoryOfPaymentMethods,
            'getIncomesCategoryAssignedToUser' => $this->user->getIncomesCategoryAssignedToUser(),
            'getExpensesCategoryAssignedToUser' => $this->user->getExpensesCategoryAssignedToUser(),
            'getPaymentMethodsAssignedToUser' => $this->user->getPaymentMethodsAssignedToUser()
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
    public function addNewIncomeAction()
    {
        if ($this->user->addNewIncomeCategory($_POST['income'])){
            Flash::addMessage('Added new income.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($this->user->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function addNewExpenseAction()
    {
       
        if ($this->user->addNewExpenseCategory($_POST['expense'])){
            Flash::addMessage('Added new expense.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($this->user->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function addNewPaymentMethodAction()
    {
        if ($this->user->addNewPaymentMethodCategory($_POST['payment'])){
            Flash::addMessage('Added new payment method.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($this->user->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function editIncomeAction()
    {
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        if ($this->user->editIncome($paramIDFromURL, $_POST['editincome'])) {
            Flash::addMessage('A category has been updated.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($this->user->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function editExpenseAction()
    {
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        $updatedLimitIncome = false;
        if(isset($_POST['remember_me_expense'])) {
            $checkedUpdateLimit = $_POST['remember_me_expense'];
            $updatedLimitIncome = $this->user->updateLimitExpenseCategory($paramIDFromURL, $_POST['editexpenselimit'] ?? null);
        }
        if ($this->user->editExpense($paramIDFromURL, $_POST['editexpence']) || $updatedLimitIncome) {
            Flash::addMessage('A category has been updated.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($this->user->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function editPaymentMethodAction()
    {
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        if ($this->user->editPaymentMethod($paramIDFromURL, $_POST['editpayment'])) {
            Flash::addMessage('A category has been updated.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($this->user->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function deleteIncomeAction()
    {
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        if ($this->user->deleteIncomeAndUpdateIncomeCategoryAssignedToUser($paramIDFromURL,$this->defaultCategory)) {
            Flash::addMessage('A category has been deleted.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($this->user->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function deleteExpenseAction()
    {
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        if ($this->user->deleteExpenseAndUpdateExpenseCategoryAssignedToUser($paramIDFromURL,$this->defaultCategory)) {
                
                Flash::addMessage('A category has been deleted.');
                $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($this->user->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function deletePaymentMethodAction()
    {
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        if ($this->user->deletePaymentMethodAndUpdatePaymenthMethodAssignedToUser($paramIDFromURL,$this->defaultCategoryOfPaymentMethods)) {
            Flash::addMessage('A category has been deleted.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($this->user->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
}

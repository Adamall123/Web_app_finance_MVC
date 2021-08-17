<?php 

namespace App\Controllers;

use Core\View;
use \App\Auth;
use App\Models\User;
use App\Models\_Settings;
use \App\Flash;


class Settings extends Authenticated
{
    protected function before()
    {
        parent::before();
        $this->user = Auth::getUser();
       
        $this->settingsUser = new _Settings($this->user);
        
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
       
        if ($this->settingsUser->updateProfile($_POST)) {

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
        
        if ($this->settingsUser->addNewIncomeCategory($_POST['income'])){
            Flash::addMessage('Added new income.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($this->settingsUser->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function addNewExpenseAction()
    {
       
        if ($this->settingsUser->addNewExpenseCategory($_POST['expense'])){
            Flash::addMessage('Added new expense.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($this->settingsUser->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function addNewPaymentMethodAction()
    {
        if ($this->settingsUser->addNewPaymentMethodCategory($_POST['payment'])){
            Flash::addMessage('Added new payment method.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($this->settingsUser->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function editIncomeAction()
    {
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        if ($this->settingsUser->editIncome($paramIDFromURL, $_POST['editincome'])) {
            Flash::addMessage('A category has been updated.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($this->settingsUser->errors[0],  Flash::WARNING );
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
        if ($this->settingsUser->editExpense($paramIDFromURL, $_POST['editexpence']) || $updatedLimitIncome) {
            Flash::addMessage('A category has been updated.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($this->settingsUser->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function editPaymentMethodAction()
    {
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        if ($this->settingsUser->editPaymentMethod($paramIDFromURL, $_POST['editpayment'])) {
            Flash::addMessage('A category has been updated.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($this->settingsUser->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function deleteIncomeAction()
    {
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        if ($this->settingsUser->deleteIncomeAndUpdateIncomeCategoryAssignedToUser($paramIDFromURL,$this->defaultCategory)) {
            Flash::addMessage('A category has been deleted.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($this->settingsUser->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function deleteExpenseAction()
    {
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        if ($this->settingsUser->deleteExpenseAndUpdateExpenseCategoryAssignedToUser($paramIDFromURL,$this->defaultCategory)) {
                
                Flash::addMessage('A category has been deleted.');
                $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($this->settingsUser->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function deletePaymentMethodAction()
    {
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        if ($this->settingsUser->deletePaymentMethodAndUpdatePaymenthMethodAssignedToUser($paramIDFromURL,$this->defaultCategoryOfPaymentMethods)) {
            Flash::addMessage('A category has been deleted.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($this->settingsUser->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function deleteAllAction()
    {
        if ($this->settingsUser->deleteAllIncomesAndExpenses()) {
            Flash::addMessage('All incomes and expenses has been removed', Flash::WARNING);
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage("You do not have any incomes and expenses.",  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function deleteUserAction()
    {
        if ($this->settingsUser->deleteAllIncomesAndExpenses()){
            if( $this->settingsUser->deleteAllIncomesAndExpensesCategoriesAssignedToUser()) {
                //delete user 
                $this->settingsUser->deleteUserAccount();
                Auth::logout();//destroy session
                Flash::addMessage('The account has been deleted.', Flash::WARNING);
                $this->redirect('/');
            }
        }
       
    }
}

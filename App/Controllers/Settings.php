<?php 

namespace App\Controllers;

use Core\View;
use \App\Auth;
use App\Models\User;
use App\Models\_Settings;
use App\Models\_Income;
use App\Models\IncomesDB; 
use App\Models\_Expense;
use App\Models\_PaymentMethod;
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
        $income = new _Income($this->user);
        $incomeDB = new IncomesDB();
        //$expense = new _Expense($this->user);
        //$paymentMethod = new _PaymentMethod($this->user);
        View::renderTemplate('Settings/show.html', [
            'user' => $this->user,
            'defaultCategory' => $this->defaultCategory,
            'defaultCategoryOfPaymentMethods' => $this->defaultCategoryOfPaymentMethods,
            'getIncomesCategoryAssignedToUser' => $incomeDB->getIncomesCategoryAssignedToUser($this->user),
         //   'getExpensesCategoryAssignedToUser' => $expense->getExpensesCategoryAssignedToUser(),
          //  'getPaymentMethodsAssignedToUser' => $paymentMethod->getPaymentMethodsAssignedToUser()
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
        $income = new _Income($this->user);
        $expense = new _Expense($this->user);
        $paymentMethod = new _PaymentMethod($this->user);
        echo json_encode(array("getIncomesCategoryAssignedToUser" => $income->getIncomesCategoryAssignedToUser(),
        "getExpensesCategoryAssignedToUser" => $expense->getExpensesCategoryAssignedToUser(),
        "getPaymentMethodsAssignedToUser" => $paymentMethod->getPaymentMethodsAssignedToUser()
        ));

        
    }
    public function addNewIncomeAction()
    {
        $incomeDB = new IncomesDB();
        if ($incomeDB->addNewIncomeCategory($_POST['income'], $this->user)){
            Flash::addMessage('Added new income.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($incomeDB->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function addNewExpenseAction()
    {
        $expense = new _Expense($this->user);
        if ($expense->addNewExpenseCategory($_POST['expense'])){
            Flash::addMessage('Added new expense.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($expense->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function addNewPaymentMethodAction()
    {
        $paymentMethod = new _PaymentMethod($this->user);
        if ($paymentMethod->addNewPaymentMethodCategory($_POST['payment'])){
            Flash::addMessage('Added new payment method.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($paymentMethod->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function editIncomeAction()
    {
        //$income = new _Income($this->user);
        $incomeDB = new IncomesDB();
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        if ($incomeDB->editIncomeCategory($paramIDFromURL, $_POST['editincome'], $this->user)) {
            Flash::addMessage('A category has been updated.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($incomeDB->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function editExpenseAction()
    {
        $expense = new _Expense($this->user);
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        $updatedLimitIncome = false;
        if(isset($_POST['remember_me_expense'])) {
            $checkedUpdateLimit = $_POST['remember_me_expense'];
            $updatedLimitIncome = $expense->updateLimitExpenseCategory($paramIDFromURL, $_POST['editexpenselimit'] ?? null);
        }
        if ($expense->editExpense($paramIDFromURL, $_POST['editexpence']) || $updatedLimitIncome) {
            Flash::addMessage('A category has been updated.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($expense->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function editPaymentMethodAction()
    {
        $paymentMethod = new _PaymentMethod($this->user);
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        if ($paymentMethod->editPaymentMethod($paramIDFromURL, $_POST['editpayment'])) {
            Flash::addMessage('A category has been updated.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($paymentMethod->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function deleteIncomeAction()
    {
        //$income = new _Income($this->user);
        $incomeDB = new IncomesDB();
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        if ($incomeDB->deleteIncomeCategoryAndUpdateIncomeCategoryAssignedToUser($paramIDFromURL,$this->defaultCategory, $this->user)) {
            Flash::addMessage('A category has been deleted.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($incomeDB->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function deleteExpenseAction()
    {
        $expense = new _Expense($this->user);
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        if ($expense->deleteExpenseAndUpdateExpenseCategoryAssignedToUser($paramIDFromURL,$this->defaultCategory)) {
                Flash::addMessage('A category has been deleted.');
                $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($expense->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function deletePaymentMethodAction()
    {
        $paymentMethod = new _PaymentMethod($this->user);
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        if ($paymentMethod->deletePaymentMethodAndUpdatePaymenthMethodAssignedToUser($paramIDFromURL,$this->defaultCategoryOfPaymentMethods)) {
            Flash::addMessage('A category has been deleted.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($paymentMethod->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function deleteAllAction()
    {
        $income = new _Income($this->user);
        $expense = new _Expense($this->user);
        $incomeDB = new IncomesDB();
        if ($incomeDB->deleteAllIncomes($this->user) && $expense->deleteAllExpenses()) {
            Flash::addMessage('All incomes and expenses has been removed', Flash::WARNING);
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage("You do not have any incomes and expenses.",  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function deleteUserAction()
    {
        $income = new _Income($this->user);
        $expense = new _Expense($this->user);
        if ($income->deleteAllIncomes() && $expense->deleteAllExpenses()){
            if($income->deleteAllIncomesCategoriesAssignedToUser() && $expense->deleteAllExpensesCategoriesAssignedToUser()) {
                //delete user 
                //delete payments
                $this->user->deleteUserAccount();
                Auth::logout();//destroy session
                Flash::addMessage('The account has been deleted.', Flash::WARNING);
                $this->redirect('/');
            }
        }
    }
}

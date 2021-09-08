<?php 

namespace App\Controllers;

use Core\View;
use \App\Auth;
use App\Models\User;
use App\Models\_Settings;
use App\Models\_Income;
use App\Models\IncomesDB; 
use App\Models\_Expense;
use App\Models\ExpenseDB;
use App\Models\_PaymentMethod;
use App\Models\PaymentDB;
use App\Models\Walidator;
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
        $expenseDB = new ExpenseDB();
        $paymentDB = new PaymentDB();
        //$expense = new _Expense($this->user);
        //$paymentMethod = new _PaymentMethod($this->user);
        View::renderTemplate('Settings/show.html', [
            'user' => $this->user,
            'defaultCategory' => $this->defaultCategory,
            'defaultCategoryOfPaymentMethods' => $this->defaultCategoryOfPaymentMethods,
            'getIncomesCategoryAssignedToUser' => $incomeDB->getIncomesCategoryAssignedToUser($this->user),
            'getExpensesCategoryAssignedToUser' => $expenseDB->getExpensesCategoryAssignedToUser($this->user),
            'getPaymentMethodsAssignedToUser' => $paymentDB->getPaymentMethodsAssignedToUser($this->user)
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
        $incomeDB = new IncomesDB();
        $expenseDB = new ExpenseDB();
        $paymentDB = new PaymentDB();
        echo json_encode(array("getIncomesCategoryAssignedToUser" => $incomeDB->getIncomesCategoryAssignedToUser($this->user),
        "getExpensesCategoryAssignedToUser" => $expenseDB->getExpensesCategoryAssignedToUser($this->user),
        "getPaymentMethodsAssignedToUser" => $paymentDB->getPaymentMethodsAssignedToUser($this->user)
        ));

        
    }
    public function addNewIncomeAction()
    {
        $incomeDB = new IncomesDB();
        $walidator = new Walidator();
        if ($walidator->validateLengthOfCategory($_POST['income'], $this->user)){
            $incomeDB->addNewIncomeCategory($_POST['income'], $this->user);
            Flash::addMessage('Added new income.');
            $this->redirect('/Settings/show');$walidator->validateLengthOfCategory($_POST['income'], $this->user);
        } else {
            Flash::addMessage($walidator->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function addNewExpenseAction()
    {
        $expenseDB = new ExpenseDB();
        $walidator = new Walidator();
        
        if ($walidator->validateLengthOfCategory($_POST['expense'], $this->user)){
            $expenseDB->addNewExpenseCategory($_POST['expense'], $this->user);
            Flash::addMessage('Added new expense.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($walidator->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function addNewPaymentMethodAction()
    {
        $paymentDB = new PaymentDB();
        $walidator = new Walidator();
        
        if ($walidator->validateLengthOfCategory($_POST['payment'], $this->user)){
            $paymentDB->addNewPaymentMethodCategory($_POST['payment'], $this->user);
            Flash::addMessage('Added new payment method.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($walidator->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function editIncomeAction()
    {
        $incomeDB = new IncomesDB();
        $walidator = new Walidator();
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        if ($walidator->validateLengthOfCategory($_POST['editincome'], $this->user)) {
            $incomeDB->editIncomeCategory($paramIDFromURL, $_POST['editincome'], $this->user);
            Flash::addMessage('A category has been updated.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($walidator->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function editExpenseAction()
    {
        $expense = new _Expense($this->user);
        $expenseDB = new ExpenseDB();
        $walidator = new Walidator();
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        $updatedLimitIncome = false;
        if(isset($_POST['remember_me_expense'])) {
            //$checkedUpdateLimit = $_POST['remember_me_expense'];
            $updatedLimitIncome = $expenseDB->updateLimitExpenseCategory($paramIDFromURL, $_POST['editexpenselimit'] ?? null, $this->user);
        }
        if ($walidator->validateLengthOfCategory($_POST['editexpence'], $this->user) || $updatedLimitIncome) {
            $expenseDB->editExpenseCategory($paramIDFromURL, $_POST['editexpence'], $this->user);
            Flash::addMessage('A category has been updated.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($walidator->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function editPaymentMethodAction()
    {
        $paymentDB = new PaymentDB();
        $walidator = new Walidator();
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        if ($walidator->validateLengthOfCategory($_POST['editpayment'], $this->user)) {
            $paymentDB->editPaymentMethodCategory($paramIDFromURL, $_POST['editpayment'], $this->user);
            Flash::addMessage('A category has been updated.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($walidator->errors[0],  Flash::WARNING );
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
        $expenseDB = new ExpenseDB();
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        if ($expenseDB->deleteExpenseAndUpdateExpenseCategoryAssignedToUser($paramIDFromURL,$this->defaultCategory, $this->user)) {
                Flash::addMessage('A category has been deleted.');
                $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($expenseDB->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function deletePaymentMethodAction()
    {
        $paymentDB = new PaymentDB();
        $paramIDFromURL =  htmlspecialchars($_GET["id"]);
        if ($paymentDB->deletePaymentMethodAndUpdatePaymenthMethodAssignedToUser($paramIDFromURL,$this->defaultCategoryOfPaymentMethods, $this->user)) {
            Flash::addMessage('A category has been deleted.');
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage($paymentDB->errors[0],  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function deleteAllAction()
    {
        $incomeDB = new IncomesDB();
        $expenseDB = new ExpenseDB();
        if ($incomeDB->deleteAllIncomes($this->user) && $expenseDB->deleteAllExpenses($this->user)) {
            Flash::addMessage('All incomes and expenses has been removed', Flash::WARNING);
            $this->redirect('/Settings/show');
        } else {
            Flash::addMessage("You do not have any incomes and expenses.",  Flash::WARNING );
            $this->redirect('/Settings/show');
        }
    }
    public function deleteUserAction()
    {
        $incomeDB = new IncomesDB();
        $expenseDB = new ExpenseDB();
        if ($incomeDB->deleteAllIncomes($this->user) && $expenseDB->deleteAllExpenses($this->user)){
            if($incomeDB->deleteAllIncomesCategoriesAssignedToUser($this->user) && $expenseDB->deleteAllExpensesCategoriesAssignedToUser($this->user)) {
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

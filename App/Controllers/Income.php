<?php

namespace App\Controllers;

use Core\View;
use App\Models\User;
use App\Models\_Income;
use App\Models\IncomesDB;
use App\Models\Walidator;
use \App\Auth; 
use \App\Flash;


class Income extends Authenticated
{
   
    protected function before()
    {
        parent::before();
        $this->user = Auth::getUser();
    }
    public function indexAction()
    {
       $incomeDB = new IncomesDB();
        View::renderTemplate('Income/index.html', [
            'userIncomes' => $incomeDB->getIncomesCategoryAssignedToUser($this->user)
        ]);
    }
    public function addAction()
    {
        
        $incomeDB = new IncomesDB();
        $income = new _Income($_POST);
        $walidator = new Walidator();
        
        if ($walidator->validateAmountAndComment($_POST)) {
            $incomeDB->saveIncome($income, $this->user);
            Flash::addMessage('A new income has been added succesfuly to your account.');
            $this->redirect('/Income/index');
        } else {
            Flash::addMessage('Failed.', Flash::WARNING);
            View::renderTemplate('Income/index.html', [
                'user' => $this->user,
                'userIncomes' => $incomeDB->getIncomesCategoryAssignedToUser($this->user),
                'walidator' => $walidator
            ]);
        }
    }
}

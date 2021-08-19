<?php

namespace App\Controllers;

use Core\View;
use App\Models\User;
use App\Models\_Income;
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
        $income = new _Income($this->user);
       
        View::renderTemplate('Income/index.html', [
            'userIncomes' => $income->getIncomesCategoryAssignedToUser()
        ]);
    }
    public function addAction()
    {
        $income = new _Income($this->user);
        if ($this->user->saveIncome($_POST)) {
            Flash::addMessage('A new income has been added succesfuly to your account.');
            $this->redirect('/Income/index');
        } else {
            Flash::addMessage('Failed.', Flash::WARNING);
            View::renderTemplate('Income/index.html', [
                'user' => $this->user,
                'userIncomes' => $income->getIncomesCategoryAssignedToUser()
            ]);
        }
    }
}

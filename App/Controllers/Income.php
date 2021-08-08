<?php

namespace App\Controllers;

use Core\View;
use App\Models\User;
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
        $this->userIncomes = User::getIncomesCategoryAssignedToUser($this->user->id);
        View::renderTemplate('Income/index.html', [
            'userIncomes' => $this->userIncomes
        ]);
    }
    public function addAction()
    {
        if ($this->user->saveIncome($this->user->id, $_POST)) {
            Flash::addMessage('A new income has been added succesfuly to your account.');
            View::renderTemplate('Income/index.html');
        } else {
            Flash::addMessage('Failed.');
            View::renderTemplate('Income/index.html', 'WARNING');
        }
    }
}

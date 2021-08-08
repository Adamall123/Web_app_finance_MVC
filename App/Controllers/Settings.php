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
}
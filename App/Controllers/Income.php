<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;


class Income extends Authenticated
{

    /**
     * Show the index page
     *
     * @return void
     */
    public function indexAction()
    {
        View::renderTemplate('Income/index.html');
    }
}

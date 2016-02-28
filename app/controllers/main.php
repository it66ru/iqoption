<?php

namespace Controllers;

class Main extends \Core\C
{

    public function index()
    {
        echo $this->render('main', 'commentary');
    }

}

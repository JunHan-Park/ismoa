<?php
namespace App\Http\Controllers;

use Request;

class HomeController
{

    public function __construct()
    {

    }

    public function getHome(Request $Request)
    {

        return view('user.home');
    }
}//end class

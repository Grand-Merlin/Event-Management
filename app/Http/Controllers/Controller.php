<?php

namespace App\Http\Controllers;

//ajout des trait necessaire
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;// refaire hérité cette classe, depiis laravel 11 elle n'herite plus automatiquement

abstract class Controller extends BaseController
{
    //ajout des trait
    use AuthorizesRequests, ValidatesRequests;
}

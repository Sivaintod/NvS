<?php
// require_once("../mvc/model/Character.php");
// require_once("../mvc/model/Building.php");
require_once("../app/validator/formValidator.php");
require_once("controller.php");

class GameboardController extends Controller
{
    /**
     * Display the game board index.
     *
     * @return view
     */
    public function index()
    {	
		require_once('jouer_BR.php');
    }
}
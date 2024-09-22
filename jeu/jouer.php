<?php
@session_start();

require_once("../fonctions.php");
require_once("f_carte.php");
require_once("f_combat.php");
require_once("f_popover.php");
require_once("../mvc/controller/gameBoardController.php");
require_once("../mvc/controller/homeController.php");
require_once("../mvc/model/Building.php");

$mysqli = db_connexion();

// recupération config jeu
$dispo = config_dispo_jeu($mysqli);

include ('../nb_online.php');

date_default_timezone_set('Europe/Paris');

$id_perso = 0;

if(isset($_SESSION["id_perso"])){
	$admin = admin_perso($mysqli, $_SESSION["id_perso"]);
	
	if($dispo==1 || $admin==1){
		
		$gameBoardController = new gameBoardController();
		$homeController = new homeController();
		
		$action = (empty($_GET['action'])) ? '' : $_GET['action'];
		switch ($action) {
			case "faq":
				$homeController->faq();
				break;
			case "ranking":
				$homeController->ranking();
				break;
			default:
				$gameBoardController->index();
		}
	}
	else {
		// logout
		$_SESSION = array(); // On écrase le tableau de session
		session_destroy(); // On détruit la session
		
		header("Location:../index.php");
	}
}
else {
	// logout
	$_SESSION = array(); // On écrase le tableau de session
	session_destroy(); // On détruit la session

	header("Location:index.php");
}
?>

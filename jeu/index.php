<?php
session_start();
require_once('../mvc/controller/homeController.php');

if(!empty($_SESSION) AND isset($_SESSION["id_perso"])){
	header('location:jouer.php');
}else{
	$homeController = new homeController();
	$action = (empty($_GET['action'])) ? '' : $_GET['action'];
		switch ($action) {
			case "presentation":
				$homeController->presentation();
				break;
			case "faq":
				$homeController->faq();
				break;
			case "ranking":
				$homeController->ranking();
				break;
			case "forum":
				$homeController->forum();
				break;
			case "credits":
				$homeController->credits();
				break;
			default:
				$homeController->index();
		}
}
?>

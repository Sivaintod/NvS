<?php
session_start();

require_once("fonctions.php");
require_once("f_login.php");
require_once("mvc/model/User.php");

$mysqli = db_connexion();

$throttle_settings = [
  3 => 3, 			//delay in seconds
  5 => 10, 			//delay in seconds
  10 => 'block'		//block
];

if(isset ($_POST['pseudo']) && isset ($_POST['password']) && isset ($_POST['captcha'])) {
	
	// recuperation des variables post
	$pseudo 	= trim(filter_input(INPUT_POST, "pseudo", FILTER_SANITIZE_STRING));
	$mdp 		= $_POST['password'];
	$captcha	= $_POST['captcha'];
	
	// test champs vide
	if ( trim($pseudo) == "" || trim($mdp) == "" || trim($captcha) == "") { 
		echo "<center><font color='red'>Merci de remplir tous les champs</font><br />";
		echo "<a href=\"index.php\" class='btn btn-primary'>retour</a></center>";
	}
	else {
		if (ctype_digit($pseudo) || strpos($pseudo,'--') !== false) {
			echo "<center><font color='red'>Le Pseudo est incorrect!</font><br />";
			echo "<a href=\"index.php\" class='btn btn-primary'>retour</a></center>";
		}
		else {
			if ($captcha == $_SESSION["code"]) {
		
				// passage du mdp en md5
				$mdp = md5($mdp); 
				
				$pseudo = $mysqli->real_escape_string($pseudo);
				// recuperation de l'id du joueur et log du joueur
				$sql = "SELECT id_joueur, mdp_joueur, id_perso FROM joueur,perso WHERE joueur.id_joueur=perso.idJoueur_perso and nom_perso='$pseudo' and chef='1' and pendu=0";
				$res = $mysqli->query($sql);
				$t_user = $res->fetch_assoc();
				
				$mdp_j 		= $t_user["mdp_joueur"];
				$id_joueur 	= $t_user["id_joueur"];
				
				// TODO - A voir si besoin
				//$resLoginStatus = getLoginStatus($mysqli, $throttle_settings);
				
				$resLoginStatus['status'] = "safe";
				
				if ($resLoginStatus['status'] == "safe") {
				
					if ($mdp_j != null && $mdp_j != "") {
					
						if($mdp == $mdp_j){
							
							$_SESSION["ID_joueur"] = $id_joueur;
							
							$_SESSION["id_perso"] = $t_user["id_perso"];
							$date = time();
							
							// on enregistre les données utilisateurs pour la triche
							$ip_joueur = $_SERVER["REMOTE_ADDR"];
							$user_agent = get_user_agent();
							$cookie_val = htmlspecialchars($_COOKIE["PHPSESSID"]);
							$nowDate = new DateTimeImmutable('NOW');
							$loginDate = $nowDate->format('Y-m-d H:i:s');
							
							$user = new User();
							$user = $user->addUserOkLogin($id_joueur,$ip_joueur,$user_agent,$cookie_val,$loginDate);
							
							header("location:jeu/jouer.php?login=ok");
						}
						else {
							
							addFailedLoginAttempt($mysqli, $id_joueur, realip());
							
							echo "<center><font color='red'>mot de passe incorrect<br />";
							echo "<a href=\"index.php\" class='btn btn-primary'>[ retour ]</a></center>";
						}
					}
					else {
						echo "<center><h1><font color='red'>Votre perso n'existe plus, vous avez sans doute été pendu !</font></h1><br />";
						echo "<a href=\"index.php\">[ retour ]</a></center>";
					}
				}
				else if ($resLoginStatus['status'] == "delay") {
					$attente = $resLoginStatus['message'];
					
					echo "<center><h1><font color='red'>Vous avez dépassé le nombre de tentatives autorisées pour votre mot de passe, votre compte est bloqué pendant ".$attente." secondes </font></h1><br />";
					echo "<a href=\"index.php\">[ retour ]</a></center>";
				}
			}
			else {
				echo "<center><h1><font color='red'>Le code captcha entré ne correspond pas! Veuillez réessayer.</font></h1></p>";
				echo "<a href=\"index.php\">[ retour ]</a></center>";
			}
		}
	}
}
?>

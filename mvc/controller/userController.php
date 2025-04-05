<?php
require_once("../mvc/model/User.php");
require_once("../mvc/model/Building.php");
require_once("../app/validator/formValidator.php");
require_once("controller.php");

class UserController extends Controller
{
    /**
     * Display the user index.
     *
     * @return view
     */
    public function index()
    {	
		echo "Page d'administration des joueurs<br>";
		Echo "En construction";
    }
	
	/**
     * Display the user creation page.
     *
     * @return view
     */
    public function create()
    {	
    }
	
	/**
     * Display the specified user.
     *
     * @param  $id
     * @return view
     */
    public function show(int $id)
    {
		// on contrôle que le compte appartient bien au joueur
		if($id !=$_SESSION['ID_joueur']){
			throw new Exception('Page non autorisée',403);
		}
		$dateTime = new DateTime();
		$now = (clone $dateTime)->format('Y-m-d H:i:s');
		$add2Days = new DateInterval('P2D');
		$add8Days = new DateInterval('P8D');
		$nowPlus2Days = (clone $dateTime)->add($add2Days)->format('Y-m-d H:i:s');
		$nowPlus8Days = (clone $dateTime)->add($add8Days)->format('Y-m-d H:i:s');
			
		$profile = new User();
		$profile = $profile->select('id_joueur, nom_perso, camp.name as camp, email_joueur, avatar, pays_joueur, region_joueur, dossier_img, admin_perso, animateur, redacteur, valid_case, afficher_rosace, bousculade_deplacement, mail_mp, mail_info, demande_perm, permission, pendu')
				->leftJoin('perso','perso.idJoueur_perso','=','joueur.id_joueur')
				->leftJoin('camp','perso.clan','=','camp.id')
				->where('id_joueur',$id)
				->get();
		$profile = $profile[0];
		
		if($profile->permission){
			$permissionDate = new DateTime($profile->permission);
			if($profile->demande_perm){
				$add2Days = new DateInterval('P2D');
				$lastDay = (clone $permissionDate)->add($add2Days);
				$remainingTime = $dateTime->diff($lastDay);
				
				$lastDays = $lastDay->format('d-m-Y à H:i:s');
			}
		}
		return require_once('../mvc/view/user/show.php');
    }
	
	/**
     * Store the user in the database
     *
     * @return view or redirect
     */
    public function store()
    {
		if($_SERVER['REQUEST_METHOD']==='POST'){

			// Validation du formulaire
			
			$errors =[];
			
			$errors = formValidator::validate([

			]);

			// Si le formulaire contient des erreurs on renvoie :
			// les erreurs, les anciennes données
			// et on redirige vers la page de création

			if (!empty($errors)){
				$_SESSION['old_input'] = $_POST;
				$_SESSION['errors'] = $errors;
				$_SESSION['flash'] = ['class'=>'danger','message'=>'le formulaire comporte une ou plusieurs erreurs'];
				
				header('location:?action=create');
				die();
			}else{
				// on nettoie les données

					
				// on stocke
				$result = '';
				
				if($result){
					$_SESSION['flash'] = ['class'=>'success','message'=>'La compagnie '.$_POST['name'].' a bien été créée'];
				}else{
					$_SESSION['old_input'] = $_POST;
					$_SESSION['flash'] = ["class" => "warning","message"=>"Une erreur inconnue est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur."];
				}
				
				header('location:?action=create');
				die();
			}
		}else{
			header('location:?action=create');
			die();
		}
    }
	
	/**
     * Display the user edit page.
     *
     * @param $id
     * @return view
     */
    public function edit(int $id)
    {
		// on contrôle que le compte appartient bien au joueur
		if($id !=$_SESSION['ID_joueur']){
			throw new Exception('Page non autorisée',403);
		}
		
		$profile = new User();
		$profile = $profile->select('id_joueur, nom_perso, camp.name as camp, avatar, email_joueur, description_joueur, age_joueur, pays_joueur, region_joueur, dossier_img, admin_perso, animateur, redacteur, valid_case, afficher_rosace, bousculade_deplacement, mail_mp, mail_info, permission, pendu')
				->leftJoin('perso','perso.idJoueur_perso','=','joueur.id_joueur')
				->leftJoin('camp','perso.clan','=','camp.id')
				->where('id_joueur',$id)
				->get();
		$profile = $profile[0];

		return require_once('../mvc/view/user/edit.php');
    }
	
	/**
     * Store the updated user in the database
     *
	 * @param $id
     * @return redirect response
     */
    public function update(int $id)
	{
		if($_SERVER['REQUEST_METHOD']==='POST'){
			$user = new User();
			$user = $user->select('id_joueur, avatar, mdp_joueur, demande_perm')
					->where('id_joueur',$_SESSION['ID_joueur'])
					->get();
			$user = $user[0];
			
			if($user->id_joueur!=$id){
				throw new Exception('Page non autorisée',403);
			}

			// Validation du formulaire
			
			$errors =[];
			
			switch($_POST['form']){
				case 'notificationsForm':
					$redirect = '?action=user&op=show&id='.$user->id_joueur;
					$tab = null;
					$inputs = [
							'mail_pm' => [['bail','checked'],'Message privé'],
							'mail_attack' => [['bail','checked'],"Message d'attaque"]
						];
					break;
				case 'gameOptsForm':
					$redirect = '?action=user&op=show&id='.$user->id_joueur;
					$tab = null;
					$inputs = [
							'rosace' => [['bail','checked'],'Rosace de déplacement'],
							'validate_move' => [['bail','checked'],'Validation de déplacement'],
							'allow_push' => [['bail','checked'],'Bousculades automatiques'],
							'img_versions' => [['bail','in:v1,v2'],"Version d'images"]
						];
					break;
				case 'permissionForm':
					$redirect = '?action=user&op=show&id='.$user->id_joueur;
					$tab = 'security';
					$_POST['cancelPermBtn'] = (!empty($_POST['cancelPermBtn'])) ? $_POST['cancelPermBtn']: null;
					$_POST['validPermBtn'] = (!empty($_POST['validPermBtn'])) ? $_POST['validPermBtn']: null;
					$inputs = [
							'cancelPermBtn' => [['bail','checked'],'Annuler permission'],
							'validPermBtn' => [['bail','checked'],'Valider permission']
						];
					break;
				case 'multiAccountForm':
					$redirect = '?action=user&op=show&id='.$user->id_joueur;
					$tab = 'security';
					$inputs = [
							'target_id' => [['bail','required','numeric'],'Joueur déclaré'],
							'explanation' => [['bail','required','string'],'Explications']
						];
					break;
				case 'returnInGameForm':
					$redirect = '/';
					$tab = '';
					$_POST['returnInGameBtn'] = (!empty($_POST['returnInGameBtn'])) ? $_POST['returnInGameBtn']: null;
					$inputs = [
							'returnInGameBtn' => [['bail','checked'],'Revenir de permission'],
						];
					break;
				case 'detailForm':
					$redirect = '?action=user&op=edit&id='.$user->id_joueur;
					$tab = '';
					$inputs = [
							'user_email' => [['bail','required','email'],'E-mail'],
							'user_age' => [['bail','not_required','numeric'],'Age'],
							'user_desc' => [['bail','not_required','string'],'Description'],
							'user_country' => [['bail','not_required','string'],'Pays'],
							'user_district' => [['bail','not_required','string'],'Région']
						];
					break;
				case 'avatarForm':
					$redirect = '?action=user&op=edit&id='.$user->id_joueur;
					$tab = '';
					$inputs = [
							'imgUpload' => [['bail','required','image','max:2000000','width:150','height:150'],'avatar']
						];
					break;
				default:
				$_SESSION['flash'] = ["class" => "warning","message"=>"Une erreur inconnue est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur."];
				header('location:?action=user&op=show&id='.$id);
				die();
			}
			
			$errors = formValidator::validate($inputs);

			// Si le formulaire contient des erreurs on renvoie :
			// les erreurs, les anciennes données
			// et on redirige vers la page de création

			if (!empty($errors)){
				$_SESSION['old_input'] = $_POST;
				$_SESSION['errors'] = $errors;
				$_SESSION['flash'] = [
					'class'=>'danger',
					'message'=>"le formulaire comporte une ou plusieurs erreurs",
					'tab'=>$tab];
				
				header('location:'.$redirect);
				die();
			}else{
				// on nettoie les données et on les injecte dans le modèle
				$sanitizer = new formValidator();
				switch($_POST['form']){
					case 'notificationsForm':
						$user->mail_info = (empty($_POST['mail_attack']))?0:1;
						$user->mail_mp = (empty($_POST['mail_pm']))?0:1;
						break;
					case 'gameOptsForm':
						$user->afficher_rosace = (empty($_POST['rosace']))?0:1;
						$user->valid_case = (empty($_POST['validate_move']))?0:1;
						$user->bousculade_deplacement = (empty($_POST['allow_push']))?0:1;
						$user->dossier_img = (empty($_POST['img_versions']))?'v1':$_POST['img_versions'];
						break;
					case 'permissionForm':
						if($_POST['cancelPermBtn']=='yes' AND $_POST['validPermBtn']==null){
							$user->permission = null;
							$user->demande_perm = 0;
						}elseif($_POST['cancelPermBtn']==null AND $_POST['validPermBtn']=='yes'){
							$dateTime = new DateTime();
							$now = (clone $dateTime)->format('Y-m-d H:i:s');
							$user->permission = $now;
							$user->demande_perm = 1;
						}else{
							$_SESSION['flash'] = [
								"class" => "warning",
								"message"=>"Une erreur inconnue est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.",
								'tab'=>$tab
								];
							header('location:?action=user&op=show&id='.$id);
							die();
						}
						break;
					case 'returnInGameForm':
						if($_POST['returnInGameBtn']=='yes'){
							$user->demande_perm = -1;
						}else{
							$_SESSION['flash'] = [
								"class" => "warning",
								"message"=>"Une erreur inconnue est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.",
								'tab'=>$tab
								];
							header('location:'.$redirect);
							die();
						}
						break;
					case 'detailForm':
						$user->email_joueur = $_POST['user_email'];
						$user->age_joueur = intval($_POST['user_age']);
						$user->description_joueur = $sanitizer->sanitize($_POST['user_desc']);
						$user->pays_joueur = $sanitizer->sanitize($_POST['user_country']);
						$user->region_joueur = $sanitizer->sanitize($_POST['user_district']);
						break;
					case 'avatarForm':
						//traitement de l'image
						$fileName = basename($_FILES['imgUpload']['name']);
						$extension = strtolower(pathinfo($fileName,PATHINFO_EXTENSION));
						
						$imgPath = $_SERVER['DOCUMENT_ROOT'].'/public/img/users';

						$imgName = $sanitizer->sanitize(str_replace(' ','_',strtolower($user->id_joueur)));
						$imgName = 'mat'.$imgName.'_'.uniqid();
						$old_image = (!empty($user->avatar))?$user->avatar:null;
						$user->avatar = $imgName.'.'.$extension;

						// on détruit l'ancienne image s'il y en a une
						if($old_image!=null AND $old_image!=$user->avatar){
							$unlinkPath = $imgPath.'/'.$old_image;
							unlink($unlinkPath);
						}
						
						$destinationPath = $imgPath.'/'.$imgName.'.'.$extension;
						
						//enregistrement de l'image sur le serveur
						$imgUploaded = move_uploaded_file($_FILES['imgUpload']['tmp_name'],$destinationPath);
						
						if($imgUploaded){
							$user->update();
							$_SESSION['flash'] = [
							'class'=>'success',
							'message'=>"Votre avatar a bien été modifié",
							'tab'=>$tab
							];
						}else{
							$_SESSION['flash'] = [
							'class'=>'warning',
							'message'=>"Une erreur inconnue est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.",
							'tab'=>$tab
							];
						}
						header('location:'.$redirect);
						die();
						break;
					case 'multiAccountForm':
						$userModel = new User();
						$target_id = intval($_POST['target_id']);
						$explanation = $sanitizer->sanitize($_POST['explanation']);
						
						$target_user = new Character();
						$target_user = $target_user->select('id_perso, idJoueur_perso')->where('id_perso',$target_id)->get();
						$target_user = $target_user[0];
						
						$result = $userModel->multiAccount($user->id_joueur,$target_user->idJoueur_perso,$explanation);
						
						if($result){
							$_SESSION['flash'] = [
							'class'=>'success',
							'message'=>'Le multi-compte avec le joueur ['.$target_id.'] a bien été pris en compte',
							'tab'=>$tab
							];
						}else{
							$_SESSION['flash'] = [
							'class'=>'warning',
							'message'=>"Une erreur inconnue est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.",
							'tab'=>$tab
							];
						}
						header('location:'.$redirect);
						die();
					default:
						$_SESSION['flash'] = [
							"class" => "warning",
							"message"=>"Une erreur inconnue est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.",
							'tab'=>$tab
							];
						header('location:'.$redirect);
						die();
				}
				
				$result = $user->update();
				
				if($result){
					$_SESSION['flash'] = [
						'class'=>'success',
						'message'=>'Vos informations ont bien été modifiées',
						'tab'=>$tab
						];
				}else{
					$_SESSION['old_input'] = $_POST;
					$_SESSION['flash'] = [
						"class" => "warning",
						"message"=>"Une erreur inconnue est survenue, veuillez recommencer. Si le problème persiste, contactez l'administrateur.",
						'tab'=>$tab
						];
				}
				header('location:'.$redirect);
			}
			
		}else{
			header('location:'.$redirect);
			die();
		}
    }
	
	/**
     * delete the user from database
     *
	 * @param $id
     * @return redirect
     */
    public function destroy($id)
    {	
		// supprimer l'utilisateur
		
		header('location:?');
		die();
    }
}
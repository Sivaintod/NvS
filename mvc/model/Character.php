<?php
require_once("Model.php");

// classe créée en remplacement de la classe "perso". La classe "perso" est maintenue pour la retro compatibilité. A supprimer à terme
class Character extends Model
{
	protected $table = "perso";
	protected $primaryKey = "id_perso";
	// protected $fillable = [];
	protected $guarded = [];
	
	/**
   * recherche les infos d'un perso
   * @return class of $this
   */
	public function searchCharacters($data,$self=0) {
		
		$query = "select id_perso, idJoueur_perso, nom_perso FROM perso WHERE id_perso LIKE CONCAT(?,'%') AND chef=1 AND est_pnj=0 AND idJoueur_perso<>? LIMIT 10";
		$values = [$data,$self];

		$request = $this->request($query,$values);
		$result = $request->fetchAll(PDO::FETCH_CLASS, 'Character');

		return $result;
	}
	
	/**
   * Récupère les persos qui sont sur la carte
   * @return array
   */
   
   public function getCharactersWithPerc(int $x,int $y,int $perc){
		$x_min = $x - $perc;
		$x_max = $x + $perc;
		$y_min = $y - $perc;
		$y_max = $y + $perc;
		
		if($x_min<0){
			$x_min = 0;
		}
		if($y_min<0){
			$y_min = 0;
		}
		$values = [$x_min,$x_max,$y_min,$y_max];
		
		$query = "SELECT perso.id_perso as id, idJoueur_perso, nom_perso, x_perso, y_perso, type_perso, image_unite, type_unite.nom_unite , camp.id as camp_id, camp.img_suffix as camp_img_sfx
				FROM perso
				LEFT JOIN type_unite ON type_unite.id_unite= perso.type_perso
				LEFT JOIN camp ON camp.id=perso.clan
				LEFT JOIN perso_in_batiment ON perso_in_batiment.id_perso=perso.id_perso
				WHERE (x_perso BETWEEN ? AND ?) AND (y_perso BETWEEN ? AND ?)
				AND perso_in_batiment.id_perso IS NULL";
		
		$request = $this->request($query,$values);		
		$result = $request->fetchAll();
		
		return $result;
   }

	/**
     * Récupération du nombre de demandes anim liées aux persos
     * @param $camp int
     * @return array
     */
	public function charactersDemands(int $camp){
		
		$query = 'SELECT COUNT(id) nb_demands FROM perso_demande_anim, perso
				WHERE perso_demande_anim.id_perso = perso.id_perso AND perso.clan = ?';
		$values = [$camp];

		$request = $this->request($query,$values);
		$result = $request->fetch();
			
		return $result;
	}

	/**
     * Récupération du nombre de questions anim liées aux persos
     * @param $camp int
     * @return array
     */
	public function charactersQuestions(int $camp){
		$query = 'SELECT COUNT(id) nb_demands FROM anim_question
				WHERE id_camp = ? AND status=0';
		$values = [$camp];

		$request = $this->request($query,$values);
		$result = $request->fetch();
			
		return $result;
	}
	
	/**
     * Récupération du nombre de demandes de capture RP
     * @param $camp int
     * @return array
     */
	public function rpCaptures(){
		$query = 'SELECT COUNT(id) nb_demands FROM anim_capture
				WHERE statut=?';
		$values = [0];

		$request = $this->request($query,$values);
		$result = $request->fetch();
			
		return $result;
	}
	
	/**
   * fonction pivot pour savoir si un perso est dans un bâtiment
   * @var charac_id (int) id du perso
   * @return array
   */
	public function inBuilding(int $id) {
		
		$query = "SELECT id, id_perso, id_instanceBat FROM perso_in_batiment WHERE id_perso=?";
		$values = [$id];

		$request = $this->request($query,$values);
		$result = $request->fetch(PDO::FETCH_ASSOC);

		return $result;
	}
	
	/**
   * fonction pivot pour savoir si un perso appartient à une compagnie et s'il en est le chef
   * @var charac_id (int) id du perso
   * @return array
   */
	public function inCompany(int $id) {
		
		$query = "SELECT id, perso_in_compagnie.id_perso, perso_in_compagnie.id_compagnie, perso_in_compagnie.poste_compagnie, poste.slug FROM perso_in_compagnie LEFT JOIN poste ON poste.id_poste=perso_in_compagnie.poste_compagnie WHERE perso_in_compagnie.id_perso=?";
		$values = [$id];

		$request = $this->request($query,$values);
		$result = $request->fetch(PDO::FETCH_ASSOC);

		return $result;
	}
	
	/**
   * fonction pivot pour récupérer les respawns d'un perso
   * @var charac_id (int) id du perso
   * @return an array
   */
	public function respawns(int $id) {
		
		$query = "SELECT id, id_instance_bat, id_bat FROM perso_as_respawn WHERE id_perso=?";
		$values = [$id];

		$request = $this->request($query,$values);
		$result = $request->fetchAll(PDO::FETCH_ASSOC);

		return $result;
	}
		
	/**
   * fonction pivot pour sauvegarder un respawn d'un perso
   * @var building_id (int) id de l'instance du bâtiment
   * @var type_id (int) id du type de bâtiment
   * @var charac_id (int) id du perso
   * @return an array
   */
	public function saveRespawn(int $building_id, int $type_id, int $charac_id) {
		
		$query = 'INSERT INTO perso_as_respawn (id_instance_bat,id_bat,id_perso) VALUES (?,?,?)';
		$values = [$building_id,$type_id,$charac_id];
		
		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
	
	/**
   * fonction pivot pour actualiser un respawn d'un perso
   * @var oldRespawn (int) id de l'ancien respawn
   * @var newRespawn (int) id du nouveau respawn
   * @var charac_id (int) id du perso
   * @return an array
   */
	public function updateRespawn(int $oldRespawn, int $newRespawn, int $charac_id) {
		

		$query = "UPDATE perso_as_respawn SET id_instance_bat=? WHERE id_instance_bat=? AND id_perso=?";
		$values = [$newRespawn,$oldRespawn,$charac_id];
		
		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
	
	/**
   * fonction pivot pour supprimer un respawn d'un perso
   * @var building_id (int) id de l'instance du bâtiment
   * @var charac_id (int) id du perso
   * @return an array
   */
	public function removeRespawn(int $building_id, int $charac_id) {
		
		$query = "DELETE FROM perso_as_respawn WHERE id_instance_bat=? AND id_perso=?";
		$values = [$building_id,$charac_id];

		$request = $this->request($query,$values);
		$result = $request->rowCount();
		
		return $result;
	}
	
	/* fonction pivot pour supprimer un ou plusieurs persos de la carte
	 * @var charac_ids (array) ids des persos
	 * @return number of row deleted
	*/
	public function removeCharacFromMap(array $charac_ids){
		$binds = [];
		$values = [];
		
		foreach($charac_ids as $id){
			$binds[] = '?';
			$values[] = $id;
		}
		
		$binds = implode(', ',$binds);
		$query = "UPDATE carte SET occupee_carte='0', idPerso_carte=NULL, image_carte=NULL WHERE idPerso_carte IN (".$binds.")";
		
		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
	
	/* fonction pivot pour désactiver un perso (suppression bât, véhicules et carte + statut "est renvoyé")
	 * @var charac_ids (array) ids des persos
	 * @return number of row deleted
	*/
	public function desactivateCharacters(array $charac_ids,bool $freeJail=false){
		
		// On supprime le perso des bâtiments/véhicules/carte
		// On déplace le perso hors carte
		// On active l'option "est_renvoyé" dans la table perso
		
		$query = 'SELECT instance_batiment.id_instanceBat FROM instance_batiment LEFT JOIN batiment ON instance_batiment.id_batiment=batiment.id_batiment WHERE batiment.nom_batiment="Pénitencier"';
		
		$request = $this->request($query);
		$jails = $request->fetchAll();
		
		$jailsBinds = [];
		$binds = [];
		$values = [];
		$NotInJail = '';
		
		foreach($charac_ids as $id){
			$binds[] = '?';
			$values[] = $id;
		}
		
		$binds = implode(', ',$binds);
		
		$query = 'DELETE FROM perso_in_train WHERE id_perso IN ('.$binds.')';
		$request = $this->request($query,$values);
		$deleteFromVehicule = $request->rowCount();
		
		$query = 'UPDATE carte SET occupee_carte="0", idPerso_carte=NULL, image_carte=NULL WHERE idPerso_carte IN ('.$binds.')';
		$request = $this->request($query,$values);
		$deleteFromMap = $request->rowCount();
		
		if(!empty($jails)){
			foreach($jails as $jail){
				$values[] = $jail['id_instanceBat'];
				$jailsBinds[] = '?';
			}
			$jailsBinds = implode(', ',$jailsBinds);
			
			if(!$freeJail){
				$NotInJail = ' AND id_instanceBat NOT IN ('.$jailsBinds.')';
			}
		}
		
		$query = 'DELETE FROM perso_in_batiment WHERE id_perso IN ('.$binds.')'.$NotInJail.'';
		$request = $this->request($query,$values);
		$deleteFromBuilding = $request->rowCount();		
		
		$now = new DateTime();
		$countResults = 0;
		
		foreach($charac_ids as $id){
			$this->id_perso = $id;
			$this->x_perso = -1000;
			$this->x_perso = -1000;
			$this->y_perso = -1000;
			$this->est_renvoye = 1;
			$this->date_renvoi = $now->format('Y-m-d H:i:s');
			$result = $this->update();
			
			if($result){
				$countResults++;
			}
		}
		
		if($countResults==count($charac_ids)){
			return true;
		}

		return false;
	}
	
	/**
	 * Normalise le nom d'un perso pour comparaison
	 * @param string $name Nom à normaliser
	 */
	function normalizeName($name)
	{
		// Enlever accents
		$name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
		// Minuscules
		$name = strtolower($name);
		// Supprimer espaces multiples
		$name = preg_replace('/\s+/', ' ', $name);
		return trim($name);
	}

	/**
	 * Vérifie si un nom est trop proche ou identique à un autre joueur
	 * @param string $newName Nom à vérifier
	 * @param int $distanceMax Distance Levenshtein maximale (strict = 3)
	 * @param float $similarMin Similarité minimale (%) (strict = 80)
	 * @return array ['match'=>bool, 'exact'=>bool, 'conflict'=>string|null, 'distance'=>int|null, 'similarity'=>float|null]
	 */
	function isNameTooClose(string $newName, int $distanceMax = 3, float $similarMin = 80)
	{
		$normName = $this->normalizeName($newName);
		$len = strlen($normName);

		// On vérifie si le nom est le même qu'un nom existant
		$query = 'SELECT nom_perso FROM perso WHERE normalized_name = ? LIMIT 1';
		$values = [$normName];
		$firstResult = $this->request($query,$values);

		if ($row = $firstResult->fetch(PDO::FETCH_ASSOC)) {
			return [
				'match' => true,
				'exact' => true,
				'conflict' => $row['nom_perso'],
				'distance' => 0,
				'similarity' => 100
			];
		}

		// Préfiltrage par longueur de nom pour améliorer les performances
		$minLen = max(1, $len - $distanceMax - 1);
		$maxLen = $len + $distanceMax + 1;

		$query = 'SELECT nom_perso, normalized_name FROM perso WHERE CHAR_LENGTH(normalized_name) BETWEEN ? AND ?';
		$values = [$minLen,$maxLen];
		$candidates = $this->request($query,$values);

		$candidates = $candidates->fetchAll(PDO::FETCH_ASSOC);

		// Comparaison proche avec les méthodes levenshtein et similar_text
		foreach ($candidates as $row) {
			$candidateNorm = $row['normalized_name'];

			$distance = levenshtein($normName, $candidateNorm);
			if ($distance <= $distanceMax) {
				return [
					'match' => true,
					'exact' => false,
					'conflict' => $row['name'],
					'distance' => $distance,
					'similarity' => null
				];
			}

			if ($distance > $distanceMax + 2) continue;

			similar_text($normName, $candidateNorm, $percent);
			if ($percent >= $similarMin) {
				return [
					'match' => true,
					'exact' => false,
					'conflict' => $row['name'],
					'distance' => $distance,
					'similarity' => $percent
				];
			}
		}

		return ['match' => false];
	}
	
	/**
	* Récupère tous les objets/armes détenus par le perso
	* @param $id_perso : L'identifiant du personnage
	* @param $type : quel type d'objet on veut récuperer 'item' pour les objets courants ou 'weapon' pour les armes non équipées
	* @return array
	*/
	public function items($id_perso,$type){
		$db = $this->dbConnectPDO();

		switch($type){
			case 'item':
				$query = "SELECT objet.id_objet, nom_objet, poids_objet, description_objet, type_objet, image_objet, contient_alcool, echangeable, deposable, GROUP_CONCAT(perso_as_objet.capacite_objet SEPARATOR ',') as destinations, COUNT(*) AS quantity, SUM(poids_objet) as poids_total, SUM(perso_as_objet.equip_objet) as equiped FROM objet INNER JOIN perso_as_objet ON perso_as_objet.id_perso=:id_perso AND perso_as_objet.id_objet=objet.id_objet GROUP BY perso_as_objet.id_objet";
				break;
			case 'weapon':
				$query = "SELECT arme.id_arme, nom_arme, poids_arme, description_arme, image_arme, COUNT(*) AS quantity, SUM(poids_arme) as poids_total FROM arme INNER JOIN perso_as_arme ON perso_as_arme.id_perso=:id_perso AND perso_as_arme.id_arme=arme.id_arme AND perso_as_arme.est_portee='0' GROUP BY id_arme";
				break;
		}

		$request = $db->prepare($query);
		$request->bindParam('id_perso', $id_perso, PDO::PARAM_INT);
		$request->execute();
		$result = $request->fetchAll();

		return $result;
	}


	/**
	* On utilise un objet de l'inventaire d'un perso et on applique les bonus
	* @param $perso : instance de classe Perso
	* @param $item : instance de classe Item
	* @return bool
	*/
	public function useItem($perso,$item,$equip=true){
		$db = $this->dbConnectPDO();

		$id_perso = $perso->id_perso;
		$pv_perso = $perso->pv_perso;
		$pvMax_perso = $perso->pvMax_perso;
		$pm_perso = $perso->pm_perso;
		$pmMax_perso = $perso->pmMax_perso;
		$pa_perso = $perso->pa_perso;
		$paMax_perso = $perso->paMax_perso;

		$id_item = $item->id_objet;
		$nom_objet = $item->nom_objet;
		$type_objet = $item->type_objet;
		$cout_pa = $item->coutPa_objet;
		$poids = $item->poids_objet;
		$bonusPerception = $item->bonusPerception_objet;
		$bonusRecup = $item->bonusRecup_objet;
		$bonusPv = $item->bonusPv_objet;
		$bonusPm = $item->bonusPm_objet;
		$bonusPa = $item->bonusPA_objet;
		$alcool = $item->contient_alcool;

		// fonction d'ajustement si le bonus dépasse le maximum de l'attribut du perso
		function checkMax($actual,$bonus,$maximum){
			if($actual+$bonus>$maximum){
				$bonus = $bonus-($actual+$bonus-$maximum);
			}
			return $bonus;
		}

		$bonusPv = checkMax($pv_perso,$bonusPv,$pvMax_perso);
		// $bonusPm = checkMax($pm_perso,$bonusPm,$pmMax_perso);
		// $bonusPa = checkMax($pa_perso,$bonusPa,$paMax_perso);


		if($type_objet == 'N'){
			//suppression de l'objet de l'inventaire
			$query = "DELETE FROM perso_as_objet WHERE id_perso=:id_perso AND id_objet=:id_item LIMIT 1";
		}elseif($type_objet == 'E'){
			$poids = 0;
			if($equip==false){
				//déséquipe l'objet
				$query = "UPDATE perso_as_objet SET equip_objet='0' WHERE id_perso=:id_perso AND id_objet=:id_item AND equip_objet=1 LIMIT 1";
			}else{
				//équipe l'objet
				$query = "UPDATE perso_as_objet SET equip_objet='1' WHERE id_perso=:id_perso AND id_objet=:id_item LIMIT 1";
			}
		}else{
			return false;
		}

		$request = $db->prepare($query);
		$request->bindParam('id_perso', $id_perso, PDO::PARAM_INT);
		$request->bindParam('id_item', $id_item, PDO::PARAM_INT);
		$result = $request->execute();

		if($result){

			// MAJ des PA, du poids et des bonus du perso
			$query = "UPDATE perso SET pa_perso = pa_perso-:pa, charge_perso=charge_perso-:poids, pv_perso = pv_perso+:pv, bonusPerception_perso=bonusPerception_perso+:bonusPerc, bonusRecup_perso=bonusRecup_perso+:bonusRecup, bonusPM_perso=bonusPM_perso+:bonusPM, bonusPA_perso=bonusPA_perso+:bonusPA, bourre_perso=bourre_perso+:alcool WHERE id_perso=:id_perso";
			$request = $db->prepare($query);
			$request->bindParam('id_perso', $id_perso, PDO::PARAM_INT);
			$request->bindParam('pa', $cout_pa, PDO::PARAM_INT);
			$request->bindParam('poids', $poids);
			$request->bindParam('pv', $bonusPv, PDO::PARAM_INT);
			$request->bindParam('bonusPerc', $bonusPerception, PDO::PARAM_INT);
			$request->bindParam('bonusRecup', $bonusRecup, PDO::PARAM_INT);
			$request->bindParam('bonusPM', $bonusPm, PDO::PARAM_INT);
			$request->bindParam('bonusPA', $bonusPa, PDO::PARAM_INT);
			$request->bindParam('alcool', $alcool, PDO::PARAM_INT);
			$result = $request->execute();

			if($result){
				return true;
			}else{
				return false;
			}

		}else{
			return false;
		}
	}

	/**
	* Déséquipe une arme d'un perso
	* @param $id_perso : L'identifiant du perso
	* @param $attribut : quel attribut on modifie. Doit être le même que dans la table Perso
	* @param $effect : Le type d'effet : bonus ou malus
	* @return bool
	*/
	public function equipItem($id_perso,$id_obj,$equip=true){
		$db = $this->dbConnectPDO();

		$query = "UPDATE perso_as_objet SET equip_objet='0' WHERE id_perso=:id_perso AND id_objet=:id_obj AND equip_objet=1 LIMIT 1";
		$request = $db->prepare($query);
		$request->bindParam('id_perso', $id_perso, PDO::PARAM_INT);
		$request->bindParam('id_obj', $id_obj, PDO::PARAM_INT);
		$result = $request->execute();

		if($result){
			return true;
		}else{
			return false;
		}
	}

	/**
	* Ajoute un bonus/malus à un perso
	* @param $id_perso : L'identifiant du perso
	* @param $attribut : quel attribut on modifie. Doit être le même que dans la table Perso
	* @param $effect : Le type d'effet : bonus ou malus
	* @return bool
	*/
	public function addBonusOrMalus($id_perso,$attribut,$effect){
		$db = $this->dbConnectPDO();

		$query = 'UPDATE perso SET '.$attribut.'='.$attribut.'+:effect WHERE id_perso=:id_perso';
		$request = $db->prepare($query);
		$request->bindParam('id_perso', $id_perso, PDO::PARAM_INT);
		$request->bindParam('effect', $effect, PDO::PARAM_INT);
		$result = $request->execute();

		if($result){
			return true;
		}else{
			return false;
		}
	}
	
	/* retrocompatibilité */
	public function infligeDegats($id_cible, $degats_final){
		$db = $this->dbConnectPDO();
		// mise a jour des pv et des malus de la cible
		$sql = "UPDATE perso SET pv_perso=pv_perso-$degats_final, bonus_perso=bonus_perso-2 WHERE id_perso='$id_cible'";
		$request = $db->prepare($sql);
		$request->execute();
	}

	public function perso_gain_xp($id, $gain_xp){
		$db = $this->dbConnectPDO();
		$sql = "UPDATE perso SET xp_perso=xp_perso+$gain_xp, pi_perso=pi_perso+$gain_xp, gain_xp_tour=gain_xp_tour+$gain_xp WHERE id_perso='$id'";
		$request = $db->prepare($sql);
		$request->execute();
	}

}
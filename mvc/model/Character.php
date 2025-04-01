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
}
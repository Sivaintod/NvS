<?php
require_once("Model.php");

// classe créée en remplacement de la classe "perso". La classe "perso" est maintenue pour la retro compatibilité. A supprimer à terme
class Character extends Model
{
	protected $table = "perso";
	protected $primaryKey = "id_perso";
	// protected $fillable = [];
	protected $guarded = [];
}
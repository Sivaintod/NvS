<?php
require_once("Model.php");

class User extends Model
{
    protected $table = "joueur";
	protected $primaryKey = "id_joueur";
	// protected $fillable = [];
	protected $guarded = [];
	
}
<?php
require_once("Model.php");

class User extends Model
{
    protected $table = "joueur";
	protected $primaryKey = "id_joueur";
	// protected $fillable = [];
	protected $guarded = [];
	
		
	/**
   * Enregistre les infos utilisateur
   * @return row count
   */
	public function addUserOkLogin(int $user_id, string $ip, string $userAgent, string $cookie, $date, int $est_acquitte=0) {
	
		$query = 'INSERT INTO user_ok_logins (id_joueur,ip_joueur,user_agent,cookie_val,time,est_acquitte) VALUES (?,?,?,?,?,?)';
		$values = [$user_id,$ip,$userAgent,$cookie,$date,$est_acquitte];

		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
}
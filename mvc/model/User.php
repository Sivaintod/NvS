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
	public function addUserOkLogin(int $user_id, string $ip, string $userAgent, string $cookie, int $est_acquitte=0) {
	
		$query = 'INSERT INTO user_ok_logins (id_joueur,ip_joueur,user_agent,cookie_val,est_acquitte,time) VALUES (?,?,?,?,?,NOW())';
		$values = [$user_id,$ip,$userAgent,$cookie,$est_acquitte];

		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
	
	public function addFailedLoginAttempt(int $user_id, string $ip){
		$query = 'INSERT INTO user_failed_logins (user_id,ip_address,attempted_at) VALUES (?,INET_ATON(?),NOW())';
		$values = [$user_id,$ip];

		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
	
	public function multiAccount(int $user_id, int $target_id,string $details){
		$query = 'INSERT INTO declaration_multi (id_perso,id_multi,situation,date_declaration) VALUES (?,?,?,NOW())';
		$values = [$user_id,$target_id,$details];

		$request = $this->request($query,$values);
		$result = $request->rowCount();

		return $result;
	}
}
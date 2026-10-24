<?php
require_once("Model.php");

class Animal extends Model
{
	
	protected $table = "instance_pnj";
	protected $primaryKey = "idInstance_pnj";
	// protected $fillable = [];
	protected $guarded = [];
	
	/**
   * Récupère les pnj animaux qui sont sur la carte
   * @return array
   */
   public function getAnimalsWithPerc(int $x,int $y,int $perc){
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
		
		$query = "SELECT idInstance_pnj, nom_pnj, pnj.image_prefix, pnj.id_pnj, pv_i, pm_i, deplace_i, x_i as position_x, y_i as position_y
				FROM instance_pnj
				LEFT JOIN pnj ON instance_pnj.id_pnj= pnj.id_pnj
				WHERE (x_i BETWEEN ? AND ?) AND (y_i BETWEEN ? AND ?)";
		
		$request = $this->request($query,$values);		
		$result = $request->fetchAll();
		
		return $result;
   }
}
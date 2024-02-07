<?php
require_once("Model.php");

class Bank extends Model
{
	protected $table = "banque_as_compagnie";
	// protected $primaryKey = "";
	// protected $fillable = [];
	protected $guarded = [];
	
	/**
     * Pivot table "histobanque_compagnie" to delete the bank history
     * @param $type array
     * @return bool
     */
	public function deleteHistory(){
		
		$firstTableKey = $this->primaryKey;
		$firstTableKeyInPivot = 'id_bank';
		
		$pivotTable = 'histobanque_compagnie';
		
		$query = 'DELETE FROM '.$pivotTable.' WHERE '.$firstTableKeyInPivot.' = ?';
		$values = [$this->$firstTableKey];

		$request = $this->request($query,$values);
			
		return $request;
	}
	
	/**
     * Pivot table "banque_log" to delete the bank log
     * @param $type array
     * @return bool
     */
	public function deleteBankLog(){
		
		$firstTableKey = $this->primaryKey;
		$firstTableKeyInPivot = 'id_bank';
		
		$pivotTable = 'banque_log';
		
		$query = 'DELETE FROM '.$pivotTable.' WHERE '.$firstTableKeyInPivot.' = ?';
		$values = [$this->$firstTableKey];

		$request = $this->request($query,$values);
			
		return $request;
	}
}
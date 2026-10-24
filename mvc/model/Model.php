<?php
require_once($_SERVER['DOCUMENT_ROOT']."/mvc/Db/Db.php");

abstract class Model extends Db
{

	protected $table;
	protected $primaryKey = 'id';
	
	// gestion des champs autorisés ou non en hydratation
	protected $fillable = [];
	protected $guarded = null;
	
	// relations (à implémenter)
	// protected array $relations = [];
	
	// état de l'objet
	protected array $attributes = [];
    protected array $original = [];
	
	private $modelAttr = ['table','primaryKey','fillable','guarded','modelAttr','selectedCols','whereConditions','orWhereConditions','joinedTables','groupByConditions','orderByConditions','limitCondition','db','attributes','original'];
	private $selectedCols = '';
	private $whereConditions = [];
	private $orWhereConditions = [];
	private $joinedTables = '';
	private $groupByConditions = '';
	private $orderByConditions = '';
	private $limitCondition = '';
	private $db;
	
	public function __construct()
	{
		// par défaut la table est le nom de la classe
		if(empty($this->table)){
			$className = (new \ReflectionClass($this))->getShortName();
			$this->table = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $className));
		}
	}
	
	// ===================== accesseurs magiques =====================
	// Toute propriété non déclarée (donc toute colonne de table) est
	// stockée dans $this->attributes : plus aucune propriété dynamique. Conforme aux versions PHP 8.2+
	
	// A FAIRE
	public function __set(string $name, $value): void
	{
		$this->attributes[$name] = $value;
	}
	
	public function __get(string $name): mixed
	{
		return $this->attributes[$name] ?? null;
	}
	
	public function __isset(string $name): bool
	{
		return isset($this->attributes[$name]);
	}
	
	public function __unset(string $name): void
	{
		unset($this->attributes[$name]);
	}
	
	// paramétrer ce que retourne le var_dump/print_r pour qu'ils n'affichent que les données métier
	public function __debugInfo() {
		return $this->attributes;
    }
	
	// ===================== hydratation =====================

	/**
	 * Détermine si un champ est assignable en masse,
	 * selon les règles fillable / guarded.
	 */
	protected function isAssignable(string $key): bool
	{
		if($this->fillable === [] && $this->guarded === []){
			return true; // tout autorisé
		}
		if($this->fillable !== []){
			if($this->guarded !== NULL){
				return in_array($key, $this->fillable) && !in_array($key, $this->guarded);
			}
			return in_array($key, $this->fillable);
		}
		if($this->guarded !== NULL){
			return !in_array($key, $this->guarded);
		}
		return false;
	}
	
	/**
	 * Hydrate l'objet à partir d'une ligne de base de données (brute, sans filtrage).
	 * @return $this
	 */
	public function fill(array $data)
	{
		foreach($data as $key => $value){
			$this->attributes[$key] = $value;
		}
		$this->original = $this->attributes;
		return $this;
	}
	
	/**
	 * Retourne les attributs sous forme de tableau.
	 */
	public function toArray(): array
	{
		return $this->attributes;
	}
	
	// ===================== requêtes =====================
	
	/**
     * Display the generic request
     * @sql string
	 * @attributs array=null
     * @return database response
     */
	protected function request(string $sql, array $attributs = null)
	{
		$this->db = Db::getInstance();
		
		if($attributs !== null){
			$request = $this->db->prepare($sql);
			$request->execute($attributs);
			return $request;
		}else{
			return $this->db->query($sql);
		}
	}
	
	// créer une entrée dans une table via des attributs définis
	public function save()
	{
		$columns = [];
		$bind = [];
		$values = [];
		
		foreach($this->attributes as $attr => $value){
			if($value !== null && $attr != $this->primaryKey){
				$columns[] = $attr;
				$bind[] = '?';
				$values[] = $value;
			}
		}
		
		$columns = implode(', ',$columns);
		$bind = implode(', ',$bind);
		
		return $this->request('INSERT INTO '.$this->table.' ('.$columns.') VALUES ('.$bind.')',$values);
	}
	
	// créer une entrée dans une table puis retourner l'instance du modèle créé
	public function saveWithModel()
	{
		$this->save();
		return $this->find($this->db->lastInsertId());
	}
	
	// créer une entrée à partir d'un tableau de données (hydratation filtrée par fillable/guarded)
	public function create(array $data)
	{	
		foreach($data as $key => $value){
			if($this->isAssignable($key)){
				$this->attributes[$key] = $value;
			}
		}

		return $this->save();
	}
	
	/*
	 * get all resources from the database. Can be used with Select, Where, Join functions, Group
	 * @return array of Model instances
	*/
	public function get(){
		
		// traitement des tableaux selected, where, with
		$selected = (empty($this->selectedCols))?'*':$this->selectedCols;
		$joined = (empty($this->joinedTables))?'':$this->joinedTables;
		$grouped = (empty($this->groupByConditions))?'':' GROUP BY '.$this->groupByConditions;
		$ordered = (empty($this->orderByConditions))?'':' ORDER BY '.$this->orderByConditions;
		$limit = (empty($this->limitCondition))?'':' LIMIT '.$this->limitCondition;
		
		if(empty($this->whereConditions)){
			$where = '';
			$values = null;
		}else{
			$columns = [];
			$values = [];
			
			foreach($this->whereConditions as $condition){
				$columns[] = $condition[0];
				
				if(is_array($condition[1])){
					foreach($condition[1] as $val){
						$values[] = $val;
					}
				}else{
					$values[] = $condition[1];
				}
			}
			
			$where = ' WHERE '.implode(' AND ',$columns);

		}
		
		if(empty($this->orWhereConditions)){
			$orWhere = '';
		}else{
			$columns = [];
			
			foreach($this->orWhereConditions as $condition){
				$columns[] = $condition[0];
				
				if(is_array($condition[1])){
					foreach($condition[1] as $val){
						$values[] = $val;
					}
				}else{
					$values[] = $condition[1];
				}
			}
			
			$orWhere = implode(' ',$columns);

		}

		$query = 'SELECT '.$selected.' FROM '.$this->table.$joined.$where.$orWhere.$grouped.$ordered.$limit;
		
		$rows = $this->request($query,$values)->fetchAll(PDO::FETCH_ASSOC);
		
		// on hydrate chaque ligne dans une nouvelle instance de modèle
		$models = [];
		foreach($rows as $row){
			$models[] = (new static())->fill($row);
		}
		return $models;
	}
	
	//récupérer une entrée d'une table via sa clé primaire
	// retourne l'instance hydratée, ou false si aucune ligne trouvée
	public function find(int $id)
	{
		$selected = (empty($this->selectedCols))?'*':$this->selectedCols;
		$joined = (empty($this->joinedTables))?'':$this->joinedTables;
		
		$request = $this->request('SELECT '.$selected.' FROM '.$this->table.$joined.' WHERE '.$this->table.'.'.$this->primaryKey.' = '.$id);
		$row = $request->fetch(PDO::FETCH_ASSOC);
		
		if($row === false){
			return false;
		}
		
		return $this->fill($row);
	}
	
	/* mettre à jour une entrée d'une table via sa clé primaire si celle-ci n'est pas dans le modèle
	 * (l'id peut être fourni en paramètre, ou lu depuis les attributs)
	 * @return number of lines affected
	*/
	public function update(int $id=NULL)
	{
		if($id === NULL){
			$id = $this->attributes[$this->primaryKey] ?? NULL;
		}
		
		$columns = [];
		$values = [];
		
		foreach($this->attributes as $attr => $value){
			if($attr != $this->primaryKey){
				$columns[] = $attr. ' = ?';
				$values[] = $value;
			}
		}
		
		$columns = implode(', ',$columns);
		$sql = 'UPDATE '.$this->table.' SET '.$columns.' WHERE '.$this->primaryKey.' = '.$id;

		$request = $this->request($sql,$values);
		return $request->rowCount();
	}
	
	//mettre à jour une entrée d'une table puis retourner l'instance du modèle
	public function updateWithModel(int $id=NULL)
	{
		if($id === NULL){
			$id = $this->attributes[$this->primaryKey] ?? NULL;
		}
		
		$columns = [];
		$values = [];
		
		foreach($this->attributes as $attr => $value){
			if($value !== null && $attr != $this->primaryKey){
				$columns[] = $attr. ' = ?';
				$values[] = $value;
			}
		}
		
		$columns = implode(', ',$columns);
		
		$this->request('UPDATE '.$this->table.' SET '.$columns.' WHERE '.$this->primaryKey.' = '.$id,$values);
		return $this;
	}
	
	/* delete the database line of the selected instance of the model. Can be used with Where
	 * @return number of deleted lines
	 */
	public function delete()
	{
		$key = $this->primaryKey;
		
		if(empty($this->whereConditions)){
			$id = $this->attributes[$key] ?? NULL;
			$where = ' WHERE '.$key.' = ?';
			$values = [$id];
		}else{
			$columns = [];
			$values = [];
			
			foreach($this->whereConditions as $condition){
				$columns[] = $condition[0];
				$values[] = $condition[1];
			}
			
			$where = ' WHERE '.implode(' AND ',$columns);
		}

		$query = 'DELETE FROM '.$this->table.$where;

		return $this->request($query,$values);// on protège cette donnée en provoquant un prepare au lieu d'un query
	}
	
	/*
	 * delete a resource by the primary key. Accept an array of primary keys to multiple delete
	 * @return request
	*/
	public function destroy($ids)
	{	
		if(is_array($ids)){
			
			foreach($ids as $id){
				$columns[] = '?';
			}
			$columns = implode(', ',$columns);
			$whereIn = 'IN ('.$columns.')';
			
		}elseif(is_int($ids)){
			$whereIn = '= ?';
			$ids = [$ids];
		}else{
			return false;	
		}
		
		$query = 'DELETE FROM '.$this->table.' WHERE '.$this->primaryKey.' '.$whereIn;
		
		return $this->request($query,$ids);// on protège cette donnée en provoquant un prepare au lieu d'un query
	}
	
	/* Ajouter une sélection d'attributs à la requête
	 * 
	 * @return *this
	 */
	public function select(...$attributs){
		
		$columns = [];
		foreach($attributs as $attr){
			$columns[] = $attr;
		}
		
		$columns = implode(', ',$columns);
		$this->selectedCols = $columns; 
		
		return $this;
	}
	
	/**
	 * Ajouter une condition WHERE à la requête
	 * 
	 * @return *this
	 */
	public function where(string $attr, string $operator, $value=null){
		
		if($value === null){
			$value = $operator;
			$operator = '=';
		}
		$condition = $attr.' '.$operator.' ?';

		$this->whereConditions[] = [$condition,$value];
		
		return $this;
	}
	
	/**
	 * Ajouter une condition OR WHERE à la requête
	 * 
	 * @return *this
	 */
	public function orWhere(string $attr, string $operator,int $value=null){
		
		if($value === null){
			$value = $operator;
			$operator = '=';
		}
		$condition = ' OR '.$attr.' '.$operator.' ?';

		$this->orWhereConditions[] = [$condition,$value];
		
		return $this;
	}
	
	/**
	 * Ajouter une condition WHERE IN à la requête
	 */
	public function whereIn(string $attr, array $values){
		
		foreach($values as $value){
			$columns[] = '?';
		}
		$columns = implode(', ',$columns);
		$whereIn = 'IN ('.$columns.')';
		
		$condition = $attr.' '.$whereIn;

		$this->whereConditions[] = [$condition,$values];
		
		return $this;
	}
	
		
	/**
     * Ajouter un groupement de résultats (GROUP BY) à la requête
     * 
     * @return $this
     */
	public function groupBy(...$attributs){
		
		$columns = [];
		foreach($attributs as $attr){
			$columns[] = $attr;
		}
		
		$columns = implode(', ',$columns);
		$this->groupByConditions = $columns; 
		
		return $this;
	}
	
	/**
     * Ajouter un tri de résultats (ORDER BY) à la requête
     * 
     * @return $this
     */
	public function orderBy(...$attributs){
		
		$columns = [];
		foreach($attributs as $attr){
			$columns[] = $attr;
		}
		
		$columns = implode(', ',$columns);
		$this->orderByConditions = $columns; 
		
		return $this;
	}
	
	/**
     * Ajouter un tri de résultats (ORDER BY) à la requête
     * 
     * @return $this
     */
	public function limit(int $limit){
		
		if($limit<0){
			$limit=0;
		}
		$this->limitCondition = $limit; 
		
		return $this;
	}
	
	/**
     * Ajouter une jointure interne (INNER JOIN) à la requête
     * 
     * @return $this
     */
	public function join(string $joined_table, string $contraint_1, string $operator, string $contraint_2){
		
		$query = ' INNER JOIN '.$joined_table.' ON '.$contraint_1.$operator.$contraint_2;

		$this->joinedTables .= $query;
		
		return $this;
	}
	
	/**
     * Ajouter une jointure gauche (LEFT JOIN) à la requête
     * 
     * @return $this
     */
	public function leftJoin(string $joined_table, string $contraint_1,string $operator,string $contraint_2){
		
		$query = ' LEFT JOIN '.$joined_table.' ON '.$contraint_1.$operator.$contraint_2;

		$this->joinedTables .= $query;
		
		return $this;
	}
	
	/**
     * Ajouter une jointure droite (RIGHT JOIN) à la requête
     * 
     * @return $this
     */
	public function rightJoin(string $joined_table, string $contraint_1,string $operator,string $contraint_2){
		
		$query = ' RIGHT JOIN '.$joined_table.' ON '.$contraint_1.$operator.$contraint_2;

		$this->joinedTables .= $query;
		
		return $this;
	}
	
	// retrocompatibilité
    protected function dbConnectPDO()
    {
		return $this->db = Db::getInstance();
	}
}

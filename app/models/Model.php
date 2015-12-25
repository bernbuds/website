<?php

class ModelNotFoundException extends Exception {};
class AttributeNotFoundException extends Exception {};
class ColumnNotFoundException extends Exception {};

class Model
{
	public $debug = false;

	protected $db;
	protected $data = array();

	// override these in subclass
	protected $table = 'override_me';	
	protected $cols = array(
		'string_col'	=> 's',
		'int_col'		=> 'i',
		'double_col'	=> 'd',
		'blob_col'		=> 'b',
		'etc...'
	);

	public function __construct($id=null)
	{
		$this->db = DB::instance();
		$this->db->debug = $this->debug;
		
		if( @$id )
		{
			$this->loadVia('id', $id);
		}
		else 
		{
			// null values by default
			foreach($this->cols as $colname => $coltype) {
				$this->data[$colname] = null;
			}
		}
	}

	public function loadVia($col, $val)
	{
		if( !in_array($col, array_keys($this->cols)) ) {
			throw new ColumnNotFoundException($col);
		}

		$stmt = $this->db->prepare("SELECT * FROM $this->table WHERE $col=?");
		$stmt->bind_param($this->cols[$col], $val);

		if( $this->debug ) {
			echo "binding: (" . $this->cols[$col] . ", $val)";
		}

		$this->db->execute($stmt);

		if( ($res = $stmt->get_result()) === false ) {
			throw new QueryException("Error: " . $this->db->error);
		}

		// no results? then it's not found
		if( ($row = $res->fetch_array(MYSQLI_ASSOC)) == null ) {
			throw new ModelNotFoundException("col = $val");
		}

		$this->data = $row;
		return $this->id;
	}

	public static function FindBy($col, $val)
	{
		$obj = new static();
		$obj->loadVia($col, $val);
		return $obj;
	}

	public function save()
	{
		if($this->id) {
			return $this->update();
		}

		return $this->create();
	}

	protected function update()
	{
		$sets = [];
		foreach($this->cols as $colname => $type) {

			$val = $this->data[$colname];
			$sets[] = "$colname=?";
		}

		$set_str = implode('=?,', array_keys($this->cols)) . '=?';

		$query = "
			UPDATE $this->table 
			SET $set_str
			WHERE id=?
		";

		$stmt = $this->db->prepare($query);
		$types_str = implode('', array_values($this->cols)) . 'i';	// this is for the last id
		$params = [$types_str];

		foreach($this->cols as $colname => $type) {
			$params[]= &$this->data[$colname];
		}

		$params[] = &$this->id;	// this is for the last id
		call_user_func_array(array(&$stmt, 'bind_param'), $params);

		if( $this->debug ) {
			echo "binding: (" . implode(', ', $params) . ')';
		}

		$this->db->execute($stmt);
	}

	protected function create()
	{
		$col_str = implode(', ', array_keys($this->cols));
		$question_marks = implode(', ', array_pad([], count($this->cols), '?'));

		$query = "
			INSERT INTO $this->table ($col_str) 
			VALUES ($question_marks)
		";

		$stmt = $this->db->prepare($query);
		$types_str = implode('', array_values($this->cols));
		$params = [$types_str];

		foreach($this->cols as $colname => $type) {
			$params[]= &$this->data[$colname];
		}

		call_user_func_array(array(&$stmt, 'bind_param'), $params);

		if( $this->debug ) {
			echo "binding: (" . implode(', ', $params) . ')';
		}

		$this->db->execute($stmt);
		return $this->db->insert_id;
	}

	public function delete()
	{
		$stmt = $this->db->prepare("DELETE FROM $this->table WHERE id=?");
		$stmt->bind_param('i', $this->id);

		if( $this->debug ) {
			echo "binding: (i, $this->id)";
		}

		$this->db->execute($stmt);
	}

	public function &__get($attr)
	{
		if( !in_array($attr, array_keys($this->data)) ) {
			throw new AttributeNotFoundException($attr);
		}

		return $this->data[$attr];
	}

	public function __set($attr, $val)
	{
		$this->data[$attr] = $val;
	}
}
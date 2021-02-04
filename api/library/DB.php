<?php

class DB
{

	private static $_instance = null;
	private $_data, $_query, $_error = false,$_error_msg = null, $_results, $_lastInsertId;
	public $_pdo, $count = 0;

	public function __construct()
	{
		try {
			$this->_pdo = new PDO('mysql:host=' . Config::get('mysql/host') . ';dbname=' . Config::get('mysql/db') . ';charset=utf8', Config::get('mysql/username'), Config::get('mysql/password'),null);

		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}

	public static function getInstance()
	{
		if (!isset(self::$_instance)) {
			self::$_instance = new DB();
		}
		return self::$_instance;
	}

	public function PDO()
	{
		return $this->_pdo;
	}
  
	/*
	query
	 */
	public function query($sql, $params = array())
	{
		$this->_error = false;
		if ($this->_query = $this->_pdo->prepare($sql)) {
			$x = 1;
			if (count($params)) {
				$binded = array();
				foreach ($params as $param) {
					$binded[] = $this->_query->bindValue($x, $param);
					$x++;
				}
			}

			if ($this->_query->execute()) {
				//execute the query
				$this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
				$this->count = $this->_query->rowCount();
				$this->_lastInsertId = $this->_pdo->lastInsertId();
			} else {
				$this->_error_msg = $this->_query->errorInfo();
				$this->_error = true;
			}
		}
		return $this;
	} 

	/* action method
	 */
	public function action($action, $table, $where = array())
	{
		// expecting 3 parameters
		if (count($where) === 3) {
			$operators = array('=', '<', '>', '<=', '>=');
			$field = $where[0];
			$operator = $where[1];
			$value = $where[2];
			if (in_array($operator, $operators)) {
				$sql = "{$action} FROM {$table} WHERE {$field} {$operator} ? ";
				if (!$this->query($sql, array($value))->error()) {
					return $this;
				}
			}
		}
		return false;
	}

	/*
	get method

	 */
	public function get($table, $where)
	{
		return $this->action('SELECT *', $table, $where);
	}

	public function getall($table)
	{
		return $this->action('SELECT *', $table);
	}
  

	/* update method colume

	 */
	public function updateByID($table, $colume, $id, $fields)
	{
		$set = '';
		$x = 1;

		foreach ($fields as $name => $value) {
			$set .= "{$name} = ? ";
			if ($x < count($fields)) {
				$set .= ', ';
			}
			$x++;
		}

		$sql = "UPDATE {$table} SET {$set} WHERE {$colume} = {$id}";
		if (!$this->query($sql, $fields)->error()) {
			return true;
		}
		return false;
	}

	/* update method colume
	 */
	public function update($table, $colume, $identifiyer, $data)
	{
		$set = '';
		$x = 1;

		foreach ($data as $name => $value) {
			$set .= "{$name} = ? ";
			if ($x < count($data)) {
				$set .= ', ';
			}
			$x++;
		}

		$sql = "UPDATE {$table} SET {$set} WHERE {$colume} = '{$identifiyer}'";
		if (!$this->query($sql, $data)->error()) {
			return true;
		}
		return false;
	}


	public function update_record($table, $data, $where)
	{
		$set = '';
		$x = 1;

		foreach ($data as $name => $value) {
			$set .= "{$name} = ? ";
			if ($x < count($data)) {
				$set .= ', ';
			}
			$x++;
		}

		$sql = "UPDATE {$table} SET {$set} {$where}";
		if (!$this->query($sql, $data)->error()) {
			return true;
		}
		return false;
	}

	/* update method colume string
	 */
	public function update_replace_string($table, $colume, $data)
	{ // $data = array(oldstring','newstring');
		$oldvalue = $data[0];
		$newvalue = $data[1];
		$sql = "UPDATE {$table} SET `$colume` = REPLACE(`$colume`, '$oldvalue', '$newvalue') WHERE `$colume` LIKE '$oldvalue'";
		if (!$this->query($sql)->error()) {
			return true;
		}
		return false;
	}

   /* insert method

	 */
	public function insert($table, $fields = array())
	{

		if (count($fields)) {

			$keys = array_keys($fields);
			$values = '';
			$x = 1;

			foreach ($fields as $field) {
				$values .= '?';
				if ($x < count($fields)) {
					$values .= ', ';
				}
				$x++;
			}

			$sql = "INSERT INTO {$table}(`" . implode('`, `', $keys) . "`) VALUES({$values}) ";

			if (!$this->query($sql, $fields)->error()) {
				return true;
			}

		}

		return false;
	}

  /* delete method

	 */
	public function delete($table, $where)
	{
		return $this->action('DELETE', $table, $where);
	}

  

	/* result method

	 */
	public function results()
	{
		return $this->_results;
	}


	/* return last insert id

	 */
	public function lastInsertId()
	{
		return $this->_lastInsertId;
	}


	/* return first data

	 */
	public function first()
	{
		return $this->_results[0]; // first data
	}


	/* return last data

	 */
	public function last()
	{
		return end($this->_results); // last data
	}
  

	/* error method

	 */
	public function error()
	{
		return $this->_error;
	}

	/* error msg method

	 */
	public function error_msg()
	{
		return $this->_error_msg;
	}


	/* find data in db methode

	 */
	public function find_object($item = null, $db_table = null, $field = '')
	{

		if ($item !== null && $db_table !== null) {

			if ($field == '') {
				$field = (is_numeric($item)) ? '_id' : 'email';
			}

			$foundObject = $this->get($db_table, array($field, '=', $item));

			if ($foundObject && $foundObject->count > 0) {
				$this->_data = $foundObject->first();
				return true;
			} else {
				return false;
			}

		}
		return false;
	}


	/* find data in db WHERE  methode

	 */
	public function find_object_where($item = null, $db_table = null, $field = '', $where = null)
	{

		if ($item !== null && $db_table !== null) {

			if ($field == '') {
				$field = (is_numeric($item)) ? '_id' : 'email';
			}

			if ($where === null) {
				$foundObject = $this->get($db_table, array($field, '=', $item));
			} else {
				$where = strtolower($where);
				$condition = 'WHERE ' . $field . '=' . $item . ' AND ' . $this->replace('where', '', $where);
				$foundObject = $this->get($db_table, $condition);
			}

			if ($foundObject && $foundObject->count > 0) {
				$this->_data = $foundObject->first();
				return true;
			} else {
				return false;
			}

		}
		return false;
	}

    /* delete all data in db table

	 */
	public function empty_table($table_name)
	{
		$sql = "TRUNCATE TABLE " . $table_name;
		if (!$this->query($sql)->error()) {
			return true;
		}
		return false;
	}

	/* find db columns to search infos in the table

	 */
	public function getColumns($table)
	{
		$sql = "SHOW COLUMNS FROM $table";
		$this->_error = false;
		if ($this->_query = $this->_pdo->prepare($sql)) {

			if ($this->_query->execute()) {
				$columns = array();

				//execute the query
				$data = $this->_query->fetchAll(PDO::FETCH_OBJ);
				foreach ($data as $column) {
					$columns[] = $column->Field;
				}
				$this->_results = $columns;
				$this->count = $this->_query->rowCount();
			} else {
				$this->_error = true;
			}
		}
		return $this;
	}

	/* find db tables to search infos in the tables

	 */
	public function getTables()
	{
		$this->_error = false;
		if ($this->_query = $this->_pdo->prepare("SHOW TABLES")) {

			if ($this->_query->execute()) {
				$tables = array();

				//execute the query
				$data = $this->_query->fetchAll(PDO::FETCH_OBJ);
				foreach ($data as $table) {
					$tables[] = $table->{'Tables_in_' . Config::get('mysql/db')};
				}
				$this->_results = $tables;
				$this->count = $this->_query->rowCount();
			} else {
				$this->_error = true;
			}
		}
		return $this;
	}

	/* find db tables to search infos in the tables
	 */
	public function dropTable($table)
	{
		$sql = "DROP TABLE if exists $table";
		$this->_error = false;
		if ($this->_query = $this->_pdo->prepare($sql)) {
			if ($this->_query->execute()) {
				$this->_error = false;
			} else {
				$this->_error = true;
			}
		} else {
			$this->_error = true;
		}
		return $this;
	}

	/* import .sql into db
	 */
	public function import($file)
	{
		$query = '';
		$sqlScript = file($file);
		foreach ($sqlScript as $line) {

			$startWith = substr(trim($line), 0, 2);
			$endWith = substr(trim($line), -1, 1);

			if (empty($line) || $startWith == '--' || $startWith == '/*' || $startWith == '//') {
				continue;
			}

			$query = $query . $line;
			if ($endWith == ';') {
				$this->query($query);
				$query = '';
			}
		}
		return "Success";
	}

	/* get data method

	 */
	public function data()
	{
		return $this->_data;
	}

	//@params getRegnumber(colume name, code prefix, desired length, should be hexadecimal eg true or false)

	public function getRegnumber($db_colume, $db_table = 'users', $prefix = "", $overridelength = 10, $hexa = false)
	{

		start :
			$length = 2000;
		if ($hexa) {
			$characters = "0123456789OPQdejklmnEFGHIopqrABCDstyWXYZzJKLMNabcRSfghiTUuvwxV";
		} else {
			$characters = "0123456789";
		}

		$string = "";
		$string2 = "";
		$string .= microtime(true);
		for ($p = 0; $p < $length; $p++) {
			$string .= $characters[mt_rand(0, strlen($characters) - 1)];
		}

		$string = substr_replace($string, '', 0, $length);
		for ($p = 0; $p < $length; $p++) {
			$string2 .= $characters[mt_rand(0, strlen($characters) - 1)];
		}

		$string = "" . $string2 . "" . $string;
		$string = implode("", explode(".", $string));
		$users = $this->get($db_table, array($db_colume, '=', $string));
		foreach ($users->results() as $user) {
			$reg_num = $user->$db_colume;
			if ($reg_num == $string) {
				goto start;
			}
		}

		$return_str = $prefix . $string;
		$return_str = strlen($return_str) > $overridelength ? substr($return_str, 0, $overridelength) : $return_str;

		return $return_str;
	}



	/* cut sstring short to spefic length
		@param $text -> text to reduce i'ts length
		@param $length -> desired length to return
		@param $continue_text -> desired length to return
		@param $withTag -> should strip tags from string or lieve them?
	 */
	public function shortstring($text, $length = 32, $continue_text = '...', $withTag = false)
	{
		if ($withTag) {
			$string = $text;
		} else {
			$string = strip_tags($text);
		}
		return strlen($string) > $length ? substr($string, 0, $length) . $continue_text : $string;
	}


	public function cutstring($text, $length = 32)
	{
		return strlen($text) > $length ? substr($text, 0, $length) : $text;
	}

	/* cut sstring short to spefic length
		@param $this_replace -> character to replace
		@param $str_replace -> character to replace with the existing string
		@param $input_text -> string to carry out the operation
	 */
	public function replace($this_replace, $str_replace, $input_text, $count = 1000)
	{
		$string = '~' . $this_replace . '~';
		return preg_replace($string, $str_replace, $input_text, $count);
	}
}

?>
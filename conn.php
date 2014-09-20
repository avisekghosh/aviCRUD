<?php
/*
 * Mysql database class - only one connection alowed
 */
class Database {
	private $_connection;
	private static $_instance;
	//The single instance
	private $_DB_DSN = 'your DSN';
	private $_DB_USERNAME = 'your username';
	private $_DB_PASSWORD = 'your password';


	/*
	 Get an instance of the Database
	 @return Instance
	 */
	public static function getInstance() {
		if (!self::$_instance) {// If no instance then make one
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	// Constructor
	private function __construct() {
	try {
		$this -> _connection = $conn = new PDO( $this->_DB_DSN, $this->_DB_USERNAME, $this->_DB_PASSWORD );
	}
	catch (PDOException $e) {
	    print "Error!: " . $e->getMessage() . "<br/>";
	    die();
		}
	}

	// Magic method clone is empty to prevent duplication of connection
	private function __clone() {
	}

	// Get pdo connection
	public function getConnection() {
		return $this -> _connection;
	}

}
?>


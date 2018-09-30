<?php
namespace Back;



class DatabaseHandler {
    private $Connection;

    public function __construct(Connection $Connection){
        $this->Connection = $Connection;

        $this->Connection->open();
        if( !$this->create_tables() ){
            die("Creating databases failed!");
        }
    }

    public function create_account($epost, $parool, $eesnimi="", $perenimi="", $telefon=""){
    	$link = $this->Connection->getLink();
		$s_epost = $link->escape_string($epost);
		$s_eesnimi = $link->escape_string($eesnimi);
		$s_perenimi = $link->escape_string($perenimi);
		$s_telefon = $link->escape_string($telefon);
    	$s_parool = password_hash($parool, PASSWORD_DEFAULT);

    	$sql = "INSERT INTO KASUTAJA (epost, paroolHash, Eesnimi, Perenimi, telefon)" . PHP_EOL .
            " VALUES ('$s_epost', '$s_parool', '$s_eesnimi', '$s_perenimi', '$s_telefon')";
        if ($this->Connection->query($sql) === FALSE) {
        	echo "Error creating account: " . $this->Connection->error . "<br>";
        	return FALSE;
        } else {
        	return TRUE;
        }
    }

    public function fetch_accounts($lookup=[]){
    	$output = [];

    	$link = $this->Connection->getLink();
    	$conditions = ["1=1"];

    	foreach($lookup as $name => $value) {
    		$condition = "" . $link->escape_string($name) . "=";

	    	switch(gettype($value)){
				case "string": $condition .= "'" . $link->escape_string($value) . "'"; break;
				case "boolean": $condition .= (int) $value; break;
				case "integer": $condition .= (int) $value; break;
				case "double": $condition .= (double) $value; break;
    			default: $condition .= "'" . $link->escape_string($value) . "'"; break;
    		}

    		$conditions[] = $condition;
    	}

    	$in = join(' AND ', $conditions);
    	$select = "SELECT ID, epost, Eesnimi, Perenimi, telefon FROM KASUTAJA WHERE $in";

		$result = $this->Connection->query($select);
        if( $result !== FALSE ){
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $dp = new DataPoint();
		    		$dp->setVariable("id", $row['ID']);
		    		$dp->setVariable("epost", $row['epost']);
		    		$dp->setVariable("eesnimi", $row['Eesnimi']);
		    		$dp->setVariable("perenimi", $row['Perenimi']);
		    		$dp->setVariable("telefon", $row['telefon']);
		    		$output[] = $dp;
                }
            }
        }

        return $output;
    }

    public function create_list($Omanik, $Aeg, $Koht, $SaabMuuta){
		$link = $this->Connection->getLink();
		$i_omanik = intval($Omanik);
		$t_aeg = date("Y-m-d H:i:s", $Aeg);
		$s_koht = $link->escape_string($Koht);
		$i_saabMuuta = intval($SaabMuuta);
		
        $sql = "INSERT INTO LIST (Aeg, Koht, SaabMuuta, KASUTAJA_ID)" . PHP_EOL .
            " VALUES (" . $t_aeg . ", " . '$s_koht' " . ", " . $i_saabMuuta . ", " . $i_omanik . ")";
        if ($this->Connection->query($sql) === FALSE) {
            echo "Error creating list: " . $this->Connection->error;
            return FALSE;
        } else {

        }
        return TRUE;
    }

    private function create_tables(){
        // KASUTAJA
        $sql = "CREATE TABLE IF NOT EXISTS KASUTAJA (
  ID INT NOT NULL AUTO_INCREMENT,
  Eesnimi VARCHAR(32) NOT NULL,
  Perenimi VARCHAR(32) NOT NULL,
  epost VARCHAR(128) NOT NULL,
  telefon VARCHAR(32) NOT NULL,
  paroolHash VARCHAR(128) NOT NULL,
  PRIMARY KEY (ID))";

        if ($this->Connection->query($sql) === FALSE) {
            echo "Error creating table KASUTAJA: " . $this->Connection->error;
            return FALSE;
        }

        // LIST
        $sql = "CREATE TABLE IF NOT EXISTS LIST (
  ID INT NOT NULL AUTO_INCREMENT,
  Aeg DATETIME NULL,
  Koht VARCHAR(512) NULL,
  SaabMuuta TINYINT NOT NULL DEFAULT 0,
  KASUTAJA_ID INT NOT NULL,
  PRIMARY KEY (ID),
  INDEX fk_LIST_KASUTAJA_idx (KASUTAJA_ID ASC),
  CONSTRAINT fk_LIST_KASUTAJA
    FOREIGN KEY (KASUTAJA_ID)
    REFERENCES KASUTAJA (ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE)";
        if ($this->Connection->query($sql) === FALSE) {
            echo "Error creating table LISt: " . $this->Connection->error;
            return FALSE;
        }

        // KIRJED
        $sql = "CREATE TABLE IF NOT EXISTS KIRJED (
  LIST_ID INT NOT NULL,
  JarjekordNR INT UNSIGNED NOT NULL,
  Kirje MEDIUMTEXT NOT NULL,
  Valija VARCHAR(32) NULL,
  PRIMARY KEY (LIST_ID),
  CONSTRAINT fk_KIRJED_LIST1
    FOREIGN KEY (LIST_ID)
    REFERENCES LIST (ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE)";
        if ($this->Connection->query($sql) === FALSE) {
            echo "Error creating table KIRJED: " . $this->Connection->error;
            return FALSE;
        }

        // OSALUS
        $sql = "CREATE TABLE IF NOT EXISTS OSALUS (
  LIST_ID INT NOT NULL,
  KASUTAJA_ID INT NOT NULL,
  PRIMARY KEY (LIST_ID, KASUTAJA_ID),
  INDEX fk_LIST_has_KASUTAJA_KASUTAJA1_idx (KASUTAJA_ID ASC),
  INDEX fk_LIST_has_KASUTAJA_LIST1_idx (LIST_ID ASC),
  CONSTRAINT fk_LIST_has_KASUTAJA_LIST1
    FOREIGN KEY (LIST_ID)
    REFERENCES LIST (ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_LIST_has_KASUTAJA_KASUTAJA1
    FOREIGN KEY (KASUTAJA_ID)
    REFERENCES KASUTAJA (ID)
    ON DELETE CASCADE
    ON UPDATE CASCADE)";
        if ($this->Connection->query($sql) === FALSE) {
            echo "Error creating table OSALUS: " . $this->Connection->error;
            return FALSE;
        }
        return TRUE;
    }

}


class DataPoint {
	private $vars;

	public function __construct(){
		$this->vars = array();
	}

	public function setVariable($name, $var){
		$this->vars[$name] = $var;
	}

	public function getJson(){
		return json_encode($this->vars);
		/*foreach($this->vars as $name => $value) {

		}*/
	}
}


class Connection {
    private $servername;
    private $username;
    private $password;
    private $dbname;

    private $link;
    private $isConnected;
    public $error;

    public function __construct($servername, $username, $password, $dbname){
        $this->servername = $servername;
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;

        $this->link = new \mysqli($servername, $username, $password, $dbname);
        if ($this->link->connect_error) {
            die("Connection failed: " . $this->link->connect_error);
        }
        $this->link->close();
    }

    public function query($sql){
        if( !$this->is_alive() ){
            $this->open();
        }
        return $this->link->query($sql);
    }

    public function is_alive(){
        return is_object($this->link) && $this->link->ping();
        //return $this->connection->ping();
    }

    public function open(){
        $this->link = new \mysqli($this->servername, $this->username, $this->password, $this->dbname);
        $this->error = $this->link->error;
        if ($this->link->connect_error) {
            die("Connection failed: " . $this->link->connect_error);
            return FALSE;
        }
        return TRUE;
    }

    public function close(){
        if( $this->is_alive() ){
            return $this->link->close();
        }
    }

    public function getLink(){
    	return $this->link;
    }
}
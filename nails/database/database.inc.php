<?php
/**
 * Database
 *
 * @package
 * @author Max
 * @copyright Copyright (c) 2008
 * @version $Id: database.inc.php 288 2009-11-27 14:28:13Z keloran $
 * @access public
 */
class Database {
	protected $aWrite 	= false;
	protected $aRead	= false;
	protected $aConfig	= false;

	private $oDB		= false;
	private $oQuery		= false;
	private $iRow		= false;
	private	$oHammer	= false;

	private $bCheckedREAD	= false;
	private $bCheckedWRITE	= false;
	private $bChecked		= false;
	private $iConnect		= false;
	private $aResult		= false;

	public 	$cDatabase	= false;
	public	$cQuery		= false;
	public	$mEscape	= false;

	static $oDBi		= false;
	private $mFinal		= false;

	/**
	 * Database::__construct()
	 *
	 * @param array $aConfig
	 * @param bool $bReadOnly
	 */
	private function __construct($aConfig = false, $bReadOnly = false) {
		if (!$aConfig) {
			$oHammer = Hammer::getHammer();

			$aConfig['read']	= $oHammer->getConfig("read", $oHammer->getConfigKey());
			$aConfig['write']	= $oHammer->getConfig("write", $oHammer->getConfigKey());

			//get the engine
			$aConfig['engine']		= $oHammer->getConfig("engine", $oHammer->getConfigKey());
			$aConfig['read']['engine']	= $aConfig['engine'];
			$aConfig['write']['engine']	= $aConfig['engine'];
		}


		//get rid of the extra stuff
		unset($aConfig['read']['read']);
		unset($aConfig['write']['write']);

		$this->aConfig		= $aConfig;

		//its a readonly so dont do the write check
		if ($bReadOnly) {
			$this->cDatabase	= $aConfig['read']['database'];
		} else {
			$this->cDatabase	= $aConfig['write']['database'];
		}
	}

	/**
	 * Database::getInstance()
	 *
	 * @param array $aConfig
	 * @return object
	 */
	static function getInstance($aConfig = false) {
		if (!self::$oDBi) {
			self::$oDBi = new Database($aConfig);
		}

		return self::$oDBi;
	}

    /**
     * Database::getActiveServer()
     *
     * @param mixed $mServers
     * @return
     */
	private function getActiveServer($mServers, $cType = false) {
		//since its already alive dont check again
		if ($this->iConnect) { return $this->mFinal; }
		$iConnect = false;

		$iPrevError = ini_get("error_reporting"); //get the inital error reporting level

		if (!isset($mServers['hostname'])) {
        	//Shuffle the array, then use the last one, and pop if it fails
			shuffle($mServers);

	    	$iCount		= count($mServers);
        	$iLast		= $iCount - 1;
	        $iPort		= isset($mServers[$iLast]['port']) ? $mServers[$iLast]['port'] : 3306;

			if ($this->iConnect) {
				$iConnect	= $this->iConnect;
			} else {
				error_reporting(0);
				$iConnected		= fsockopen($mServers[$iLast]['hostname'], $iPort, $iErr, $cErr, 5);
				error_reporting($iPrevError);
				if ($iConnected) {
					$this->iConnect	= $iConnected;
					$iConnect		= $this->iConnect;
				}
			}

            if (isset($iConnect)) {
            	return $mServers[$iLast];
	    	} else {
    	    	array_pop($mServers);
        		$this->getActiveServer($mServers, $cType);
            }
		} else {
			$iPort		= isset($mServers['port'])	? $mServers['port']	: 3306;

			//set the errors to non
			error_reporting(0);
			$iConnected		= fsockopen($mServers['hostname'], $iPort, $iErr, $cErr, 5);
			error_reporting($iPrevError);
			if ($iConnected) {
				$this->iConnect = $iConnected;
				$iConnect		= $this->iConnect;
			}
		}

		//since it has to always connect or return false
		if (!$iConnect) { return false; }
		$this->mFinal = $mServers;

		return $mServers;
	}

	/**
	 * Database::getConnection()
	 *
	 * @param int $iType
	 * @return
	 */
	private function getConnection($iType) {
		if ($iType === 1) {
			$aDatabaseConfig = $this->getActiveServer($this->aConfig['read'], "READ");
		} else if ($iType === 2) {
			$aDatabaseConfig = $this->getActiveServer($this->aConfig['write'], "WRITE");
		}

		//no config at all, must be a problem
		if (!$aDatabaseConfig) {
			throw new Spanner("All the databases are down", 1001);
		}

		//now get the engine to use
		if (!$this->oDB) {
			if (isset($this->aConfig['engine'])) {
				switch($this->aConfig['engine']) {
					case "mysqli":
					case "":
						$this->aConfig['engine'] = "mysql";
						break;
				}
			} else {
				$this->aConfig['engine'] = "mysql";
			}

			$this->oDB	= new Database_PDO($aDatabaseConfig);
		}

		return true;
	}

	/**
	 * Database::write()
	 *
	 * @param string $cQueryString
	 * @param mixed $mEscape
	 * @return
	 */
	public function write($cQueryString, $mEscape = false) {
		$this->getConnection(2);
		$cQueryString = trim($cQueryString);

		$this->cQuery	= $cQueryString;
		$this->mEscape	= $mEscape;
		$cQueryString	= $this->makeSingle($cQueryString);

		$this->oQuery	= $this->oDB->queryWrite($cQueryString, $mEscape);
		$this->iRow	= 0;

		return $this->insertID();
	}

	/**
	 * Database::read()
	 *
	 * @param string $cQueryString
	 * @param mixed $mEscape
	 * @return
	 */
	public function read($cQueryString, $mEscape = false) {
		$this->getConnection(1);
		$cQueryString = trim($cQueryString);

		$this->cQuery	= $cQueryString;
		$this->mEscape	= $mEscape;
		$cQueryString	= $this->makeSingle($cQueryString);

		$this->oQuery	= $this->oDB->queryRead($cQueryString, $mEscape);
		$this->iRow	= 0;
	}

	/**
	 * Database::makeSingle()
	 *
	 * @param string $cQuery
	 * @return string
	 */
	private function makeSingle($cQuery) {
		$cSingle	= str_replace("\n", "", $cQuery);
		$cSingle	= str_replace("\r", "", $cSingle);
		$cSingle	= str_replace("\t", " ", $cSingle);
		$cSingle	= str_replace("  ", " ", $cSingle);
		$cSingle	= str_replace("        ", "  ", $cSingle);

		return $cSingle;
	}

	/**
	* Database::query()
	*
	* @desc This will always point it to read, this only here for BC
	* @param string $cQueryString
	* @param mixed $mEscape
	*
	*/
	public function query($cQueryString, $mEscape = false) {
		return $this->read($cQueryString, $mEscape);
	}

	/**
	 * Database::makeProcedure()
	 *
	 * @param array $aProcedure
	 * @return null
	 */
	public function makeProcedure($aProcedure = null) {
		if (!is_array($aProcedure)) { return false; }

		$cQuery	= false;
		for ($i = 0; $i < count($aProcedure); $i++) {
			$this->cQuery .= $aProcedure[$i] . "\n";
		}

		$this->getConnection(2);
		$this->oDB->makeProcedure($aProcedure);
	}

	/**
	 * Database::nextRecord()
	 *
	 * @desc this is to return the full resultset
	 * @return array
	 */
	public function nextRecord() {
		$this->aResult	= $this->oDB->nextRecord($this->oQuery);
		$this->iRow	+= 1;

		return $this->aResult;
	}

	/**
	 * Database::f()
	 *
	 * @desc this is to grab the field from the resultset
	 * @param string $cName
	 * @return mixed
	 */
	public function f($cName) {
		return $this->field($cName);
	}

	/**
	* Database::field()
	*
	* @desc This is a symlink to f() to make it abit easier to learn
	* @param string $cName
	* @return mixed
	*/
	public function field($cName) {
		if (isset($this->aResult[$cName])) {
			return stripslashes($this->aResult[$cName]);
		} else {
			return false;
		}
	}

	/**
	 * Database::insertID()
	 *
	 * @desc this is to grab the latest insertid from the query
	 * @return int
	 */
	public function insertID() {
		return $this->oDB->insertID();
	}

	/**
	* Database::printQuery()
	*
	* @desc this is for debugging
	* @return string
	*/
	public function printQuery() {
		$cJoined	= $this->cQuery;

		if ($this->mEscape) {
			if (is_array($this->mEscape)) {
				for ($i = 0; $i < count($this->mEscape); $i++) {
					if (!is_numeric($this->mEscape[$i])) { //add the '' around the values for strings
						$this->mEscape[$i] = "'" . $this->mEscape[$i] . "'";
					}

					$iString = strpos($cJoined, "?");
					$cStart = substr($cJoined, 0, ($iString + 1));

					$cReplace = str_replace("?", $this->mEscape[$i], $cStart);
					$cRest = substr($cJoined, ($iString + 1));

					$cJoined = $cReplace . $cRest;
				}
			} else {
				$cJoined = str_replace("?", $this->mEscape, $this->cQuery);
			}
        }

		$cReturn  = "Query: " . $this->cQuery;
		$cReturn .= "<br />Escape: " . print_r($this->mEscape, true);
		$cReturn .= "<br />Joined: " . $cJoined;

		$cSingle  = $this->makeSingle($cJoined);
		$cReturn .= "<br />Single Line: " . $cSingle;

		return $cReturn;
	}

	/**
	* Database::__destruct()
	*
	*/
	public function __destruct() {
		if ($this->oDB) {
			$this->oDB->freeResult();
		}
	}

	/**
	* Database killConnection()
	*
	* @desc this is to destroy the connection, usually called when your done with it
	*/
	public function killConnection() {
		if ($this->oDB) {
			$this->oDB->freeResult();
			$this->oDB = null;
		}
	}

	/**
	 * Database::__get()
	 *
	 * @desc This grabs the element as a fake class variable
	 * @param string $cName
	 * @return string
	 */
	public function __get($cName) {
		$cReturn = false;

		if ($this->aResult) {
			if (array_key_exists($cName, $this->aResult)) {
				$cReturn = stripslashes($this->aResult[$cName]);
			}
		}

		return $cReturn;
	}

	/**
	* Database::getError()
	*
	* @return array
	*/
	public function getError() {
		return $this->oDB->getError();
	}

	/**
	 * Database::resultSet()
	 *
	 * @return array
	 */
	public function resultSet() {
		$aReturn	= false;

		if ($this->aResult) {
			foreach ($this->aResult as $cKey => $cValue) {
				$aReturn[$cKey] = stripslashes($cValue);
			}
		}

		return $aReturn;
	}

	/**
	 * Database::__call()
	 *
	 * @desc This is because not all functions are supported nativly in Hammer
	 * that are used in PDO so added this to make it so you can use the ones that arent
	 * @param string $cName
	 * @param mixed $mArgs
	 * @return mixed
	 */
	public function __call($cName, $mArgs = false) {
		$mReturn	= false;

		if ($this->oDB) { $mReturn = $this->oDB->$cName($mArgs); }

		return $mReturn;
	}

}

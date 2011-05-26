<?php
/**
 * Database_PDO
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Database_PDO extends PDO implements Database_Interface {
	public $aConfig;

	private $oDB;
	private $oResult;

	public $mResult;
	public $iParams;
	public $cQueryd;

	/**
	 * Database_PDO::__construct()
	 *
	 * @param array $aConfig
	 */
	public function __construct($aConfig) {
		$this->aConfig = $aConfig;

		$cEngine 		= $aConfig['engine'];
		$cUsername		= $aConfig['username'];
		$cPassword		= $aConfig['password'];
		$cDatabase		= $aConfig['database'];
		$cHostname		= $aConfig['hostname'];

		//remove the i from mysqli
		if ($cEngine == "mysqli") { $cEngine = "mysql"; }
		if (!$cEngine) { $cEngine = "mysql"; }

		//now the stuff that i need in order to connect
		$cDSN	 = $cEngine;
		$cDSN	.= ":dbname="	. $cDatabase;
		$cDSN	.= ";host="	. $cHostname;

		try {
			parent::__construct($cDSN, $cUsername, $cPassword);
		} catch (PDOException $e) {
			throw new Spanner($e->getMessage(), 1000);
		}

		$this->doInitial();
	}

	/**
	 * Database_PDO::doInitial()
	 *
	 * @desc This sets the database to UTF8 so that we dont get it confused with other things
	 * @return null
	 */
	private function doInitial() {
		$this->beginTransaction();
			$this->exec("SET NAMES 'UTF8'");
			$this->exec("SET CHARACTER SET 'UTF8'");
		$this->commit();
	}

	/**
	* Database_PDO:getError()
	*
	* @return array
	*/
	public function getError() {
		return $this->errorInfo();
	}

	/**
	 * Database_PDO::doQuery()
	 *
	 * @param string $cQuery
	 * @param mixed $mEscape
	 * @return null
	 */
	private function doQuery($cQuery, $mEscape = null) {
		$iEscape	= 0;

		if ($mEscape) { //it needs preparing if its got stuff to escape
			$oResult = $this->prepare($cQuery);

			//execute needs an array
			if (!is_array($mEscape)) { $mEscape = array($mEscape); }
			$bParams	= false;

			//get a count of the entrys
			$iEscape = count($mEscape);
			for ($i = 0; $i < $iEscape; $i++) {
				$bParams = true;
				$iParam = $this->getTyped($mEscape[$i]);

				$oResult->bindParam(($i + 1), $mEscape[$i], $iParam);
			}

			//theres things i can do with it
			if ($bParams) {
				$oResult->execute();
			} else {
				$oResult->execute(array_values($mEscape));
			}
		} else { //nothing needs preparing so just execute it
			$oResult	= $this->query($cQuery);
		}

		$this->oResult	= $oResult;
		$this->iParams	= $iEscape;

		//we got an error, so throw new a spanner in the works
		if ($this->errorCode() != "00000") {
			throw new Spanner(printRead($this->errorInfo(), "ret"), 1050);
		}
	}

	/**
	 * Database_PDO::getType()
	 *
	 * @param mixed $mVar
	 * @return int
	 */
	private function getTyped($mVar) {
		$iReturn	= PDO::PARAM_STR;

		//its a number so set it as such
		if (filter_var($mVar, FILTER_VALIDATE_INT)) {
			if (filter_var($mVar, FILTER_VALIDATE_BOOLEAN)) {
				$iReturn = PDO::PARAM_BOOL;
			} else {
				$iReturn = PDO::PARAM_INT;
			}
		}

		return $iReturn;
	}

	/**
	 * Database_PDO::queryWrite()
	 *
	 * @param string $cQuery
	 * @param mixed $mEscape
	 * @return null
	 */
	public function queryWrite($cQuery, $mEscape = null) {
		$this->doQuery($cQuery, $mEscape);
	}

	/**
	 * Database_PDO::queryRead()
	 *
	 * @param string $cQuery
	 * @param mixed $mEscape
	 * @return null
	 */
	public function queryRead($cQuery, $mEscape = null) {
		$this->doQuery($cQuery, $mEscape);
	}

	/**
	 * Database_PDO::insertID()
	 *
	 * @return int
	 */
	public function insertID() {
		return $this->lastInsertId();
	}

	/**
	 * Database_PDO::nextRecord()
	 *
	 * @return array
	 */
	public function nextRecord() {
		$mResult 			= false;

		if (is_object($this->oResult)) {
			$mResult		= $this->oResult->fetch(PDO::FETCH_ASSOC);
			$this->mResult	= $mResult;
		} else {
			$this->mResult	= $this->oResult;
		}

		return $this->mResult;
	}

	/**
	 * Database_PDO::f()
	 *
	 * @param string $cField
	 * @return string
	 */
	public function f($cField) {
		return $this->field($cField);
	}

	/**
	 * Database_PDO::field()
	 *
	 * @param string $cField
	 * @return string
	 */
	public function field($cField) {
		$cReturn	= false;

		//does it actually exist
		if (isset($this->aResult[$cField])) {
			$cReturn = $this->aResult[$cField];
		}

		return $cReturn;
	}

	/**
	 * Database_PDO::makeProcedure()
	 *
	 * @param array $aProcedure
	 * @return null
	 */
	public function makeProcedure($aProcedure) {
		$this->beginTransaction();

		for ($i = 0; $i < count($aProcedure); $i++) {
			$this->exec($aProcedure[$i]);
		}

		$this->commit();
	}

	//not needed
	public function clean($cInput) {}
	public function completeQuery($cSQL, $mEscape) {}

	/**
	 * Database_PDO::freeResult()
	 *
	 * @desc This gets rid of stuff, so that result sets dont get mixed
	 * @return
	 */
	public function freeResult($bKilled = false) {
		$this->oResult	= null;
		$this->aResult	= null;
	}
<<<<<<< HEAD

	/**
	 * Database_PDO::__call()
	 *
	 * @desc This is becasue not all parts of the PDO statement/result set are supported
	 * nativly in Hammer so this is here so you can use the ones that arent
	 * @param string $cName
	 * @param mixed $mArgs
	 * @return mixed
	 */
	public function __call($cName, $mArgs = false) {
		$mReturn = false;

		if ($this->oResult) { $mReturn = $this->oResult->$cName($mArgs); }

		return $mReturn;
	}
=======
>>>>>>> c0c66965fad63221c98f14c695de9a95e55161f3
}

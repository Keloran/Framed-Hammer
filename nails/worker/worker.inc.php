<?php
/**
 * Worker
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id: worker.inc.php 2541 2010-09-30 08:04:23Z keloran $
 * @access public
 */
class Worker {
	/**
	* @var object $oWorker
	*/
	static private $oWorker;

	/**
	* @desc These are the vars that are set by the nails object
	*
	* @var object $oNails
	* @var object $oDB
	* @var object $oUser
	* @var object $oSession
	*/
	private $oNails;
	private $oDB;
	private $oUser;
	private $oSession;
	private $oMessages;

	/**
	* @var int $iUserID
	*/
	public $iUserID;

	/**
	 * Worker::__construct()
	 *
	 * @param Nails $oNails
	 * @return null
	 */
	function __construct(Nails $oNails) {
		$this->oNails		= $oNails;
		$this->oDB			= $oNails->getDatabase();
		$this->oUser		= $oNails->getUser();
		$this->oSession		= $oNails->getSession();
		$this->oMessages	= $oNails->getNail("messages");

		$this->iUserID	= $this->oUser->getUserID();

		//These need to be removed
		if ($this->oNails->checkVersion("worker", "1.0") == false) {
			$this->install();
		}
	}

	/**
	 * Worker::getInstance()
	 *
	 * @param Nails $oNails
	 * @return object
	 */
	static function getInstance(Nails $oNails) {
		if (is_null(self::$oWorker)) {
			self::$oWorker = new Worker($oNails);
		}

		return self::$oWorker;
	}

	/**
	* Worker::install()
	*
	* @return null
	*/
	private function install() {
		$this->oNails->addTable("
			CREATE TABLE `worker_tasks` (
				`iTaskID` INT NOT NULL AUTO_INCREMENT,
				`iStatus` INT NOT NULL,
				`iUserID` INT NOT NULL,
				`cTask` VARCHAR(150) NOT NULL,
				`cOptions` TEXT,
				`cStatus` VARCHAR(100),
				`cType` VARCHAR(100),
				PRIMARY KEY(`iTaskID`),
				INDEX(`iStatus`, `iUserID`)
			)");

		$this->oNails->addVersion("worker", "1.0");
	}

	/**
	 * Worker::addTask()
	 *
	 * @param string $cName
	 * @param string $cType
	 * @param array $aOptions
	 * @return int
	 */
	public function addTask($cName, $cType, $aOptions = null) {
		//Since it has to have something
		if (!$aOptions) {
			$cOptions	= "";
		} else {
			$cOptions	= serialize($aOptions);
		}

		//No user so it must be system
		if (!$this->iUserID) {
			$iUserID	= "1";
		} else {
			$iUserID	= $this->iUserID;
		}

		$aInsert	= array(
			$cName,
			$cType,
			$cOptions,
			$iUserID
		);

		$this->oDB->write("INSERT INTO worker_tasks (cTask, cType, cOptions, iUserID) VALUES ('?', '?', '?', '?')", $aInsert);
		$iTaskID	= $this->oDB->insertID();

		return $iTaskID;
	}

	/**
	 * Worker::getStatus()
	 *
	 * @param string $cName
	 * @return array
	 */
	public function getStatus($cName) {
		$aReturn	= false;

		$aSelect	= array($cName, $this->iUserID);
		$this->oDB->read("SELECT cStatus, iStatus FROM worker_tasks WHERE cTask = '?' AND iUserID = '?' LIMIT 1");
		if ($this->oDB->nextRecord()) {
			$aReturn['reason']	= $this->oDB->f('cStatus');
			$aReturn['status']	= $this->oDB->f('iStatus');
		}

		return $aReturn;
	}

	/**
	 * Worker::removeJob()
	 *
	 * @param string $cName
	 * @return null
	 */
	private  function removeJob($cName) {
		$aDelete	= array($cName, $this->iUserID);
		$this->oDB->read("DELETE FROM worker_tasks WHERE cTask = '?' LIMIT 1", $aDelete);
	}

	/**
	 * Worker::createWorkers()
	 *
	 * @return string Only for testing
	 */
	public function createWorkers() {
		$aResults	= false;
		$i			= 0;
		$cReturn	= false;
		$bRun		= false;

		//Can test for sysload, but since this might be turned off, or not exist dont restrict workers always
		if (function_exists("sys_getloadavg")) {
			$aLoad	= sys_getloadavg();
			if ($aLoad[0] <= 4) {
				$bRun = true;
			}
		} else {
			$bRun	= true;
		}

		//Sysload is low enough
		if ($bRun) {
			$this->oDB->read("SELECT iTaskID, cType FROM worker_tasks WHERE iStatus = 0 ORDER BY iTaskID ASC");
			while($this->oDB->nextRecord()) {
				$aResults[$i]['id']		= $this->oDB->f('iTaskID');
				$aResults[$i]['type']	= $this->oDB->f('cType');
				$i++;
			}

			//Now we have some tasks actually send them to the workers
			if ($aResults) {
				foreach ($aResults as $aTasks){
					$cName	= "Worker_" . $aTasks['type'];

					$oTask		= new $cName($this->oNails, $aTasks['id']);
					$oTask->doTask();
				}
			}
		}

		return $cReturn;
	}

	/**
	 * Worker::getFinished()
	 *
	 * @return string
	 */
	public function getFinished() {
		$cReturn	= "";
		$i			= 0;
		$aResults	= false;

		$this->oDB->read("SELECT cTask, iTaskID, iUserID FROM worker_tasks WHERE iStatus >= 99 ORDER BY iTaskID ASC");
		while($this->oDB->nextRecord()){
			$aResults[$i]['id']		= $this->oDB->f('iTaskID');
			$aResults[$i]['task']	= $this->oDB->f('cTask');
			$aResults[$i]['user']	= $this->oDB->f('iUserID');
			$i++;
		}

		if ($aResults) {
			foreach ($aResults as $aTasks){
				$this->removeJob($aTasks['task']);

				$cTask	= $aTasks['task'] . " completed";
				$cReturn	.= "Task: " . $cTask . "\n";

				//Notifications
				$this->oSession->addAction($cTask);
				$this->oMessages->notifyUser($iUserID, $cTask);
			}
		}

		return $cReturn;
	}

	/**
	 * Worker::setStatus()
	 *
	 * @param int $iStatus
	 * @param string $cStatus
	 * @return null
	 */
	private function setStatus($iStatus, $cStatus) {
		$aUpdate	= array($iStatus, $cStatus, $this->iTaskID);
		$this->oDB->write("UPDATE worker_tasks SET iStatus = '?0', cStatus = '?1' WHERE iTaskID = '?2'", $aUpdate);
	}
}
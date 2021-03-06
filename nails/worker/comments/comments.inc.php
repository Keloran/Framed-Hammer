<?php
/**
 * Worker_Comments
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Worker_Comments implements Worker_Interface {
	private $oNails;
	private $oDB;

	protected $iTaskID;
	protected $cName;
	protected $aOptions;


	/**
	 * Worker_Comments::__construct()
	 *
	 * @param Nails $oNails
	 * @param int $iTaskID
	 */
	function __construct(Nails $oNails, $iTaskID) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$this->iTaskID	= $iTaskID;
	}

	/**
	 * Worker_Comments::getOptions()
	 *
	 * @return array
	 */
	function getOptions() {
		$cOptions	= false;
		$aOptions	= false;

		$this->oDB->read("SELECT cOptions FROM worker_tasks WHERE iTaskID = '?' LIMIT 1", $this->iTaskID);
		if ($this->oDB->nextRecord()) {
			$cOptions	= $this->oDB->f('cOptions');
		}

		if ($cOptions) {
			$aOptions	= unserialize(stripslashes($cOptions));
		}

		$this->aOptions	= $aOptions;
		$this->setStatus(10, "Got the Options");
	}

	/**
	 * Worker_Comments::getName()
	 *
	 * @return string
	 */
	function getName() {
		$cName	= false;

		$this->oDB->read("SELECT cTask FROM worker_tasks WHERE iTaskID = '?' LIMIT 1", $this->iTaskID);
		if ($this->oDB->nextRecord()) {
			$cName	= $this->oDB->f('cTask');
		}

		$this->cName	= $cName;
		$this->setStatus(15, "Got the Name");
	}

	/**
	 * Worker_Comments::doTask()
	 *
	 * @return null
	 */
	function doTask() {
		$this->getOptions();
		$this->getName();

		foreach ($this->aOptions as $cName => $mOption){
			switch($cName){
				case "delete":
					$aUpdate	= array("Comment Deleted", $mOption);
					$this->oDB->write("UPDATE news_comments SET cComment = '?' WHERE iCommentID = '?' LIMIT 1", $aUpdate);
					$this->setStatus(100, "Comment Deleted");
					break;

				case "moderate":
					$aUpdate	= array($mOption[1] . "<br />Comment Moderated", $mOption[0]);
					$this->oDB->write("UPDATE news_comments SET cComment = '?', WHERE iCommentID = '?' LIMIT 1", $aUpdate);
					$this->setStatus(100, "Comment Moderated");
					break;
			}
		}
	}

	/**
	 * Worker_Comments::setStatus()
	 *
	 * @param int $iStatus
	 * @param string $cStatus
	 * @return null
	 */
	function setStatus($iStatus, $cStatus) {
		$aUpdate	= array($iStatus, $cStatus, $this->iTaskID);
		$this->oDB->write("UPDATE worker_tasks SET iStatus = '?', cStatus = '?' WHERE iTaskID = '?'", $aUpdate);
	}
}
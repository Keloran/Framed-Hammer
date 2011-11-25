<?php
/**
 * Twitter_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Twitter_Install {
	private $oNails;
	private $oDB;

	/**
	 * Twitter_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("twitter");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	/**
	 * Twitter_Install::upgrade()
	 *
	 * @return null
	 */
	function upgrade() {

	}

	/**
	 * Twitter_Install::install()
	 *
	 * @return null
	 */
	function install() {
		$this->oNails->addTable("
			CREATE TABLE IF NOT EXISTS `twitter` (
				`iUserID` INT NOT NULL,
				`username` VARCHAR(32) DEFAULT NULL,
				`state` SMALLINT DEFAULT 0,
				`token` VARCHAR(64) DEFAULT NULL,
				`secret` VARCHAR(64) DEFAULT NULL,
				`description` VARCHAR(255) DEFAULT NULL,
				`status` VARCHAR(140) DEFAULT NULL,
				`location` VARCHAR(80) DEFAULT NULL,
				`followers` SMALLINT DEFAULT 0,
				`mtime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`iUserID`))");
		$this->oNails->addVersion("1.0", "twitter");
	}
}
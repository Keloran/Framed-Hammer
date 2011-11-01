<?php
/**
 * Menu_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Menu_Install {
	private $oNails;
	private $oDB;

	/**
	 * Menu_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("menu");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	private function upgrade() {

	}

	private function install() {
		$this->oNails->addTable("CREATE TABLE IF NOT EXISTS `menu` (`iMenuID` INT NOT NULL AUTO_INCREMENT, `cPage` VARCHAR(100) NOT NULL, `cTitle` VARCHAR(150) NOT NULL, `cLink` TEXT NOT NULL, `iSort` INT NOT NULL, `iParentID` INT NOT NULL, `iChildren` INT NOT NULL, PRIMARY KEY(`iMenuID`)) ENGINE = MyISAM");
		$this->oNails->addIndexs("menu", array("iSort", "iParentID", "iChildren"));

		$this->oNails->addVersion("menu", "1.0");

		$this->oNails->sendLocation("install");
	}
}
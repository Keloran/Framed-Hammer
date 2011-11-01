<?php
/**
 * Organic_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Organic_Install {
	private $oNails;
	private $oDB;

	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("organic");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	/**
	 * Organic_Install::install()
	 *
	 * @return
	 */
	private function install() {
		$this->oDB->write("
			CREATE TABLE IF NOT EXISTS `organic` (
				`iOrganic` INT NOT NULL AUTO_INCREMENT,
				`cHost` VARCHAR(100),
				`cOrganic` VARCHAR(150),
				`dDated` DATETIME,
					PRIMARY KEY (`iOrganic`),
					INDEX(`dDated`, `cOrganic`)
			)");
		$this->oNails->addVersion("organic", "1.0");

		$this->oNails->sendLocation("install");
	}

	private function upgrade() {
		if ($this->oNails->checkVersion("organic", "1.2") == false) {
			$cSQL = "ALTER TABLE `organic` ADD COLUMN `cUnParsed` TEXT";
			$this->oNails->updateVersion("organic", "1.1", $cSQL);

			$this->oNails->updateVersion("organic", "1.2", false, "XML Testing");
		}
	}
}
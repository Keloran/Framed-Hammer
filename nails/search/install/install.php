<?php
/**
 * Search_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Search_Install {
	private $oNails;
	private $oDB;

	/**
	 * Search_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("search");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	/**
	 * Search_Install::upgrade()
	 *
	 * @return
	 */
	private function upgrade() {
		if ($this->oNails->checkVersion("search", "1.1") == false) {
			//1.1
			$cSQL = " ALTER TABLE `search` ADD COLUMN cSearchQuery TEXT NOT NULL ";
			$this->oNails->updateVersion("search", "1.1", $cSQL, "Added actually full query to the results");
		}
	}

	/**
	 * Search_Install::install()
	 *
	 * @return
	 */
	private function install() {
		$this->oDB->write("
			CREATE TABLE IF NOT EXISTS `search` (
				iSearchID INT NOT NULL AUTO_INCREMENT,
				cSearch VARCHAR(200),
				cSource TEXT,
				dDated DATETIME,
					PRIMARY KEY (iSearchID),
					INDEX (dDated)
			)");
		$this->oNails->addVersion("search", 1.0);
	}
}
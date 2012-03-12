<?php
/**
 * News_Comments_Install
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class News_Comments_Install{
	private $oNails;
	private $oDB;

	/**
	 * News_Comments_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("news_comments");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	private function install() {
		$this->oNails->addVersion("news_comments", "1.0");
	}

	private function upgrade() {
	}
}

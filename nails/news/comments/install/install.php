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
		//Create the comments table
		$this->oNails->addTable("
			CREATE  TABLE IF NOT EXISTS `news_comments` (
			  `iCommentID` INT NOT NULL AUTO_INCREMENT,
			  `iNewsID` INT NOT NULL,
			  `cComment` TEXT NULL DEFAULT NULL,
			  `tsDate` INT NULL DEFAULT NULL,
			  `iUserID` INT NULL DEFAULT NULL,
			  PRIMARY KEY (`iCommentID`),
			  INDEX `fk_news_comments_news` (`iNewsID` ASC))
			ENGINE = InnoDB");
		$this->oNails->addIndexs("news_comments", array("iNewsID", "iUserID"));

		$this->oNails->addVersion("news_comments", "1.0");
	}
	
	private function upgrade() {
	}
}

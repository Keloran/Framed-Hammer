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
	public function __construct(Nails $oNails) {
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
	public function upgrade() {
		if ($this->oNails->checkVersion("twitter", "1.3") == false) {
			//1.1
			$this->ocelot();

			//1.2
			$this->catapiller();

			//1.3
			$this->revolver();
		}
	}

	/**
	 * Twitter_Install::install()
	 *
	 * @return null
	 */
	public function install() {
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
		$this->oNails->addVersion("twitter", "1.0");
	}

	/**
	 * Twitter_Install::ocelot()
	 *
	 * @return
	 */
	private function ocelot() {
		$this->oNails->addTable("
			CREATE TABLE IF NOT EXISTS `twitter_details` (
				`iUserID` INT NOT NULL,
				`cDescription` VARCHAR(255) DEFAULT NULL,
				`iFollowers` SMALLINT DEFAULT 0,
				`iFollowing` SMALLINT DEFAULT 0,
				`cImage` TEXT DEFAULT NULL,
				`cLocation` VARCHAR(80) DEFAULT NULL,
				PRIMARY KEY (`iUserID`))");

		$this->oNails->addTable("
			CREATE TABLE IF NOT EXISTS `twitter_tweets` (
				`iTweetID` BIGINT NOT NULL,
				`iUserID` INT NOT NULL,
				`cTweet` VARCHAR(140),
				`iReTweet` SMALLINT DEFAULT 0,
				`cScreenName` VARCHAR(32) DEFAULT NULL,
				`tsTime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`iTweetID`))");

		$cSQL	= "ALTER TABLE twitter DROP COLUMN description,  DROP COLUMN status, DROP COLUMN followers, DROP COLUMN location";
		$this->oNails->updateVersion("twitter", "1.1", $cSQL, "Make details, and drop old columns");
	}

	/**
	 * Twitter_Install::catapiller()
	 *
	 * @return
	 */
	private function catapiller() {
		$this->oDB->write("ALTER TABLE twitter_details ADD INDEX (`iUserID`)");
		$this->oDB->write("ALTER TABLE twitter_tweets ADD INDEX (`iUserID`), ADD INDEX (`iTweetID`)");

		$this->oNails->updateVersion("twitter", "1.2",  false, "Add UserID indexs");
	}

	/**
	 * Twitter_Install::revolver()
	 *
	 * @return
	 */
	private function revolver() {
		$cSQL	= "ALTER TABLE twitter_tweets ADD COLUMN `cImage` TEXT DEFAULT NULL";
		$this->oNails->updateVersion("twitter", "1.3", $cSQL, "Add user image to the tweet for when retweet");
	}
}
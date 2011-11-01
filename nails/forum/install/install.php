<?php
class Forum_Install {
	private $oNails;
	private $oDB;

	/**
	 * Forum_Install::__construct()
	 *
	 * @param Nails $oNails
	 */
	function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();

		$bInstalled	= $oNails->checkInstalled("forum");
		if ($bInstalled) {
			$this->upgrade();
		} else {
			$this->install();
		}
	}

	/**
	 * Forum_Install::install()
	 *
	 * @return
	 */
	private function install() {
		//Add the forums
		$this->oNails->addTable("CREATE TABLE IF NOT EXISTS `forums` (`iForumID` INT NOT NULL AUTO_INCREMENT, `iParentID` INT NOT NULL DEFAULT 0, `cTitle` VARCHAR(150) NOT NULL, `cDescription` VARCHAR(250) NOT NULL, `iGroupID` INT NOT NULL DEFAULT 1, PRIMARY KEY (`iForumID`)) ENGINE = MYISAM");
		$this->oNails->addIndexs("forums", array("iParentID", "iGroupID"));

		//Add the topics
		$this->oNails->addTable("CREATE TABLE IF NOT EXISTS `forums_topics` (`iTopicID` INT NOT NULL AUTO_INCREMENT, `cTitle` VARCHAR(100) NOT NULL, `iForumID` INT NOT NULL, `iPosterID` INT NOT NULL, `tsDate` INT NOT NULL, `cContent` TEXT, `bSticky` BOOL NOT NULL DEFAULT 0, `iViews` INT NOT NULL DEFAULT 0, `bLocked` BOOL NOT NULL DEFAULT 0, PRIMARY KEY (`iTopicID`)) ENGINE = MYISAM");
		$this->oNails->addIndexs("forums_topics", array("iForumID", "iPosterID", "bSticky", "iViews", "bLocked"));

		//Add the replys
		$this->oNails->addTable("CREATE TABLE IF NOT EXISTS `forums_replys` (`iReplyID` INT NOT NULL AUTO_INCREMENT, `iTopicID` INT NOT NULL, `iForumID` INT NOT NULL, `cContent` TEXT, `tsDate` INT NOT NULL, `iPosterID` INT NOT NULL, PRIMARY KEY (`iReplyID`)) ENGINE = MYISAM");
		$this->oNails->addIndexs("forums_replys", array("iTopicID", "iPosterID", "iForumID"));

		//Modify the groups table
		if ($this->oNails->groupsInstalled()) {
			//Modify the users table
			$this->oDB->write("ALTER TABLE `users` ADD `cSignature` VARCHAR(200) NOT NULL, ADD `iPosts` INT NOT NULL DEFAULT 0");
			$this->oNails->addIndexs("users", "iPosts");

			$aGroups = array("forumsReply", "forumsTopic", "forumsSticky", "forumsDelete", "forumsLock");
			$this->oNails->addGroups($aGroups);

			$aAdminAbilitys = array("forumsDelete", "forumsTopic", "forumsReply", "forumsSticky", "forumsLock");
			$this->oNails->addAbility("admin", $aAdminAbilitys);

			$aUserAbilitys = array("forumsTopic", "forumsReply");
			$this->oNails->addAbility("registered", $aUserAbilitys);
		}

		$this->oNails->addVersion("forums", "1.0");

		$this->oNails->sendLocation("install");
	}

	private function upgrade() {

	}
}
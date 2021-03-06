<?php
/**
 * Forum_Content
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Forum_Content implements Nails_Interface {
	use Address;

	private $oNails;
	private $oForums;
	private $oContent;
	private $oDB;
	private $oSession;

	private $iTopicID;
	private $cTopicTitle;
	private $iReplyID;

	/**
	 * Forum_Content::__construct()
	 *
	 * @param Nails $oNails
	 */
	private function __construct(Nails $oNails) {
		$this->oNails	= $oNails;
		$this->oDB		= $oNails->getDatabase();
		$this->oSession	= $oNails->getSession();
	}

	/**
	 * Forum_Content::getTopicID()
	 *
	 * @return int
	 */
	public function getTopicID() {
		$iReturn	= false;
		if (!$this->oNails->cChoice) { return $iReturn; } //return null since we have no topic

		$cTopic		= "%" . $this->unSEO($this->oNails->cChoice) . "%";
		$this->oDB->read("SELECT iTopicID FROM forums_topics WHERE cTitle LIKE (?) LIMIT 1", $cTopic);
		if ($this->oDB->nextRecord()) {
			$iReturn	= $this->oDB->f('iTopicID');
		}

		$this->iTopicID		= $iReturn;
		$this->cTopicTitle	= $this->unSEO($this->oNails->cChoice);

		return $this->iTopicID;
	}

	/**
	 * Forum_Content::quoteReply()
	 *
	 * @param int $iReplyID
	 * @return array
	 */
	public function quoteReply($iReplyID) {
		$aReturn	= false;

		$this->oDB->read("SELECT forums_replys.cContent, FROM_UNIXTIME(forums_replys.tsDate, '%d/%/m/%Y') as dated, forums_replys.cUsername, forums_replys.iPosterID
			FROM forums_replys
			LEFT JOIN users ON users.iUserID = forums_replys.iPosterID
			WHERE iReplyID = ? LIMIT 1", $iReplyID);
		if ($this->oDB->nextRecord()) {
			$aReturn['username']	= $this->oDB->f('cUsername');
			$aReturn['userid']		= $this->oDB->f('iPosterID');
			$aReturn['content']		= $this->oDB->f('cContent');
			$aReturn['dated']		= $this->oDB->f('dated');
		}

		return $aReturn;
	}

	/**
	 * Forum_Content::findQuote()
	 *
	 * @param int $iQuoteID
	 * @return array
	 */
	public function findQuote($iQuoteID) {
		$aReturn	= false;

		$this->oDB->read("SELECT forums_replys.iTopicID, forums_topics.cTitle
			FROM forums_replys
			LEFT JOIN forums_topics ON forums_topics.iTopicID = forums_replys.iTopicID
			WHERE forums_replys.iReplyID = ? LIMIT 1", $iQuoteID);
		if ($this->oDB->nextRecord()) {
			$aReturn['topic']	= $this->oDB->f('iTopicID');
			$aReturn['reply']	= $this->oDB->f('iReplyID');
			$aReturn['title']	= $this->oDB->f('cTitle');
		}

		return $aReturn;
	}

	/**
	 * Forum_Content::getInstance()
	 *
	 * @param Nails $oNails
	 * @return object
	 */
	public static function getInstance(Nails $oNails) {
		if (is_null(self::$oContent)) {
			self::$oContent = new Forum_Content($oNails);
		}

		return self::$oContent;
	}
}
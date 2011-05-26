<?php
/**
 * Encryption
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2008
 * @version $Id: encryption.inc.php 63 2009-09-22 09:06:11Z keloran $
 * @access public
 */
class Encryption extends Nails {
	private $cAlgorithm	= MCRYPT_BLOWFISH;
	private $cMode		= MCRYPT_CBC;
	private	$cRandSrc	= MCRYPT_DEV_URANDOM;
	private $cCryptKey	= false;

	public $cClearTest	= false;
	public $cCypherText	= false;
	public $cIV			= false;

	static $oEncryption;

	/**
	 * Constructor
	 * @access protected
	 */
	private function __construct() {
		parent::_construct();
		$aConfig			= $this->getConfig("Encrypt");
		$this->cCryptKey	= $aConfig['key'];

		$iIVSize	= mcrypt_get_iv_size($this->cAlgorithm, $this->cMode);
		$cIV		= mcrypt_create_iv($iIVSize, $this->cRandSrc);

		$this->cIV	= $cIV;

		//do the upgrade
		if ($this->checkVersion("encryption", "1.0") == false) {
			//1.0
			$this->addVersion("encryption", "1.0");
		}
	}

	/**
	 * Encryption::getInstance()
	 *
	 * @return object
	 */
	static function getInstance() {
		if (is_null(self::$oEncryption)) {
			self::$oEncryption = new Encryption();
		}

		return self::$oEncryption;
	}

	/**
	 * Encryption::encrypt()
	 *
	 * @return
	 */
	public function encrypt() {
		$cCypherText		= mcrypt_encrypt($this->cAlgorithm, $this->cCryptKey, $this->cClearText, $this->cMode, $this->cIV);
		$this->cCypherText	= $cCypherText;
	}

	/**
	 * Encryption::decrypt()
	 *
	 * @return
	 */
	public function decrypt() {
		$cClearText			= mcrypt_decrypt($this->cAlgorithm, $this->cCryptKey, $this->cCypherText, $this->cMode, $this->cIV);
		$this->cClearText	= $cClearText;
	}
}
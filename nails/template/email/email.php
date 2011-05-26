<?php
/**
 * Template_Email
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2010
 * @version $Id$
 * @access public
 */
class Template_Email extends Template_Abstract {
	public $aParams;
	public $cTemplate;

	protected $aVars;

	//instance
	static $oRender;

	//specific
	private $cFolder;

	/**
	 * Template_Email::__construct()
	 *
	 * @param mixed $mParams
	 * @param string $cTemplate
	 */
	public function __construct($mParams, $cTemplate = null) {
		$this->setParams($mParams);

		//since we have a template set
		if ($cTemplate) {
			$this->setTemplate($cTemplate);
		}
	}

	/**
	 * Template_Email::setDefault()
	 *
	 * @param string $cDefault
	 * @return null
	 */
	public function setDefault($cDefault) {
		$this->cDefault = $cDefault;
	}

	/**
	 * Template_Email::setTemplate()
	 *
	 * @param string $cTemplate
	 * @return string
	 */
	public function setTemplate($cTemplate = null) {
		$cReturn	= false;

		$this->cFolder	= SITEPATH . "/layout/emails/";
		if (file_exists($this->cFolder . $cTemplate)) {
			$cPath = $this->cFolder . $cTemplate;
		}

		//since the page isnt actually real
		if (strstr($cPath, "http:")) { return false; }

		if ($cPath) {
			return $cPath;
		} else {
			throw new Spanner($cTemplate . " template doesnt exist at " . $cPath, 500);
		}
	}
}
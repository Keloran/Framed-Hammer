<?php
/**
 * Template_Email
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2012
 * @version $Id$
 * @access public
 */
class Template_Email extends Template_Abstract {
	/**
	 * Template_Email::__construct()
	 *
	 */
	public function __construct($mParams) {
		$this->setParams($mParams);
	}

	/**
	 * Template_Email::setTemplate()
	 *
	 * @param string $cTemplate
	 * @return string
	 */
	public function setTemplate($cTemplate = null) {
		$cReturn	= false;

		$cOriginal	= SITEPATH . "/layout/emails/templates/" . $cTemplate . ".tpl";
		$this->addDebug("Original", $cOriginal);

		$cOld		= SITEPATH . "/templates/" . $cTemplate . ".tpl";
		$this->addDebug("Old", $cOld);

		$cNewish	= SITEPATH . "/layout/emails/" . $cTemplate . ".tpl";
		$this->addDebug("Old-New Style", $cNewish);

		$bFound = false;
		$bFound	= file_exists($cOriginal);

		//first round of founds
		if ($bFound) {
			$cReturn	= $cOriginal;
		} else {
			$bFound 	= file_exists($cOld);
		}

		//second round of founds
		if ($bFound) {
			$cReturn	= $cOld;
		} else {
			$bFound		= file_exists($cNewish);
		}

		//last round of founds
		if ($bFound) {
			$cReturn 		= $cNewish;
		} else {
			$this->cError	= "Not found any of the email templates";
		}

		if ($this->cError && $this->bDebug) { $this->debugTemplates(); }

		$this->cTemplate	= $cReturn;

		return $cReturn;
	}
}
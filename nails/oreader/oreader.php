<?php
/**
 * oReader
 *
 * @package
 * @author Keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class oReader {
	private $aData;

	/**
	 * oReader::__construct()
	 *
	 * @param mixed $mString
	 * @param mixed $aOptions
	 */
	function __construct($mString) {
		//there is nothing here, so why continue processing
		if (!$mString) { return null; }

		//options that are set later
		$this->mOriginal	= $mString;
		$this->aFile		= debug_backtrace();
		$this->cFormated	= print_r($mString, 1);

		//Show the methods of the class your trying diagnose
		if (is_object($mString)) { $this->cMethods = print_r(get_class_methods($mString), 1); }
	}

	/**
	 * oReader::doOutput()
	 *
	 * @desc this is because construct wont do echo
	 * @return mixed
	 */
	public function doOutput() {
		//it wants color, so
		if ($this->bColor) {
			$this->cOutput	 = $this->colorMe($this->cFormated);
			$this->cOutput	.= $this->colorMe($this->cMethods);
		} else {
			$this->cOutput  = $this->cFormated;
			$this->cOutput .= $this->cMethods;
		}

		//if its console then it needs a different method
		if ($this->bFirePHP || $this->bConsole) {
			$this->cConsole	 = $this->cFormated;
			$this->cConsole	.= $this->cMethods;
		}

		//turn it into new lines
		$this->cOutput	= str_replace(" ", "&nbsp;", $this->cOutput);
		$this->cOutput	= preg_replace("{[\t]+}", "&nbsp;&nbsp;&nbsp;&nbsp;", $this->cOutput);
		$this->cOutput	= nl2br($this->cOutput);

		//Protect stuff
		$this->cConsole	= $this->protectMe($this->cConsole);
		$this->cOutput	= $this->protectMe($this->cOutput);


		//now do we want a header
		if ($this->bEmail) { $this->cEmail = $this->cOutput; }
		if ($this->bConsole || $this->bFirePHP) {
			$cConsole	 = "printRead called by: " . $this->aFile[1]['file'] . "\n";
			$cConsole	.= "on line: " . $this->aFile[1]['line'] . "\n";
			$cConsole	.= $this->cConsole;

			$this->cConsole = $cConsole;
		}

		//get the output anyway
		$cOutput	 = "<b>printRead called by: " . $this->aFile[1]['file'] . "</b><br />";
		$cOutput	.= "<b>on line: " . $this->aFile[1]['line'] . "</b><br />";
		$cOutput	.= str_replace("<br&nbsp;/>", "<br />", $this->cOutput); //br gets added a space, so needs removing on old converted

		$this->cOutput = $cOutput;


		//start hte code to make it nice
		$cFinal = "<code>";

		//If there a title then bold it
		if (isset($this->cName)) { $cFinal .= "<b><u>" . ucwords($this->cName) . "</u></b><br />"; }

		//close the code to make it nice
		$cFinal .= $this->cOutput;
		$cFinal .= "</code><br />";

		//Send it back to object
		$this->cOutput = $cFinal;

		//Console remove all the tags since not in use for console, and firephp
		if ($this->bConsole || $this->bFirePHP) {
			$this->cConsole = str_replace("<br />", "\n", $this->cConsole);
			$this->cConsole = strip_tags($this->cConsole);

			if ($this->bFirePHP) {
				//now check the size
				if (strlen($this->cConsole) >= 1200) { //1200 for now
					$this->cConsole = "Output will cause 502 on php-fpm";
				}

				$this->FirePHP($this->cConsole, $this->cLevel);
			}
		}

		//now are we returning or echoing
		if ($this->bReturn) {
			if ($this->bConsole) { return $this->cConsole; }
			if ($this->bEmail) { return $this->cEmail; }

			return $this->cOutput;
		} else {
			if ($this->bConsole) {
				echo $this->cConsole;
			} else if ($this->bEmail) {
				echo $this->cEmail;
			} else {
				echo $this->cOutput;
			}
		}
	}

	/**
	 * oReader::protectMe()
	 *
	 * @param string $cString
	 * @return string
	 */
	private function protectMe($cString = null) {
		if (!$cString) { return false; } //since it could never have asked for console/firephp

		//split the lines and remove anything that might need protecting
		$aLines 	= explode("\n", $cString);
		$iLines		= count($aLines);
		$cFixed		= "";
		for ($i = 0; $i < $iLines; $i++) {
			if (strstr($aLines[$i], "[hostname]")) {
				$cFixed .= $this->hideProtected($aLines[$i]);
			} else if (strstr($aLines[$i], "[username]")) {
				$cFixed .= $this->hideProtected($aLines[$i]);
			} else if (strstr($aLines[$i], "[password]")) {
				$cFixed .= $this->hideProtected($aLines[$i]);
			} else if (strstr($aLines[$i], "[database]")) {
				$cFixed .= $this->hideProtected($aLines[$i]);
			} else {
				$cFixed .= $aLines[$i];
			}
		}

		return $cFixed;
	}

	/**
	 * oReader::colorMe()
	 *
	 * @param string $cString
	 * @return string
	 */
	private function colorMe($cString) {
		//different color modes depending on whats in it
		if ((strstr($cString, "Array")) || (strstr($cString, "Object ("))) { //PHP highlight
			$bAdded	= false;

			//add the php tag if needed
			if (!strstr($cString, "<?php")) {
				$bAdded 	= true;
				$cString	= "<?php " . $cString;
			}

			//now highlight the stuff
			if (function_exists("hightlight_string")) { $cString = highlight_string($cString, true); }

			//if added strip the <?php bit, only if added
			if ($bAdded) {
				$cStart	= substr($cString, 0, 35);
				$cRest	= substr($cString, 79);

				//the rest plus the start tag
				$cString = $cStart .= $cRest;
			}

		//XML highlught
		} else if (strstr($cString, "<?xml")) {
			$cString	= $this->xml_highlight($cString);

		//SQL highlight
		} else if ((stristr($cString, "SELECT")) && (stristr($cString, "FROM"))) {
			$cString	= $this->sql_highlight($cString);
		}

		return $cString;
	}

	/**
	 * oReader::__set()
	 *
	 * @param string $cName
	 * @param mixed $mData
	 * @return null
	 */
	public function __set($cName, $mData) { $this->aData[$cName] = $mData; }

	/**
	 * oReader::__get()
	 *
	 * @param string $cName
	 * @return mixed
	 */
	public function __get($cName) {
		$mReturn = false;
		if (isset($this->aData[$cName])) { $mReturn = $this->aData[$cName]; }

		return $mReturn;
	}

	/**
	 * oReader::hideProtected()
	 *
	 * @desc Used to hide passwords
	 * @param string $cString
	 * @return string
	 */
	private function hideProtected($cString) {
		$cReturn	= preg_replace('`(=>)(.*)`is', "\1**Protected**<br />", $cString);
		return $cReturn;
	}

	/**
	 * oReader::xml_highlight()
	 *
	 * @desc This is used to highlight XML
	 * @param string $cXML
	 * @return string
	 */
	private function xml_highlight($cXML) {
		$cRegex		= '`(<([a-z]+)([^>]*)>)(.*?)(</\2>)`is';
		$cReplace	= "\1\n\t\4\5";

		$cXML	= preg_replace_callback($cRegex, xml_parse_highlight($cXML), $cXML);

		//Special Chars
		$cXML	= htmlspecialchars($cXML);

		// debug FF00FF

		//Tag <> and values
		$cXML = preg_replace("#&lt;([/]*?)(.*)([\s]*?)&gt;#sU", "<font color=\"#0000FF\">&lt;\\1\\2\\3&gt;</font>", $cXML);

		//Attribute name
		$cXML = preg_replace("#&lt;([\?])(.*)([\?])&gt;#sU", "<font color=\"#800000\">&lt;\\1\\2\\3&gt;</font>", $cXML);

		//Tag Start
		$cXML = preg_replace("#&lt;([^\s\?/=])(.*)([\[\s/]|&gt;)#iU", "&lt;<font color=\"#808000\">\\1\\2</font>\\3", $cXML);

		//Tag End
		$cXML = preg_replace("#&lt;([/])([^\s]*?)([\s\]]*?)&gt;#iU", "&lt;\\1<font color=\"#808000\">\\2</font>\\3&gt;", $cXML);

		//Attribute values
		$cXML = preg_replace("#([^\s]*?)\=(&quot;|')(.*)(&quot;|')#isU", "<font color=\"#800080\">\\1</font>=<font color=\"#D14769\">\\2\\3\\4</font>", $cXML);

		//CDATA
		$cXML = preg_replace("#&lt;(.*)(\[)(.*)(\])&gt;#isU", "&lt;\\1<font color=\"#800080\">\\2\\3\\4</font>&gt;", $cXML);

		//Find the start of the tag, and then find the end of it, so that I can seperate it properlly

		//New Line
		$cXML = preg_replace("#&gt;</font><font color=\"\#0000FF\">&lt;(.*)#isU", "&gt;</font><font color=\"#0000FF\"><br />&nbsp;&nbsp;&lt;\\1", $cXML);
		$cXML = preg_replace("#<br />&nbsp;&nbsp;&lt;/#isU", "<br />&lt;/", $cXML);
		$cXML   = preg_replace("{[\t]+}", "&nbsp;&nbsp;&nbsp;&nbsp;", $cXML);

		return nl2br($cXML);
	}

	/**
	 * oReader::xml_parse_highlight()
	 *
	 * @param string $cString
	 * @return string
	 */
	private function xml_parse_highlight($cString) {
		$cRegex		= '`(<([a-z]+)([^>]*)>)(.*?)(</\2>)`is';
		$cReplace	= "\1\n\t\4\5";

		return preg_replace($cRegex, $cReplace, $cString);
	}

	/**
	 * oReader::sql_highlight()
	 *
	 * @param string $cSQL
	 * @return string
	 */
	private function sql_highlight($cSQL) {
		$cStart = "<font color=\"#800000\">";
		$cEnd	= "</font>";

		//SELECT, WHERE
		$cSQL 	= str_ireplace("SELECT", "<font color=\"#0000FF\">SELECT</font>", $cSQL);
		$cSQL	= str_ireplace("WHERE", "<font color=\"#0000FF\">\nWHERE</font>", $cSQL);
		$cSQL 	= str_ireplace("(SELECT", "<font color=\"#0000FF\">(\nSELECT</font>", $cSQL);

		//AND, LIKE
		$cSQL	= str_ireplace("LIKE", "<font color=\"#0000FF\">LIKE</font>", $cSQL);
		$cSQL	= str_ireplace("AND", "<font color=\"#0000FF\">\nAND</font>", $cSQL);

		//FROM, JOIN, LEFT JOIN, RIGHT JOIN
		$cSQL	= str_ireplace('FROM', "<font color=\"#0000FF\">\nFROM</font>", $cSQL);
		$cSQL	= str_ireplace('JOIN', "<font color=\"#0000FF\">\nJOIN</font>", $cSQL);
		$cSQL	= str_ireplace('LEFT JOIN', "<font color=\"#0000FF\">\nLEFT JOIN</font>", $cSQL);
		$cSQL	= str_ireplace('RIGHT JOIN', "<font color=\"#0000FF\">\nRIGHT JOIN</font>", $cSQL);

		//LIMIT, GROUP
		$cSQL	= str_ireplace('LIMIT', "<font color=\"#800080\">\nLIMIT</font>", $cSQL);
		$cSQL	= str_ireplace('GROUP', "<font color=\"#800080\">\nGROUP</font>", $cSQL);

		//BY, AS
		$cSQL	= str_ireplace('BY', "<font color=\"#800080\">BY</font>", $cSQL);
		$cSQL	= str_ireplace('AS', "<font color=\"#800080\">AS</font>", $cSQL);

		//Parenthesses
		$cSQL	= str_ireplace('(', "<font color=\"#800080\">(</font>", $cSQL);
		$cSQL	= str_ireplace(')', "<font color=\"#800080\">)</font>", $cSQL);


		$cFinal	= $cStart . $cSQL . $cEnd;
		return nl2br($cFinal);
	}

	/**
	 * oReader::FirePHP()
	 *
	 * @desc this actually doesnt return a real object, it actually pushes to browser
	 * @return object
	 */
	private function FirePHP($cMessage, $cLevel = false) {
		$oFirePHP = new FirePHP();

		//set the level to UC
		$cLevel = strtoupper($cLevel);

		//different levels
		switch($cLevel) {
			case "NOTICE":
				$oFirePHP->info($cMessage, " Level:" . $cLevel);
				break;

			case "ERROR":
				$oFirePHP->error($cMessage, " Level:" . $cLevel);
				break;

			case "WARNING":
				$oFirePHP->warn($cMessage, " Level:" . $cLevel);
				break;

			default:
				if (!$cLevel) {
					$oFirePHP->log($cMessage);
				} else {
					$oFirePHP->log($cMessage, " Level:" . $cLevel);
				}
				break;
		}

		/**
		$oInstance 	= FirePHP::getInstance(true);
		$aArgs		= func_get_args();
		return call_user_func_array(array($oInstance, "fb"), $aArgs);
		*/
	}
}
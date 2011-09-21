<?php
trait Security {
	/**
 	* hammerHash()
 	*
 	* @param string $cString
 	* @param int $iStrength
 	* @return string
 	*/
	function hammerHash($cString, $iStrength = false) {
		if (function_exists("hash")) { //has isnt installed so revert to sha1
			if ($iStrength) {
				switch ($iStrength) {
					case 3:
						$cHash = "sha512";
						break;

					case 2:
						$cHash = "sha384";
						break;

					case 1:
					default:
						$cHash = "sha256";
						break;
				}
			} else {
				$cHash = "sha256";
			}

			$cReturn	= hash($cHash, $cString);
		} else {
			$cReturn	= sha1(uniqid(rand() . $cString));
		}

		return $cReturn;
	}

	/**
	 * genHash()
	 *
	 * @param string $cString
	 * @return string
	 */
	function genHash($cString) {
		$cRand		= "";

		//get the hash
		$cHash 		= $this->hammerHash($cString, 1);
		$iHash		= strlen($cHash);

		//now go through and generate a random one based on that
		for ($i = 0; $i < 20; $i++) {
			$iRand_a	= rand(0, ($iHash - 1));
			$iRand_b	= ($iRand_a + 1);

			$cRand .= $cHash[$iRand_a];
			$cRand .= round($iRand_b + ($iRand_b / ($iHash - 1) * $iRand_b));
		}

		return $cRand;
	}

	/**
	 * hideProtected()
	 *
	 * @desc This is used to hide passwords/hostnames from being sent through the email, although it doesnt work with hte xml
	 * @param string $cString
	 * @return
	 */
	function hideProtected($cString) {
		$cReturn	= preg_replace('`(=>)(.*)`is', "\1**Protected**<br />", $cString);
		return $cReturn;
	}

	/**
	 * genPassword()
	 *
	 * @param int $iLength
	 * @return string
	 */
	function genPassword($iLength) {
		$cRand 		= "";
		$aLetters	= range("a", "z");
		$aNumbers	= range(0, 9);
		$aContent	= array_merge($aLetters, $aNumbers);

		for ($i = 0; $i < $iLength; $i++) {
			$iRand  = array_rand($aContent);
			$cRand .= $aContent[$iRand];
		}

		return $cRand;
	}

	/**
	 * genName()
	 *
	 * @param int $iLength
	 * @return string
	 */
	function genName($iLength) {
		$cRand		= "";
		$aLetters	= rand("a", "z");

		for ($i = 0; $i < $iLength; $i++) {
			$iRand	 = array_rand($aLetters);
			$cRand	.= $aLetters[$iRand];
		}

		return $cRand;
	}
}

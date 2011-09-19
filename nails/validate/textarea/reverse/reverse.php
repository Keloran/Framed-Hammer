<?php
/**
 * Validate_TextArea_Reverse
 *
 * @package
 * @author keloran
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class Validate_TextArea_Reverse {
	//Traits
	use Text;

	public $mValue;
	private $mPreValue;

	/**
	 * Validate_TextArea_Reverse::__construct()
	 *
	 */
	function __construct() {

	}

	/**
	 * Validate_TextArea_Reverse::validate()
	 *
	 * @param mixed $mEntry
	 * @return mixed
	 */
	public function validate($mEntry) {
		$this->mPrevalueValue	= $mEntry;
		$this->mValue			= $this->doValidate();

		return $this;
	}

	/**
	 * Validate_TextArea_Reverse::doValidate()
	 *
	 * @return string
	 */
	private function doValidate() {
		if (!isset($cText[1])) { return false; }

		//reverse others
		$cReturn	= $this->reverseLanguage($cText);
		$cReturn	= $this->reverseOthers($cReturn);

		$aSearch	= array(
			//urls of various types
			'`(<a rel="no-follow" href="(.+?)" title="(.+?)">)(.+?)(<\/a>)`si',
			'`(<a href="(.+?)" rel="no-follow">)(.+?)(<\/a>)`si',
            '`(<a href="(.+?)" title="(.+?)">)(.+?)(<\/a>)`is',
			'`(<a href="(.+?)">)(.+?)(<\/a>)`is',

			//images
			'`(<img src="(.+?)" \/>)`is',
			'`(<img src="(.+?)" alt="(.+?)" \/>)`is',

			//lits
			'`(<ul>)(.+?)(<\/ul>)`is',
			'`(<li>)(.+?)(<\/li>)`is',

			//newlines
			'`<br />`is',
			'`<hr />`is',

			//headings
			'`(<h([0-6])>)(.+?)(<\/h([0-6])>)`is',

			//word crap
			'`&#96;(.+?)&#180;`is',
			'`&#8220;(.+?)&#8221;`is',

			//youtube
			'`(<object width="425" height="344"><param name="movie" value="http://www.youtube.com/v/(.+?)"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/(.+?)" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="425" height="344"></embed></object>)`is',

			//formatting
			'`(<em>)(.+?)(<\/em>)`is',
			'`(<span style="border-bottom: 1px dotted">)(.+?)(<\/span>)`is',
			'`(<div class="quote">)(.+?)(<\/div>)`is',
			'`(<code>)(.+?)(<\/code>)`is',
			'`(<strong>)(.+?)(<\/strong>)`is',
			'`(<pre>)(.+?)(<\/pre>)`is',
			'`(<span style="color:([a-zA-Z0-9\#]+);">)(.+?)(<\/span>)`is',
			'`(<p>)(.+?)(<\/p>)`is',
			'`(<p id="([a-zA-Z0-9]+)">)(.+?)(<\/p>)`is',
        );

		$aReplace	= array(
			//urls of various types
			'[urn=\2 title=\3]\4[/urn]',
			'[urn=\2]\3[/urn]',
            '[url=\2 title=\3]\4[/url]',
			'[url=\2]\3[/url]',

			//images
			'[img]\2[/img]',
			'[img alt=\3]\2[/img]',

			//lists
			'[list]' . "\n" . '\2[/list]' . "\n", //again needs newline
			'[*]\2' . "\n", //nees to be  a newline

			//newlines
			"\n",
			'[hr][/hr]',

			//headings
			'[h\2]\3[/h\5]',

			//word crap
			'[qu]\1[/qu]',
			'[qua]\1[/qua]',

			//youtube
			'[youtube]\2[/youtube]',

			//formatting
			'[i]\2[/i]',
			'[u]\2[/u]',
			'[quote]\2[/quote]',
			'[code]\2[/code]',
			'[b]\2[/b]',
			'[i]\2[/i]',
			'[color=\2]\3[/color]',
			'[p]\2[/p]',
			'[p id=\2]\3[/p]',
        );

		//smileys
		$aSearch_b = array(
			'`(<img src="\/images\/smiles\/)([a-zA-Z]+)(\.png" alt=")([a-zA-Z]+)(" \/>)`is',
		);

		$aReplace_b = array(
			':\4:',
		);

		$cReturn	= preg_replace($aSearch, $aReplace, $cReturn);

		//Set these ignores if in use
		if ($cIgnore) {
			switch($cIgnore){
				case "smileys":
					break;
			} // switch
		} else {
			$cReturn = preg_replace($aSearch_b, $aReplace_b, $cReturn);
		}

		return $cReturn;
	}

	/**
	 * Validate_TextArea_Reverse::reverseLanguage()
	 *
	 * @param string $cText
	 * @return string
	 */
	private function reverseLanguage($cText) {
		$aPattern = array(
			'`&#138;`is',
			'`&#142;`is',
			'`&#154;`is',
			'`&#158;`is',
			'`&#159;`is',
			'`&#192;`is',
			'`&#193;`is',
			'`&#194;`is',
			'`&#195;`is',
			'`&#196;`is',
			'`&#197;`is',
			'`&#198;`is',
			'`&#199;`is',
			'`&#200;`is',
			'`&#201;`is',
			'`&#202;`is',
			'`&#203;`is',
			'`&#204;`is',
			'`&#205;`is',
			'`&#206;`is',
			'`&#207;`is',
			'`&#208;`is',
			'`&#209;`is',
			'`&#210;`is',
			'`&#211;`is',
			'`&#212;`is',
			'`&#213;`is',
			'`&#214;`is',
			'`&#215;`is',
			'`&#216;`is',
			'`&#217;`is',
			'`&#218;`is',
			'`&#219;`is',
			'`&#219;`is',
			'`&#220;`is',
			'`&#221;`is',
			'`&#222;`is',
			'`&#223;`is',
			'`&#224;`is',
			'`&#225;`is',
			'`&#226;`is',
			'`&#227;`is',
			'`&#228;`is',
			'`&#229;`is',
			'`&#230;`is',
			'`&#231;`is',
			'`&#232;`is',
			'`&#233;`is',
			'`&#234;`is',
			'`&#235;`is',
			'`&#236;`is',
			'`&#237;`is',
			'`&#238;`is',
			'`&#239;`is',
			'`&#240;`is',
			'`&#241;`is',
			'`&#242;`is',
			'`&#243;`is',
			'`&#244;`is',
			'`&#245;`is',
			'`&#246;`is',
			'`&#248;`is',
			'`&#249;`is',
			'`&#250;`is',
			'`&#251;`is',
			'`&#252;`is',
			'`&#253;`is',
			'`&#254;`is',
			'`&#255;`is',
		);

		$aReplace	= array(
			'Š',
			'Ž',
			'š',
			'ž',
			'Ÿ',
			'À',
			'Á',
			'Â',
			'Ã',
			'Ä',
			'Å',
			'Æ',
			'Ç',
			'È',
			'É',
			'Ë',
			'Ì',
			'Í',
			'Î',
			'Ï',
			'Ð',
			'Ñ',
			'Ò',
			'Ó',
			'Ô',
			'Õ',
			'Ö',
			'×',
			'Ø',
			'Ù',
			'Ú',
			'Û',
			'Ü',
			'Ý',
			'Þ',
			'ß',
			'à',
			'á',
			'â',
			'ã',
			'ä',
			'å',
			'æ',
			'ç',
			'è',
			'é',
			'ê',
			'ë',
			'ì',
			'í',
			'î',
			'ï',
			'ð',
			'ñ',
			'ò',
			'ó',
			'ô',
			'õ',
			'ö',
			'ø',
			'ù',
			'ú',
			'û',
			'ü',
			'ý',
			'þ',
			'ÿ',
		);

		$cReturn = preg_replace($aPattern, $aReplace, $cText);

		return $cReturn;
	}

	/**
	 * Validate_TextArea_Reverse::reverseOthers()
	 *
	 * @param string $cText
	 * @return string
	 */
	private function reverseOthers($cText) {
		$aPattern = array(
			'`&#63;`is',
			'`&#163;`is',
			'`&#164;`is',
			'`&#165;`is',
			'`&#191;`is',
		);

		$aReplace = array(
			'?',
			'£',
			'¤',
			'¥',
			'¿',
		);

		$cReturn = preg_replace($aPattern, $aReplace, $cText);

		return $cReturn;
	}
}
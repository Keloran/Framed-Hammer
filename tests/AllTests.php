<?php
require_once ("PHPUnit/Framework.php");
require_once("/Hammer/hammer.php");

/**
 * Tester
 *
 * @package FirstTest
 * @author Max
 * @copyright Copyright (c) 2008
 * @version $Id: alltess.php 482 2010-01-04 14:26:49Z keloran $
 * @access public
 *
 */
class FirstTest extends PHPUnit_Framework_TestCase {
	private $oHammer	= false;

	public function setUp() {
		try {
			$this->oHammer	= new Hammer("Tester");
		} catch (Hammer $e) {
			$this->markTestSkipped("Hammer Failed");
		}

		$this->assertTrue($this->oHammer instanceof Hammer);
	}

	public function testGetSiteSkin() {
		$this->oHammer->cSkin = "tester";
		$this->assertEquals("tester", $this->oHammer->cSkin);
	}
}

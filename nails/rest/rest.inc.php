<?php
class Rest extends Nails {
	function __construct() {
		parent::__construct();

		//do the upgrade
		if ($this->checkVersion("rest", "1.0") == false) {
			//1.0
			$this->addVersion("rest", "1.0");
		}
	}

	function get($mRequest) {
	}

	function post($mRequest) {
	}

	function delete($mRequest) {
	}
}
<?php
if (!defined("HAMMERPATH")) {
	$cPath	 = "../";
	$cPath	.= dirname(__FILE__);
	define("HAMMERPATH", $cPath);
}

$cPath = HAMMERPATH . "/functions/";
$aFiles = false;
if ($pHandle_a = opendir($cPath)) {
	while (false !== ($mFile_a = readdir($pHandle_a))) {
		//skips
		if ($mFile_a == "templates") { continue; }
		if ($mFile_a == ".") { continue; }
		if ($mFile_a == "..") { continue; }

		//set the path
		$mFile_a = $cPath . $mFile_a;

		//go into the folder
		if (is_dir($mFile_a)) {
			if ($pHandle_b = opendir($mFile_a)) {
				while(false !== ($mFile_b = readdir($pHandle_b))) {
					//skips
					if ($mFile_b == ".") { continue; }
					if ($mFile_b == "..") { continue; }
					if (strpos($mFile_b, "swp")) { continue; } //swap file, should not be included

					//Only files left
					$cPath_b = $mFile_a . "/" . $mFile_b;
					if (is_file($cPath_b)) {
						$aFiles[] = $cPath_b;
					}
				}
				closedir($pHandle_b);
			}
		} else {
			if (strpos($mFile_a, "swp")) { continue; } //swap file, should not be included
			$aFiles[] = $mFile_a;
		}
	}
	closedir($pHandle_a);
}

//now include the new files
if ($aFiles) {
	for($i = 0; $i < count($aFiles); $i++) {
		include_once($aFiles[$i]);
	}
}

//now 5.2 specific
if (PHP_VERSION <= 5.3) {
	$cPath = HAMMERPATH . "/nails/traits/";
	$aFiles = false;
	if ($pHandle_a = opendir($cPath)) {
		while (false !== ($mFile_a = readdir($pHandle_a))) {
			//skips
			if ($mFile_a == "templates") { continue; }
			if ($mFile_a == ".") { continue; }
			if ($mFile_a == "..") { continue; }

			//set the path
			$mFile_a = $cPath . $mFile_a;

			//go into the folder
			if (is_dir($mFile_a)) {
				if ($pHandle_b = opendir($mFile_a)) {
					while(false !== ($mFile_b = readdir($pHandle_b))) {
						//skips
						if ($mFile_b == ".") { continue; }
						if ($mFile_b == "..") { continue; }
						if (!strpos($mFile_b, "fn")){ continue; } //its proberlly a trait file
						if (strpos($mFile_b, "swp")) { continue; } //swap file, should not be included

						//Only files left
						$cPath_b = $mFile_a . "/" . $mFile_b;
						if (is_file($cPath_b)) {
							$aFiles[] = $cPath_b;
						}
					}
					closedir($pHandle_b);
				}
			}
		}
		closedir($pHandle_a);
	}

	//now include the new files
	if ($aFiles) {
		for($i = 0; $i < count($aFiles); $i++) {
			include_once($aFiles[$i]);
		}
	}
}

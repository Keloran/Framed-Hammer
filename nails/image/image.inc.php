<?php
/**
* This library is from Tim Igoes framework
* modified to work with Hammer
* original code is owned by Tim Igoe (tim@timigoe.co.uk)
*/
class Image {
	static $oImage;

	/**
	 * Image::getInstance()
	 *
	 * @return object
	 */
	static function getInstance() {
		if (is_null(self::$oImage)) {
			self::$oImage	= new Image();
		}

		return self::$oImage;
	}

	/**
	 * Image::__construct()
	 *
	 */
	private function __construct() {}

	//resize the image
	public function resizer($cFilename, $iMaxX, $iMaxY, $cType = 'jpg') {
		$aImgInfo 		= getimagesize($cFilename);
		$cImageType 	= image_type_to_mime_type($aImgInfo[2]);
		$cPictureData	= false;

		//dictate of the rate of reduction
		if ($aImgInfo[0] > $iMaxX) { $iRate_x = $aImgInfo[0] / $iMaxX; } else { $iRate_x = $aImgInfo[0]; }
		if ($aImgInfo[1] > $iMaxY) { $iRate_y = $aImgInfo[1] / $iMaxY; } else { $iRate_y = $aImgInfo[1]; }

		//this is to keep it in aspect
		$iMinX	= ceil(($iMaxX / 100) * 95);
		$iMinY	= ceil(($iMaxY / 100) * 95);

		//set the rate
		if ($iRate_x > $iRate_y) { //width larger than height
			$iRate = $iRate_x;
		} else { //width smaller or same size as height
			$iRate = $iRate_y;
		}

		// Fudge
		if (!isset($iRate)) { $iRate = 1; }

		//try to solve the medium image problem
		$iNewH = floor($aImgInfo[0] / $iRate);
		if ($iNewH < $iMinY) { $iNewh = $iMinY; } else { $iNewh = $iNewH; }

		//new width
		$iNewW = floor($aImgInfo[1] / $iRate);
		if ($iNewW < $iMinX) { $iNeww = $iMinX; } else { $iNeww = $iNewW; }

		//dictates what we use to create the new image
		switch($cImageType) {
			case "image/jpeg":
			case "image/jpg":
			case "image/pjpeg":
				$pSrc = imagecreatefromjpeg($cFilename);
				$pDst = imagecreatetruecolor($iNewh, $iNeww);
				break;

			case "image/gif":
				$pSrc = imagecreatefromgif($cFilename);
				$pDst = imagecreatetruecolor($iNewh, $iNeww);
				break;

			case "image/png":
				$pSrc = imagecreatefrompng($cFilename);
				$pDst	= imagecreatetruecolor($iNewh, $iNeww);
				break;

			case "image/tiff":
			case "image/tif":
				if (class_exists("Imagick")) {
					$cName	= substr($cFilename, 0, -4) . ".jpg";

					//take the image and turned it into a png hopefully
					$oImage	= new Imagick();
					$oImage->readImage($cFilename);
					$oImage->resizeImage($iNewh, $iNeww, Imagick::FILTER_CATROM, 1);
					$oImage->setImageFormat("jpg");
					$oImage->writeImage($cName);
					$oImage->clear();
					$oImage->destroy();

					//send to the resizer
					return $this->resizer($cName, $iMaxX, $iMaxY);
				} else {
					throw new Spanner("Image Magick doesn't exist", 400);
				}
				break;
		}

		//if theres a new destination image, now do stuff with it
		if (isset($pDst)) {
			ImageCopyResampled($pDst, $pSrc, 0, 0, 0, 0, $iNewh, $iNeww, ImageSX($pSrc), ImageSY($pSrc));

			ob_start(); // Start capturing stdout.
				switch ($cType) {
					case 'jpg':
						ImageJPEG($pDst); // As though output to browser.
						break;

					case 'png':
						ImagePNG($pDst);
						break;

					default:
						throw new Spanner('Unknown Image Parser', 401);
						break;
				}

				$cPictureData = ob_get_contents(); // the raw jpeg image data.
			ob_end_clean(); // Dump the stdout so it does not screw other output.

			imagedestroy($pSrc);
			imagedestroy($pDst);

			unset($aImgInfo);
		}

		return $cPictureData;
	}

	//resize the image, and add black borders
	public function resizerCropped($cFilename, $iMaxX, $iMaxY, $cType = 'png') {
		$iMaxX2	= $iMaxX + 50;
		$iMaxY2	= $iMaxY + 50;

		$cImage = $this->resizer($cFilename, $iMaxX2, $iMaxY2, $cType);
		$pCropImage = imagecreatefromstring($cImage);

		if (ImageSY($pCropImage) > $iMaxY) {
			// Crop the image down to $MaxY px high
			ob_start(); // Start capturing stdout.
				$pCroppedImage = imagecreatetruecolor($iMaxX, $iMaxY);
				ImageCopyResampled($pCroppedImage, $pCropImage, 0, 0, 0, 0, $iMaxX, $iMaxY, $iMaxX, $iMaxY);
				ImagePNG($pCroppedImage);
				$cImage = ob_get_contents(); // the raw jpeg image data.
			ob_end_clean();
		}

		return $cImage;
	}
}
?>

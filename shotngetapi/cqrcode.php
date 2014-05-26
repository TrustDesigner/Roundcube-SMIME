<?php

if(isset($init_flag) == false)
	die;
	
/**
* this class is use to make a qr code image.
**/
class CQrCode{
	// ********************************************************************************************
	const PARAM_NAME = 'shotnget';
	
	private $imgPath;
	private $imgName;
	
	private $useLogo;

	private $base64;
	
	// ********************************************************************************************
	/**
		@param CConf A CConf Object
	**/
	public function __construct($imgPath = null) {
		CDebugger::$debug->tracein('__construct', 'CQrCode');
		
		if($imgPath == null)
			$imgPath = CConstants::$API_PATH.'temp/';
		
		$this->imgPath = $imgPath;
		
		$this->useLogo = true;
		
		CDebugger::$debug->traceout(true);
	}

	// ********************************************************************************************
	/**
	* Generate the qr code image and return the image path.
	* @param CRequest $request the current request linked to the qr cde
	@return string The qr code image path
	**/
	public function generate($request){
		CDebugger::$debug->tracein('generate', 'CQrCode');
		
		if($request != null){
			require ('phpqrcode/qrlib.php'); 
			
			$errorCorrectionLevel = 'L';
			$matrixPointSize = 4;

			$text = $request->getUrlRep().'?'.CQrCode::PARAM_NAME.'='.$request->getRand();
			
			//error_log("Filename : " . tempnam("./", "qrcodeTemp"));
			$filename = tempnam("./", "qrcode");

			QRcode::png($text, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
			
			if($this->useLogo == true)
				$this->addLogo($filename);
			
			if ($fd = fopen($filename, "rb", 0)) {
			  $gambar = fread($fd, filesize($filename));
			  fclose($fd);
			  $base = chunk_split(base64_encode($gambar));
			  $this->base64 = 'data:image/png;base64,' . $base;
			  unlink($filename);
			}

			CDebugger::$debug->traceout(true);
			return $this->base64;
		}
		else {
			CDebugger::$debug->traceout(false);
			return false;
		}
	}
	
	// ********************************************************************************************
	private function generateFilename() {
		CDebugger::$debug->tracein('generateFilename', 'CQrCode');
		
		$generate = true;
		while($generate){
			$filename = '';
			for( $i=0;$i<8;$i++ )
				$filename .= rand( 10, 99 );

			if(!file_exists($this->imgPath.$filename.'.png'))
				$generate = false;
		}
		
		$this->imgName = $filename.'.png';
		
		CDebugger::$debug->traceout(true);
		return $this->imgPath.$filename.'.png';
	}
	
	private function addLogo($filename){
		CDebugger::$debug->tracein('addLogo', 'CQrCode');
		
		$srcFile = $filename;
		$src = imagecreatefrompng($srcFile);
		$infosSrc = getimagesize($srcFile);

		$cadreFile = CConstants::$API_PATH.'cadre.png';
		
		$cadre = imagecreatefrompng($cadreFile);
		$infosCadre = getimagesize($cadreFile);
		
		$ratio = $infosSrc[0] / $infosCadre[0];
		$cradre2Largeur = $infosCadre[0] * $ratio;
		$cradre2hauteur = $infosCadre[1] * $ratio;
		
		$cadre2 = imagecreatetruecolor($cradre2Largeur, $cradre2hauteur);
		imagecopyresized($cadre2, $cadre, 0, 0, 0, 0, $cradre2Largeur, $cradre2hauteur, $infosCadre[0], $infosCadre[1]);
		
		$qrcode = imagecreatetruecolor($infosSrc[0], $infosSrc[1] + $cradre2hauteur);

		imagecopy($qrcode,$src, 0, 0, 0, 0, $infosSrc[0], $infosSrc[1]);
		imagecopy($qrcode,$cadre2, 0, $infosSrc[1], 0, 0, $cradre2Largeur, $cradre2hauteur);
		
		imagepng($qrcode, $filename);
		
		imagedestroy($src);
		imagedestroy($cadre);
		imagedestroy($qrcode);
		
		CDebugger::$debug->traceout(true);
	}
	
	// ********************************************************************************************
	public function getImgName() { return $this->imgName; }
	
	public function isUseLogo() { return $this->useLogo; }
	public function setUseLogo($useLogo) { $this->useLogo = $useLogo; }
	
}

?>
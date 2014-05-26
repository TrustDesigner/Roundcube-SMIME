<?php
/**
 * Shotnget Mail / get_code
 *
 * This page is used to generate the QRcode to flash with the ShotNGet application
 *
 * @version 1.0
 * @author Trust Designer, Tourte Alexis
 * @url
 */

	
$apiPath = './shotngetapi/';
require($apiPath.'shotnget_api.php');

$apiParameters = new ApiParameters($apiPath);
$apiManager = new ApiManager($apiParameters);

$requestParameters = $apiManager->generateNewRequest(Config::$RESPONSE_URL);

if($requestParameters != null)
  {
    // display qr code
    $imgPath = $requestParameters->getImgPath();
    echo '<img id="shotnget_code" src="'.$imgPath.'" /><div style="display:none;" id="shotnget_rand" >'.$requestParameters->getRand().'</div><br />';
  }
else
  {
    echo $apiManager->getErrors();
  }

?>
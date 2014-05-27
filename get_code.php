<?php
/**
 * Shotnget SMIME / get_code
 *
 * This page is used to generate the QRcode to flash with the ShotNGet application
 *
 * @version 1.0
 *
 * shotnget_smime is a roundcube plugin used for SMIME signature / decipherment and connections
 * Copyright (C) 2007-2014 Trust Designer,  Tourte Alexis
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
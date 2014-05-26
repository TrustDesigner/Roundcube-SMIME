<?php
	if(!isset($flagPassage))
		die();

	$rand = $response->getRand();
	$filename = $randPath.$rand;
	
	$oldData = @file_get_contents($filename.'.rand');
	$oldResponse = new CResponse('', '');
	$oldResponse->parseData($oldData);

	$file = fopen($filename.'.rand', 'w+');
	traceIn($data);
	fputs($file, $data);
	fclose($file);

	//delete old flag file
	@unlink($filename.'.flag');

	//cré le fichier .rep
	$file = fopen($filename.'.rep', 'w+');
	fclose($file);

	//wait for next cmds
	$TPS_ATTENTE = 30; // en secondes
	$FREQ_TEST = 1; //en secondes

	$nbTests = $TPS_ATTENTE / $FREQ_TEST;
	$ok = false;

	for($i=0;$i<$nbTests;$i++){
		sleep($FREQ_TEST);

		if(file_exists($filename.'.flag')){
			header ("Content-Type:text/xml");
			@unlink($filename.'.flag');

			$i = $nbTests;
			$ok = true;

			$xml = file_get_contents($filename.'.rand');
			traceOut($xml);
			echo $xml;
		}
	}

	if($ok == false){
		header ("Content-Type:text/xml");

		$req = new CRequest('', '');
		$req->initWithRand($rand);
		$req->setUrlSign($oldResponse->getUrlSign());

		$cmdResp = new CCmdResp('resp');
		$cmdResp->setResult(CErrors::RESULT_SERVER_ERROR);
		$cmdResp->setMsg("Server timeout !");

		$req->addCmd($cmdResp);
		$xml = $req->serializer('SRVTOCP');
		traceOut($xml);
		echo $xml;
	}




	function traceIn($data) {
		/* $file = fopen('C:\certphone\log\mobile_in.txt', 'a+'); */

		/* if($file != false) */
		/* { */
		/* 	fputs($file, $data."\n\n"); */
		/* 	fclose($file);	 */
		/* } */
	}

	function traceOut($data) {
		/* $file = fopen('C:\certphone\log\mobile_out.txt', 'a+'); */

		/* if($file != false) */
		/* { */
		/* 	fputs($file, $data."\n\n"); */
		/* 	fclose($file);	 */
		/* } */
	}

?>

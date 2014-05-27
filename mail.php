<?php
/**
 * Shotnget SMIME / mail
 *
 * Class used to deformat mail
 *
 * @version 1.0
 *
 * shotnget_smime is a roundcube plugin used for SMIME signature / decipherment and connections
 * Copyright (C) 2007-2014 Trust Designer, Tourte Alexis
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
 
class mail {
  public $headers = array();
  public $subHeaders = array();
  public $subHeadersOf = array();
  public $parts = array();

  function __construct($mail, $decodeParts = false) {
    $this->decodeHeaders($mail, $this->headers, $this->subHeaders, $this->subHeadersOf);
    if ($decodeParts === true)
      $this->decodeMail($mail);
  }

  private function decodeMail($msg) {
    $len = 2;
    if (($posfinheader = strpos($msg, "\n\n")) === false) {
      $len = 4;
      if (($posfinheader = strpos($msg, "\r\n\r\n")) === false)
	return;
    }
    $msg = substr($msg, $posfinheader + $len);
    if ($this->headers['Content-Type'] == "multipart/signed") {
      $this->decodeMultipart($msg);
    } else {
      $this->parts[] = array('content' => $msg);
    }
  }
  
  private function decodeMultipart($msg) {
    $boundary = "--".$this->subHeaders['boundary'];
    while ($msg != "") {
      $boundmsg = $this->readBoundary($msg, $boundary, $nextmsg);
      if ($boundmsg == false)
	break;
      $this->parts[] = $this->decodePart($boundmsg);
      $msg = $nextmsg;
    }
  }

  private function decodePart($msg) {
    $part = array();
    $len = 2;
    if (($posfinheader = strpos($msg, "\n\n")) === false) {
      $len = 4;
      if (($posfinheader = strpos($msg, "\r\n\r\n")) === false)
        return array("headers" => array(), "content" => $msg);
    }

    $part['headers'] = array();
    $part['subheaders'] = array();
    $part['subheadersOf'] = array();
    $this->decodeHeaders($msg, $part['headers'], $part['subheaders'], $part['subheadersOf']);
    $part['content'] = substr($msg, $posfinheader + $len);
    return $part;
  }
  
  private function readBoundary($msg, $boundary, &$nextmsg) {
    $tab = array();
    $i = 0;
    $bound = "";
    $boundaryLenght = strlen($boundary);

    // Search for the first separator
    if (($pos = strpos($msg, $boundary)) === false)
      return false; // Not found
    // Clean the start of the part
    $msg = substr($msg, $pos + $boundaryLenght);
    $msg = substr($msg, 0, 1) == "\r" ? substr($msg, 1) : $msg;
    $msg = substr($msg, 0, 1) == "\n" ? substr($msg, 1) : $msg;
    // Searching for another separator
    $pos = strpos($msg, $boundary);
    if ($pos === false) {
      // Not found, the end of the message
      $nextmsg = "";
    } else {
      // Get the part form the message
      $bound = substr($msg, 0, $pos);
      // Remove the part which is already treated
      $nextmsg = substr($msg, $pos);
    }
    return trim($bound);
  }
  
  private function decodeHeaders($headermsg, &$headers, &$subHeadersParam, &$subHeadersOf) {
    $tab = array();
    $i = 0;
    $j = 0;

    $len = 2;
    if (($posfinheader = strpos($headermsg, "\n\n")) === false) {
      $len = 4;
      if (($posfinheader = strpos($headermsg, "\r\n\r\n")) === false)
        return;
    }
    $headermsg = substr($headermsg, 0, $posfinheader + $len);
    // Transform message in array with maxximum of 100 lignes because $headermsg contains all the message
    $tabmsg = explode("\n", $headermsg, 100);

    // Clean the array
    while(($line = $tabmsg[$i++]) != "") {
      $line = str_replace(array("\r", "\t"), array("", " "), $line);
      if(substr($line, 0, 1) != " ") {
	$j++;
	$tab[$j] = $line;
      }
      else {
	$tab[$j] .= ($len == 2 ? "\n" : "\r\n").$line;
      }
    }

    foreach ($tab as $line) {
      $header = explode(":", $line, 2);
      if(count($header) > 1) {
	$key = $header[0];
	// Check if sub headers exist in this header
	$subHeadersOf[$key] = trim($header[1]);
	$subHeaders = str_replace(array("\""), array(""), $header[1]);
	$subHeaders = explode(";", $subHeaders);
	// If sub Headers exists
	if (count($subHeaders) > 1) {
	  $headers[$key] = trim($subHeaders[0]);
	  $i = 1;
	  // For each sub header, add it inside headers
	  while ($i != count($subHeaders)) {
	    $subHeadersType = explode("=", $subHeaders[$i], 2);
	    if (count($subHeadersType) > 1) {
	      $key = trim($subHeadersType[0]);
	      $subHeadersParam[$key] = trim($subHeadersType[1]);
	    }
	    ++$i;
	  }
	  } else {
	  $headers[$key] = trim($header[1]);
	}
      }
    }
  }
}

?>
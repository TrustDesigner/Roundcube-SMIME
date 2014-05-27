<?php
/**
 * Shotnget SMIME / shotnget_smime
 *
 * Main class for shotnget_smime plugin
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

require_once('shotnget_request.php');
require_once('shotnget_uncrypt.php');
require_once('shotnget_sign.php');
require_once('shotngetapi/shotnget_api.php');
require_once('shotngetapi/cerrors.php');
require_once('mail.php');

class shotnget_smime extends rcube_plugin
{
  public $task = 'mail|login|logout|settings';

  private $infos = array();

  /**
   * Initialize plugin hooks
   */
  function init()
  {
    $this->load_config('config.inc.php');

    rcmail::get_instance()->output->set_env('shotnget_sign_qrcode', rcmail::get_instance()->config->get('shotnget_sign_qrcode'));

    $this->add_texts('localization/', true);

    $this->add_hook('message_load', array($this, 'verify_mail'));
    $this->add_hook('message_before_send', array($this, 'send_mail'));
    $this->add_hook('message_compose_body', array($this, 'compose_message'));

    $this->register_action('plugin.changeSign', array($this, 'changeSign'));  
    $this->register_action('plugin.changeCrypted', array($this, 'changeCrypted'));
    $this->register_action('plugin.cancelSend', array($this, 'cancelSend'));
    $this->register_action('plugin.uncryptMailShotnget', array($this, 'uncrypt_mail_shotnget'));


    if (rcmail::get_instance()->config->get('shotnget_sign_qrcode') == false &&
	rcmail::get_instance()->task == 'settings') {
      $this->register_action('plugin.removeCertificate', array($this, 'remove_certificate'));  
      $this->register_action('plugin.detailsCertificate', array($this, 'get_certificate_details'));
    }
    if (rcmail::get_instance()->task == 'settings') {
      $this->register_action('plugin.shotnget_smime.add_certificate', array($this, 'add_certificate'));
      $this->register_action('plugin.shotnget_smime.save_certificate', array($this, 'save_certificate'));
      
      $this->include_script('shotngetapi/shotngetapi.js');
      $this->include_script('shotnget_smime_settings.js');
    }
    if (rcmail::get_instance()->task == 'login' || rcmail::get_instance()->task == 'logout') {
      $this->add_hook('authenticate', array($this, 'get_private_key_from_password'));
      $this->include_script('shotngetapi/shotngetapi.js');
      $this->include_script('client.js');
    }
  }

  /**
   * Get the hash from user to uncrypt private key with shotnget login
   * @param $args (see roundcube documentation for authenticate hook)
   * @return $args
   */
  function get_private_key_from_password($args) {
    if (isset($_POST['_rand']) === false || isset($_POST['_shotnget']) === false)
      return $args;
    $rand = $_POST['_rand'];

    $args['valid'] = false;
    $args['abort'] = true;

    if (CUtils::checkRand($rand) === false)
      return $this->display_error(array('error', 'invalid_request'), $args);
    $ret = shotnget_request::wait_response($rand);
    if ($ret['ret'] === false)
      return $this->display_error($ret, $args, $rand);

    if (($response = shotnget_request::get_response($rand)) === false)
      return $this->display_error($ret, $args, $rand);

    $request = shotnget_request::init_login_request($response, $rand);
    shotnget_request::send_request($request, $rand);

    $ret = shotnget_request::wait_response($rand);
    if ($ret['ret'] === false)
      return $this->display_error($ret, $args, $rand);

    if (($response = shotnget_request::get_response($rand)) === false)
      return $this->display_error($ret, $args, $rand);

    $args['valid'] = true;
    $args['abort'] = false;
    $authCmd = $response->getCmdByValue(CCmd::CMD_AUTH);

    //get the file (contains identification info).
    $file = $authCmd->getFile();

    //get the username returned by certphone
    $username = $file->getParamByType(CParam::TYPE_ID_UID)->getValue();
    $password = $file->getParamByType(CParam::TYPE_PWD)->getValue();
    $_SESSION['userHash'] = sha1($file->getParamByType(CParam::TYPE_WA_KEY32)->getValue());

    shotnget_request::send_request(shotnget_request::format_response_request(), $rand);

    $args['user'] = $username;
    $args['pass'] = $password;
    return $args;
  }

  /**
   * Remove the certificate from the given email address inside _POST
   */
  function remove_certificate() {
    if (isset($_POST['_email']) === false) {
      rcmail::get_instance()->output->command('display_message', $this->gettext('invalid_request'), 'error');
      return;
    }
    $email = $_POST['_email'];
    $file = shotnget_certificate::get_certificate($email);
    if (file_exists($file) == true && unlink($file) == true)
      rcmail::get_instance()->output->command('display_message', $this->gettext('remove_ok'), 'confirmation');
    else
      rcmail::get_instance()->output->command('display_message', $this->gettext('remove_failed'), 'error');
  }

  /**
   * Get the certificate details from the given email address inside _POST
   */
  function get_certificate_details() {
    $email = $_POST['_email'];
    if (isset($_POST['_email']) === false) {
      rcmail::get_instance()->output->command('display_message', $this->gettext('invalid_request'), 'error');
      return;
    }
    $file = shotnget_certificate::get_certificate($email);
    if (file_exists($file) == true && ($res = openssl_x509_parse(file_get_contents($file))) != false) {
      rcmail::get_instance()->output->show_message($this->get_html_details($res), 'notice', null, null, 20);      
    } else
      rcmail::get_instance()->output->command('display_message', $this->gettext('failed_get_cert'), 'error');
  }

  /**
   * Format the certificate details from the given certificate in html
   */
  function get_html_details($cert) {
    $out = "";
    foreach ($cert as $type => $data) {
      if (is_array($data) == true && $this->gettext($type) != "[$type]") {
	$out .= "<table><tbody><legend style='color: #00FF00;' >".$this->gettext($type)." :</legend><tr>";
	foreach ($data as $key => $value) {
	  if (is_array($value) == true)
	    $out .= "<td style='font-weight: normal; padding: 0 5px 0 5px' >$value[2]</td>";
	  else
	    $out .= "<td style='font-weight: normal; padding: 0 5px 0 5px' >$value</td>";
	}
	$out .= "</tr></tbody></table>";
      } else if ($this->gettext($type) != "[$type]") {
	if ($type == 'validFrom_time_t' || $type == 'validTo_time_t')
	  $data = date('l jS \of F Y h:i:s A', $data);
	$out .= "<table><tbody><tr><td style='color: #00FF00;' >".$this->gettext($type)." : </td><td style='font-weight: normal;'>".$data."</td></tr></tbody></table>";
      }
    }

    $out .= "<script type='text/javascript' >document.getElementsByClassName('notice')[0].style.width = 'auto';</script>";

    return $out;
  }

  /**
   * Inject html to draw the settings page
   */
  function add_certificate() {
    if (rcmail::get_instance()->config->get('shotnget_sign_qrcode') == false) {
      $this->register_handler('plugin.body', array($this, 'add_certificate_form'));
    } else {
      $this->register_handler('plugin.body', array($this, 'upload_shotnget_certificate_form'));
    }
    // This is used to affich client side the html injected by the handler
    $rcmail = rcmail::get_instance();
    $rcmail->output->set_pagetitle($this->gettext('add_certificate'));
    $rcmail->output->send('plugin');
  }

  function upload_shotnget_certificate_form() {
    $out = "<div class='iframe floatingbuttons'>";

    $out .= "<h1 class='boxtitle'>".$this->gettext('add_certificate')."</h1>";
    $out .= "<div class='boxcontent' >";

    $out .= "<form id='form_solo'  class='propform' style='padding-top: 10px;' method='post' action='./?_task=settings&_action=plugin.shotnget_smime.save_certificate' enctype='multipart/form-data' >";
    $out .= "<fieldset class='main' >";
    $out .= "<legend>".$this->gettext('upload_cert')."</legend>";
    $out .= "<table class='propform' style='margin-bottom: 10px;' ><tbody>";
    $out .= "<tr><td class='title' ><label for='solo_cert_file' >".$this->gettext('your_cert')." (.pfx / .p12)</label></td>";
    $out .= "<td><input id='solo_cert_file' type='file' name='certificate' accept='.pfx,.pem,.p12' required /></td></tr>";
    $out .= "</tbody></table>";

    $out .= "<div class='footerleft formbuttons floating' >";
    $out .= "<input type='button' class='button mainaction' value='".$this->gettext('save')."' onclick='shotnget_smime_save()' />";
    $out .= "</div>";

    $out .= "</fieldset>";
    $out .= "</form>";


    $out .= "</div>";

    $out .= "</div>";
    $out .= "<script>get_shotnget_code('plugins/shotnget_smime/get_code.php', function () {document.getElementById('shotnget_login_div').style.display = 'none';}, '".$this->gettext('falsh_to_upload_cert')."');</script>";

    $this->include_script('settings_button_callback.js');
    return $out;
  }

  /**
   * Compose the html for the settings page
   * @return $out The html for the settings page
   */
  function add_certificate_form() {
    $out = "<div class='iframe floatingbuttons'>";
    $out .= "<h1 class='boxtitle'>".$this->gettext('add_certificate')."</h1>";
    $out .= "<div class='boxcontent' >";

    /* $out .= "<div style='display: block; font-size: 14px; font-weight: bold; padding: 0 0 10px 2px;' >".$this->gettext('how_upload')."</div>"; */
    /* $out .= "<table class='propform' ><tbody>"; */
    /* $out .= "<tr><td class='title' ><label for='solo_radio' >".$this->gettext('key_and_cert_solo_file')."</label></td>"; */
    /* $out .= "<td><input type='radio' id='solo_radio' name='type' checked='true' onchange='document.getElementById(\"form_solo\").style.display = \"block\"; document.getElementById(\"form_multi\").style.display = \"none\";' /></td></tr>"; */
    /* $out .= "<tr><td class='title' ><label for='multi_radio' >".$this->gettext('key_and_cert_separated_files')."</label></td>"; */
    /* $out .= "<td><input type='radio' id='multi_radio' name='type' onchange='document.getElementById(\"form_solo\").style.display = \"none\"; document.getElementById(\"form_multi\").style.display = \"block\";' /></td></tr>"; */
    /* $out .= "</tbody></table>"; */

    $out .= "<form id='form_solo'  class='propform' style='padding-top: 10px;' method='post' action='./?_task=settings&_action=plugin.shotnget_smime.save_certificate' enctype='multipart/form-data' >";
    $out .= "<fieldset class='main' >";
    $out .= "<legend>".$this->gettext('upload_cert')."</legend>";
    $out .= "<table class='propform' style='margin-bottom: 10px;' ><tbody>";
    $out .= "<tr><td class='title' ><label for='solo_cert_file' >".$this->gettext('your_cert')." (.pfx / .p12)</label></td>";
    $out .= "<td><input id='solo_cert_file' type='file' name='certificate' accept='.pfx,.pem,.p12' required /></td></tr>";
    $out .= "<tr><td class='title' ><label for='solo_pwd'>".$this->gettext('pwd_private_key')."</label></td>";
    $out .= "<td><input id='solo_pwd' type='password' name='pass_key' /></td></tr>";
    $out .= "</tbody></table>";

    $out .= "<div class='footerleft formbuttons floating' >";
    $out .= "<input type='submit' class='button mainaction' value='".$this->gettext('save')."' />";
    $out .= "</div>";

    $out .= "</fieldset>";
    $out .= "</form>";

    $out .= "<form id='form_multi' class='propform' style='padding-top: 10px;display: none;' method='post' action='./?_task=settings&_action=plugin.shotnget_smime.save_certificate' enctype='multipart/form-data' >";
    $out .= "<fieldset class='main' >";
    $out .= "<legend>".$this->gettext('upload_cert_and_key')."</legend>";
    $out .= "<table class='propform' style='margin-bottom: 10px;' ><tbody>";
    $out .= "<tr><td class='title' ><label for='duo_cert_file' >".$this->gettext('your_cert')." (.pem)</label></td>";
    $out .= "<td><input id='duo_cert_file' type='file' name='certificate' accept='.pem' required /></td></tr>";
    $out .= "<tr><td class='title' ><label for='duo_key_file' >".$this->gettext('your_private_key')." (.key / .pem)</label></td>";
    $out .= "<td><input id='duo_key_file' type='file' name='private_key' accept='.pem,.key' required /></td></tr>";
    $out .= "<tr><td class='title' ><label for='multi_pwd'>".$this->gettext('pwd_private_key')."</label></td>";
    $out .= "<td><input id='multi_pwd' type='password' name='pass_key' /></td></tr>";
    $out .= "</tbody></table>";

    $out .= "<div class='footerleft formbuttons floating' >";
    $out .= "<input type='submit' class='button mainaction' value='".$this->gettext('save')."' />";
    $out .= "</div></fieldset></form>";


    $out .= "<div style='display: block; font-size: 14px; font-weight: bold; padding: 0 0 10px 2px;' >".$this->gettext('cert_upload')."</div>";
    $out .= "<table class='propform' ><tbody>";

    $user = rcmail::get_instance()->user;
    foreach ($user->list_identities() as $identity) {
      if (file_exists(shotnget_certificate::get_certificate($identity['email'])) != false) {
	$out .= "<tr>";
	$out .= "<td>".$this->gettext('your_mail_address')." : ".$identity['email']."</td>";
	$out .= "<td style='width: 10%;'>"."<input type='button' style='cursor: default;' class='button mainaction' onclick='shotnget_smime_details(\"".$identity['email']."\")' value='Details' />"."</td>";
	$out .= "<td>"."<input type='button' value='".$this->gettext('remove')."' class='button mainaction' onclick='shotnget_smime_remove(\"".$identity['email']."\");' />"."</td>";
	$out .= "</tr>";
      }
    }

    $out .= "</tbody></table>";
    $out .= "</div></div>";

    $this->include_script('settings_button_callback.js');
    return $out;
  }
  
  /**
   * Save certificate wich was upload by the settings page
   */
  function save_certificate() {
    $rcmail = rcmail::get_instance();
    if ($rcmail->config->get('shotnget_sign_qrcode') === true) {

      $rand = $_POST['_rand'];
      $filename = Config::$TEMP_PATH.$rand;
      file_put_contents($filename.'.rand', "");
      if (CUtils::checkRand($rand) == false || !isset($_FILES['certificate']))
	die();
      $ret = shotnget_request::wait_response($rand);
      if ($ret['ret'] === false) {
	$this->display_error($ret, null, $rand);
	$this->add_certificate();
	return;
      }
      if (($response = shotnget_request::get_response($rand)) === false) {
	$this->display_error(null, null, $rand);
	$this->add_certificate();
	return;
      }

      $data = file_get_contents($_FILES['certificate']['tmp_name']);
      $request = shotnget_request::init_upload_cert_request($response, $rand, base64_encode($data));
      shotnget_request::send_request($request, $rand);

      $ret = shotnget_request::wait_response($rand);
      if ($ret['ret'] === false) {
	$this->display_error($ret, null, $rand);
	$this->add_certificate();
	return;
      }
      if (($response = shotnget_request::get_response($rand)) === false) {
	$this->display_error($ret, null, $rand);
	$this->add_certificate();
	return;
      }

      shotnget_request::send_request(shotnget_request::format_response_request(), $rand);
      $err = true;
    } else {
      if ($_FILES['private_key']['tmp_name'] == "")
	$err = $this->gettext('no_file');
      if (isset($_SESSION['userHash']) === false)
	$err = $this->gettext('connect_with_shotnget');
      // Save certificate and private key form uploaded files
      if (isset($err) === false) {
	if (isset($_FILES['private_key']))
	  $err = $this->save_separated_key_and_certificate(file_get_contents($_FILES['certificate']['tmp_name']), file_get_contents($_FILES['private_key']['tmp_name']), $_POST['pass_key']);
	else
	  $err = $this->save_p12_format(file_get_contents($_FILES['certificate']['tmp_name']), $_POST['pass_key']);
      }
    }
    if ($err !== true) {
      $rcmail->output->command('display_message', $this->gettext('error').": ".$err, 'error');
    } else
      $rcmail->output->command('display_message', $this->gettext('validation_cert'), 'confirmation');
    $this->add_certificate();
  }

  /**
   * Save p12 / pfx certificates.
   * Detach the key and the certificate from the given data
   * @param $certificate Content of uploaded certificate
   * @param $private_key_password Password for the private key
   * @return true / false depending if it works
   */
  function save_p12_format($certificate, $private_key_password) {
    if (openssl_pkcs12_read($certificate, $out, $private_key_password) === false)
      return $this->gettext('error_pkcs_read');
    if (openssl_x509_check_private_key($out['cert'], array($out['pkey'], $private_key_password)) === false)
      return $this->gettext('error_check_private_key');
    if (($mail_address = $this->verify_mail_address_in_certificate($out['cert'])) == false)
      return $this->gettext('error_verify_mail_address');
    $extracerts = "";
    foreach ($out['extracerts'] as $extracert)
      $extracerts .= $extracert;
    return $this->format_and_save_certificate($out['cert'], $out['pkey'], $private_key_password, $mail_address, $extracerts);
  }

  /**
   * Save others formats
   * @param $certificate Content of the uploaded certificate
   * @param $private_key Content of the uploaded private key
   * @param $private_key_password Password for the private key
   * @return true / false depending if it works
   */
  function save_separated_key_and_certificate($certificate, $private_key, $private_key_password) {
    if (openssl_x509_check_private_key($certificate, array($private_key, $private_key_password)) === false)
      return $this->gettext('error_check_private_key');
    if (($mail_address = $this->verify_mail_address_in_certificate($certificate)) == false)
      return $this->gettext('error_verify_mail_address');
    return $this->format_and_save_certificate($certificate, $private_key, $private_key_password, $mail_address);
  }

  /**
   * Save certificate and crypted key with hash of user password in files
   * @param $certificate Content of the uploaded certificate
   * @param $private_key Content of the uploaded private key
   * @param $private_key_password Password for the private key
   * @param $mail_address adress mail of the user
   * @return true / false depending if it works
   */
  function format_and_save_certificate($certificate, $private_key, $private_key_password, $mail_address, $extracert = "") {
    $rcmail = rcmail::get_instance();
    $cert_sha1 = sha1($certificate);
    if (($raw_pkey = openssl_pkey_get_private($private_key, $private_key_password)) === false)
      return $this->gettext('error_decrypt_private_key');
    if (openssl_pkey_export($raw_pkey, $pkey_new_pwd, $_SESSION['userHash']) === false)
      return $this->gettext('error_encrypt_private_key');
    if (file_put_contents($rcmail->config->get('keys_path').$mail_address."_".$cert_sha1, $pkey_new_pwd) === false)
      return $this->gettext('error_save_private_key');
    if (file_put_contents($rcmail->config->get('certificate_path').$mail_address.".pem", $certificate) === false)
      return $this->gettext('error_save_certificate');
    if (file_put_contents($rcmail->config->get('certificate_path').$mail_address."_extra.pem", $extracert) === false)
      return $this->gettext('error_save_certificate');
    return true;
  }

  /**
   * Check if mail address inside certificte is inside user indentities
   * @param $certificate Content of the uploaded certificate
   * @return true / false depending if yes or no
   */
  function verify_mail_address_in_certificate($certificate) {
    $rcmail = rcmail::get_instance(); 
    $deserialized = openssl_x509_parse($certificate);
    $mailAdress = $deserialized['subject']['emailAddress'];
    $user = $rcmail->user;
    foreach ($user->list_identities() as $identity) {
      if ($identity['email'] == $mailAdress)
	return $mailAdress;
    }
    return false;
  }

  /**
   * Initialize signed and crypted to false and add js script to put hooks on
   * signed / crypted buttton un the mail composition vue
   * @param $args see message_compose_body hook from roundcube
   * @return $args
   */
  function compose_message($args) {
    $_SESSION['shotnget_mail_is_signed'] = false;
    $_SESSION['shotnget_mail_is_crypted'] = false;
    $this->include_script('client.js');
    $this->include_script('shotngetapi/shotngetapi.js');
    return $args;
  }

  /**
   * Callback function when the user click on signed button in compose message page
   */
  function changeSign() {
    $checked = $_POST['_checked'];
    $_SESSION['shotnget_mail_is_signed'] = $checked == "true" ? true : false;
    if ($_SESSION['shotnget_mail_is_signed'] == true)
      $_SESSION['shotnget_rand'] =  $_POST['_rand'];
    else if (isset($_POST['_rand']))
      unset($_POST['_rand']);

    $rcmail = rcmail::get_instance();
    $rcmail->output->command('plugin.changeCallback', array('state' => $_SESSION['shotnget_mail_is_signed'], 'checked' => $checked, 'type' => 'signature', 'rand' => $_POST['_rand']));
  }

  /**
   * Callback function when the user click on crypted button in compose message page
   */
  function changeCrypted() {
    $checked = $_POST['_checked'];
    $_SESSION['shotnget_mail_is_crypted'] = $checked == "true" ? true : false;
    $rcmail = rcmail::get_instance();
    $rcmail->output->command('plugin.changeCallback', array('state' => $_SESSION['shotnget_mail_is_crypted'], 'checked' => $checked, 'type' => 'chiffrement'));
  }

  /**
   * Callback function when the user cancel the send of the message by clicking outside of the qrcode
   */
  function cancelSend() {
    $rand = $_POST['_rand'];
    shotnget_request::cancel_action($rand);
    $rcmail = rcmail::get_instance();
    $rcmail->output->command('plugin.changeCallback', array('canceled' => true));
  }

  /**
   * This function is called when the user click on the send button in compose page
   * Treat the mail if signed and/or crypted options are selected
   * @param $args see message_before_send hook from roundcube
   * @return $args
   */
  function send_mail($args) {
    $signed = $_SESSION['shotnget_mail_is_signed'];
    $crypted = $_SESSION['shotnget_mail_is_crypted'];

    if ($signed == false && $crypted == false)
      return $args;

    $myMail = explode(" ", str_replace(array("<", ">"), array("", ""), $args['from']));
    $myMail = $myMail[count($myMail) - 1];
    
    $args['message']->_build_params['text_encoding'] = 'quoted-printable';
    $data = $args['message']->getMessage();

    if ($signed == true) {
      $shotnget_sign = new shotnget_sign($data, $myMail, isset($_SESSION['shotnget_rand']) ? $_SESSION['shotnget_rand'] : null);
      if (rcmail::get_instance()->config->get('shotnget_sign_qrcode') == true)
	$ret = $shotnget_sign->perform_signature();
      else
	$ret = $shotnget_sign->perform_basic_signature();
      if ($ret['ret'] == false) {
        $shotnget_sign->close_connexion();
        return $this->display_error($ret, $args, $rand);
      }
      $data = $ret['data'];
    }

    if ($crypted === true) {
      $mail = new rcube_mime();
      $mailto = $mail->decode_address_list($args['mailto']);

      $mailList = array();
      foreach ($mailto as $mailDesc) {
	if (!in_array($mailDesc['mailto'], $mailList))
	  $mailList[] = $mailDesc['mailto'];
      }

      if (!in_array($myMail, $mailList))
	$mailList[] = $myMail;

      $shotnget_uncrypt = new shotnget_uncrypt($data, $myMail, $mailList);
      $ret = $shotnget_uncrypt->encrypt_mail();
      if ($ret['ret'] == false)
	return $this->display_error($ret, $args, $rand);
      $data = $ret['data'];
    }

    if ($crypted === true || $signed === true) {
      // Parse message to get all elements
      $mail = new mail($data, true);
      $parts = explode("\n\n", $data, 2);
      $args['message']->_parts = array();
      $args['message']->_txtbody = $parts[1];
      $args['message']->_htmlbody = "";
      $args['message']->_build_params['boundary'] = $mail->subHeaders['boundary'];
      $args['message']->_build_params['ctype'] =  $mail->subHeadersOf['Content-Type'];
      $args['message']->_build_params['text_encoding'] = '7bits';
      $args['message']->_headers['Content-Transfer-Encoding'] = '8bits';
    }
    if (isset($_SESSION['shotnget_rand']))
      $this->cleanTempFiles($_SESSION['shotnget_rand']);
    return $args;
  }

  /**
   * This function return the user address mail which is located in the 'To' header of the message
   * @param $mailto Header string which contain all the people for whom the message has been sent
   * @return $mailto
   */
  function get_email($mailto) {
    $mailto = explode(",", $mailto);
    if (!is_array($mailto))
      $mailto = array($mailto);
    $emailsAddress = array();
    foreach ($mailto as $emailAddress) {
      $emailAddress = explode(" ", str_replace(array("<", ">"), array("", ""), trim($emailAddress)));
      $emailAddress = $emailAddress[count($emailAddress) - 1];
      if (!in_array($emailAddress, $emailsAddress))
	$emailsAddress[] = $emailAddress;
    }
    $mailto = $emailsAddress;

    $identities = rcmail::get_instance()->user->list_identities();
    foreach ($identities as $identity) {
      foreach ($mailto as $emailAddress) {
	if ($emailAddress == $identity['email']) {
	  $ret = $emailAddress;
 	  return $ret;
	}
      }
    }
    return null;
  }

  /**
   * Function called to save the key to uncrypt a message with shotnget
   * All keys will be saved inside an array with the id of the mail
   */
  function uncrypt_mail_shotnget() {
    if (!isset($_POST['_rand']))
      return $this->display_error($this->gettext('error').' : '.$this->gettext('error_uncrypt'), $args);

    $rand = $_POST['_rand'];
    $_SESSION['shotnget_rand'] = $rand;
    $mailId = $_SESSION['uncryptParamsShotnget']['uid'];
    $mail = rcube::get_instance()->storage->get_raw_body($mailId);
    $userMail = $_SESSION['uncryptParamsShotnget']['email'];
    $mailTo = $_SESSION['uncryptParamsShotnget']['mailTo'];
    $isSigned = $_SESSION['uncryptParamsShotnget']['signed'] === true ? true : false;

    $shotnget_uncrypt = new shotnget_uncrypt($mail, $userMail, $mailTo, $isSigned, $rand);
    $ret = $shotnget_uncrypt->uncrypt_mail();
    if ($ret['ret'] === false) {
      return $this->display_error($ret, null, $rand);
    }

    $cert = $ret['data']['cert'];
    $hash = sha1($cert);
    if (!isset($_SESSION['userDecryptCert'][$hash]))
      $_SESSION['userDecryptCert'][$hash] = $cert;

    $_SESSION['knownUncryptData'][$mailId]['key'] = $ret['data']['key'];
    $_SESSION['knownUncryptData'][$mailId]['cert'] = $hash;

    $this->cleanTempFiles($rand);
    return $this->display_error(null);
  }

  /**
   * This function is called when the user see a mail (Send mail or received mail)
   * If the message is signed or crypted it will decode it and verify it signature
   * @param $args see message_load hook from roundcube
   * @return $args
   */
  function verify_mail($args) {
    $rcmail = rcube::get_instance();
    $isCrypted = false;

    // Get mail form storage
    $mail = rcube::get_instance()->storage->get_raw_body($args['object']->uid);

    $ctype = $args['object']->headers->ctype;

    $mime = new mail($mail);

    if (count($args['object']->attachments) != 0 && $args['object']->attachments[0]->filename == "")
      $args['object']->attachments[0]->filename = $mime->subHeaders['filename'];

    if ($mime->subHeaders['smime-type'] == 'signed-data') {
      $ctype = "application/x-pkcs7-signature";
    }

    // If message isn't signed and/or encrypted
    if ($ctype != "application/x-pkcs7-mime" &&
	$ctype != "multipart/signed" &&
	$ctype != "application/x-pkcs7-signature")
      return $args;

    $res = null;
    
    if (($myMail =  $this->get_email($mime->headers['From'])) == null) {
      $mailto = $this->get_email($mime->headers['To']);
      $myMail = explode(" ", str_replace(array("<", ">"), array("", ""), $mime->headers['From']));
      $myMail = $myMail[count($myMail) - 1];
    } else {
      $mailto = explode(" ", str_replace(array("<", ">"), array("", ""), $mime->headers['To']));
      $mailto = $mailto[count($mailto) - 1];
    }

    // Create tmp file where mail content will be stored
    $message = tempnam("", "crypted");
    file_put_contents($message, $mail);

    // If message crypted
    if ($ctype == "application/x-pkcs7-mime") {
      $res = null;
      if (rcmail::get_instance()->config->get('shotnget_sign_qrcode') == false) { // Shotnget desactivated
	$res = $this->uncrypt_mail($message, $mailto, $isCrypted, $myMail);
      } else if (isset($_SESSION['knownUncryptData'][$args['object']->uid])) { // shotnget activated and already decrypt
	$isCrypted = true;
	$data = $_SESSION['knownUncryptData'][$args['object']->uid];
	$shotnget_uncrypt = new shotnget_uncrypt($mail, $myMail, $mailto);
	$certificate = $_SESSION['userDecryptCert'][$data['cert']];
	$ret = $shotnget_uncrypt->uncrypt_with_key($certificate, $data['key']);
	if ($ret['ret'] !== false) {
	  $res = $ret['data'];
	  $mime = new rcube_mime();
	  $deserialize = $mime->parse_headers($res);
	  $deserialize['content-type'] = explode(";", $deserialize['content-type'])[0];
	  if ($deserialize['content-type'] == "multipart/signed" ||
	      $deserialize['content-type'] == "application/x-pkcs7-signature") {
	    file_put_contents($message, $res);
	    $res = $this->check_sign($message, $mailTo, $isCrypted, $myMail);
	  }
	  // This line is to remove the uncrypt key from data
	  //unset($_SESSION['knownUncryptData'][$args['object']->uid]);
	  $msg = $this->gettext('crypted_message');
	} else {
	  $msg = $this->gettext('error_uncrypt');
	  $res = false;
	}
	if (count($this->infos) == 0)
          $this->add_mail_info($msg);
        else {
          $this->infos[0]->body .= "<br />".$msg;
          $this->infos[0]->size = strlen($this->infos[0]->body);
        }
      } else { // shotnget activated and to decrypt
	$_SESSION['uncryptParamsShotnget'] = array('uid' => $args['object']->uid, 'email' => $myMail);
	$this->include_script('inbox.js');
	$this->include_script('shotngetapi/shotngetapi.js');
	$res = null;
      }
    }
    // If messages signed
    else {
      $res = $this->check_sign($message, $mailto, $isCrypted, $myMail);
      if ($isCrypted == true && rcmail::get_instance()->config->get('shotnget_sign_qrcode') == false) {
	$_SESSION['uncryptParamsShotnget'] = array('uid' => $args['object']->uid, 'email' => $myMail, 'signed' => true, 'mailTo' => $mailTo);
      	$this->include_script('inbox.js');
	$this->include_script('shotngetapi/shotngetapi.js');
        $res = null;
      }
    }
    // Deserialize result
    if ($res != null && $res != false) {
      $mime = new rcube_mime();
      $deserialize = $mime->parse_message($res);

      if (count($deserialize->parts) != 0) {
	$this->add_parts($args, $deserialize, $this->has_html($deserialize), $isCrypted);
      }
      if ($isCrypted == true && $deserialize->body != "") {
      	$this->add_mail_info($deserialize->body, true, $args);
      }
    }
    if ($res !== null)
      $this->push_infos($args, $res === false ? true : false);
    return $args;
  }

  /**
   * This function is used to uncrypt a crypted mail
   * @param $message File containing the crypted mail
   * @param $mailTo Mail adress of the sender
   * @param encrypt Boolean : tru if the message is crypted / false otherwise
   * @param $myMail Mail adress of the user
   * @return false if failed / Content of the uncrypted message otherwise
   */
  function uncrypt_mail($message, $mailTo, &$encrypt, $myMail) {
    // Create tmp file where uncrypted message will be stored
    $encrypt = true;
    if (rcmail::get_instance()->config->get('shotnget_sign_qrcode') == true)
      return false;
    $shotnget_uncrypt = new shotnget_uncrypt(file_get_contents($message),
					     $myMail, $mailTo, false, null, $_SESSION['userHash']);
    $ret = $shotnget_uncrypt->uncrypt_basic_mail();
    if ($ret['ret'] === true) {
      if (count($this->infos) == 0)
	$this->add_mail_info($this->gettext('crypted_message'));
      else {
	$this->infos[0]->body .= "<br />".$this->gettext('crypted_message');
	$this->infos[0]->size = strlen($this->infos[0]->body);
      }
      file_put_contents($message, $ret['data']);

      $mime = new rcube_mime();
      $deserialize = $mime->parse_headers($ret['data']);
      $deserialize['content-type'] = explode(";", $deserialize['content-type'])[0];

      // If crypted
      if ($deserialize['content-type'] == "application/x-pkcs7-mime") {
        return $this->uncrypt_mail($message, $mailTo, $encrypt, $myMail);
      }
      // If messages signed
      else if ($deserialize['content-type'] == "multipart/signed" ||
	       $deserialize['content-type'] == "application/x-pkcs7-signature") {
        return $this->check_sign($message, $mailTo, $encrypt, $myMail);
      }
      @unlink($message);
      return $ret['data'];
    }

    $this->add_mail_info($this->gettext('error_uncrypt').$ret['error'].$ret['email']);
    return false;
  }

  /**
   * This function is used to verify a signed mail
   * @param $message File containing the crypted mail
   * @param $mailTo Mail adress of the sender
   * @param encrypt Boolean : tru if the message is crypted / false otherwise
   * @param $sender Mail adress of the sender
   * @return false if failed / Content of the signed message otherwise
   */
  function check_sign($message, $mailTo, &$encrypt, $sender) {
    $shotnget_sign = new shotnget_sign(file_get_contents($message), $sender, null, $mailTo);
    $ret = $shotnget_sign->verify_signature();
    if ($ret['ret'] === true) {
      file_put_contents($message, $ret['data']);
      if (count($this->infos) == 0)
	$this->add_mail_info($ret['info']);
      else {
	$this->infos[0]->body .= "<br />".$ret['info'];
	$this->infos[0]->size = strlen($this->infos[0]->body);
      }

      $mime = new rcube_mime();
      $deserialize = $mime->parse_headers($ret['data']);
      $deserialize['content-type'] = explode(";", $deserialize['content-type'])[0];

      // If crypted
      if ($deserialize['content-type'] == "application/x-pkcs7-mime") {
	return $this->uncrypt_mail($message, $mailTo, $encrypt, $sender);
      }
      // If messages signed
      else if ($deserialize['content-type'] == "multipart/signed" ||
	       $deserialize['content-type'] == "application/x-pkcs7-signature") {
	return $this->check_sign($message, $mailTo, $encrypt, $sender);
      }

      @unlink($message);
      return $ret['data'];
    }
    $this->add_mail_info($this->gettext('error_verify_sign').$ret['error'].$ret['openssl_error']);
    return false;
  }

  /**
   * Function to check if the given mail contains html
   * @param $res Roundcube object containing the mail
   * @return true / false
   */
  function has_html($res) {
    $hasHtml = false;
    foreach ($res->parts as $val) {
      if ($val->mimetype == 'text/html')
	$hasHtml = true;
      if (count($val->parts) != 0) {
	$hasHtml = $hasHtml == true ? $hasHtml : $this->has_html($val);
      }
    }
    return $hasHtml;
  }

  /**
   * Function to add parts to the mail in roundcube object
   * @param $args Roundcube object containing the mail
   * @param $res Object containing the parts to add
   * @param $hasHtml Boolean : true if the mail contains html / false otherwise
   * @param $isCrypted Boolean : true if the mail is crypted / false otherwise
   */
  function add_parts($args, $res, $hasHtml, $isCrypted) {
    foreach ($res->parts as $val) {
      if ($val->disposition == 'attachment') {
	if ($isCrypted == true)
	  $args['object']->attachments[] = $val;
      }
      else if (count($val->parts) != 0) {
	$this->add_parts($args, $val, $hasHtml, $isCrypted);
      }
      else {
	$val->type = 'content';
	if ($hasHtml == true) {
	  $val->mime_id = count($args['object']->mime_parts) + 1;
	  $args['object']->mime_parts[] = $val;
	}
	if ($isCrypted == true) {
	  if ($hasHtml == false || $val->mimetype == 'text/html') {
	    $val->mime_id = count($args['object']->parts) + 1;
	    $args['object']->parts[] = $val;
	  }
	}
      }
    }
  }

  /**
   * Add message in a new part of the mail
   * @param $msg Message to add to the list or Roundcube object
   * @param $directAdd Boolean : true if add directly to Roundcube object / false
   * to add in a list to add to the Roundcube object later
   * @param $args Roundcube object containing the mail (Only needed if param iss equal to true)
   */
  function add_mail_info($msg, $directAdd = false, $args = null) {
    $message = new rcube_message_part();
    $message->body = $msg;
    $message->charset = "UTF-8";
    $message->type = "content";
    $message->mimetype = 'text/html';
    $message->ctype_primary = 'text';
    $message->ctype_secondary = 'html';
    $message->size = strlen($message->body);
    if ($directAdd == false)
      $this->infos[] = $message;
    else {
      $message->mime_id = count($args['object']->parts) + 1;
      $args['object']->parts[] = $message;
    }
  }

  /**
   * Adds the informations to the mail in html format
   * The informations are the error messages and the details of the signer
   * @param $args Roundcube object containing the mail
   */
  function aff_signature_in_html_page($args) {
    libxml_use_internal_errors(true);
    $document = new DOMDocument("1.0", "UTF-8");
    $document->loadHTML(mb_convert_encoding($args['content'], 'HTML-ENTITIES', 'UTF-8'));
    $header = $document->getElementById('messageheader');

    if ($header != null) {
      foreach ($this->infos as $info) {
	$node = $document->createDocumentFragment();
	$node->appendXml($info->body);
	$header->appendChild($node);
      }
    }

    if (($author = $document->getElementById('shotnget_sign_author'))) {
      $authorLink = $document->createElement('a', $author->nodeValue);
      $authorLink->setAttribute('href', 'mailto:'.$author->nodeValue);
      $authorLink->setAttribute('onclick', "return rcmail.command('compose','".$author->nodeValue."',this)");
      $author->parentNode->replaceChild($authorLink, $author);
    }

    $args['content'] = $document->saveHtml();
    return $args;
  }

  /**
   * Treat all parts saved in infos to add it in Roundcube object
   * @param $args Roundcube object containing the mail
   */
  function push_infos($args, $errors) {
    foreach ($this->infos as $val) {
      $val->mime_id = count($args['object']->parts) + 1;
      if ($errors === true)
	$val->body = "<div style=\"color: #FF0000; font-weight: bold;\">".$val->body."</div>";
      else
	$val->body = "<div style=\"color: #009300; font-weight: bold;\">".$val->body."</div>";
      $args['object']->parts[] = $val;
    }
    if (count($this->infos) != 0)
      $this->add_hook('render_page', array($this, 'aff_signature_in_html_page'));
  }

  /**
   * Print error client side and abort action with setting result to false
   * @param $msg Error message to display
   */
  function display_error($msg, $args = null, $rand = null) {
    if ($msg != null) {
      if (is_array($msg)) {
	$error = $this->gettext($msg['error']);
	if (isset($msg['email']))
	  $error .= $msg['email'];
	if (isset($msg['openssl_error']))
	  $error .= $msg['openssl_error'];
	rcmail::get_instance()->output->command('display_message', $error, 'error');
	rcmail::get_instance()->output->command('plugin.shotnget_smime.hideQrcode', array('error', $error));
      } else {
	rcmail::get_instance()->output->command('display_message', $msg, 'error');
	rcmail::get_instance()->output->command('plugin.shotnget_smime.hideQrcode', array('error', $msg));
      }
    } else
      	rcmail::get_instance()->output->command('plugin.shotnget_smime.hideQrcode', array('error', $msg));
    if ($rand != null)
      shotnget_request::send_request(shotnget_request::format_response_request(), $rand);
    if (isset($_SESSION['shotnget_rand'])) {
      $this->cleanTempFiles($_SESSION['shotnget_rand']);
    }
    $args['abort'] = true;
    $args['result'] = false;
    return $args;
  }

  /**
   * This function remove the tmp files used by the api
   * @param $rand The rand used for the transfert
   */
  function cleanTempFiles($rand) {
    $filename = Config::$TEMP_PATH.$rand;
    if (file_exists($filename.'.rep'))
      unlink($filename.'.rep'); //supprime le fichier .rep

    if (file_exists($filename.'.temp'))
      unlink($filename.'.temp'); //supprime le fichier .temp

    if (file_exists($filename.'.cmds'))
      unlink($filename.'.cmds'); //supprime le fichier .cmds

    if (file_exists($filename.'.data'))
      unlink($filename.'.data'); //supprime le fichier .data
  }

}

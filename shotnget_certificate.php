<?php
/**
 * Shotnget SMIME / shotnget_certificate
 *
 * Class used to perform actions on certificates
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

class shotnget_certificate {

  /**
   * Format a certificate from the DER to PEM format
   * @param $cert_content The certificate content in DER format
   * @return The PEM format (base64 + BEGIN / END CERTIFICATE)
   */
  public static function format_certificate($cert_content) {
    return "-----BEGIN CERTIFICATE-----\n"
      .chunk_split(base64_encode($cert_content), 64, "\n")
      ."-----END CERTIFICATE-----";
  }

  /**
   * This function check if the certificate can be used to be signed and if the user mail address
   * match the certificate mail address
   * @param $cert_content The contents of the certificate in base64 (pem format)
   * @param $mail The address mail of the user
   * @return $res true / false with error message
   */
  public static function verify_certificate_with_mail($cert_content, $mail, $purpose = X509_PURPOSE_SMIME_SIGN) {
    $data = openssl_x509_parse($cert_content);
    $cert_mail = $data['subject']['emailAddress'];

    if ($purpose !== false) {
      if (openssl_x509_checkpurpose($data, X509_PURPOSE_SMIME_SIGN) === false)
        return array('ret' => false, 'error' => 'error_purpose_sign_use');
    }
    if ($cert_mail !== $mail)
      return array('ret' => false, 'error' => 'error_verify_mail_address');
    return array('ret' => true);
  }

  /**
   * Function to get the path to the certificate of a given user
   * @param $address Mail adress of a user
   * @param $has_prepend add file:// at the begining of the path
   * @return The path to the certificate of the given user
   */
  public static function get_certificate($address, $has_prepend = false) {
    $folder = rcmail::get_instance()->config->get('certificate_path');
    $prepend = "file://";
    return ($has_prepend == true ? $prepend : "").$folder.$address.".pem";
  }

  /**
   * Function to get the path to the private key of a given user
   * @param $address Mail adress of a user
   * @param $has_prepend add file:// at the begining of the path
   * @return The path to the private key of the given user
   */
  public static function get_private_key($address, $certificate_content, $has_prepend = false) {
    $folder = rcmail::get_instance()->config->get('keys_path');
    $prepend = "file://";
    return ($has_prepend == true ? $prepend : "").$folder.$address."_".sha1($certificate_content);
  }

  /**
   * Function to get the path to the extra certificate of a given user
   * @param $address Mail adress of a user
   * @param $has_prepend add file:// at the begining of the path
   * @return The path to the certificate of the given user
   */
  public static function get_extra_certificate($address, $has_prepend = false) {
    $folder = rcmail::get_instance()->config->get('certificate_path');
    $prepend = "file://";
    return ($has_prepend == true ? $prepend : "").$folder.$address."_extra.pem";
  }

  /**
   * Save the cencrypt certificate from the given message
   * @param $message The file containing the message
   * @param $mail The user email
   * @return All the certificates from the message in an array
   */
  public static function save_cert($message, $mail) {
    $certs = tempnam("", "certs");

    exec("cat ".$message." | openssl smime -pk7out | openssl pkcs7 -print_certs > ".$certs);
    $data = file_get_contents($certs);
    @unlink($certs);

    $certs_content = explode("\n\n", $data);
    foreach ($certs_content as $key => $cert_content) {
      $data = substr($cert_content, strpos($cert_content, "\n") + 1);
      $data = substr($data, strpos($data, "\n") + 1);
      $certs_content[$key] = $data;
    }

    foreach ($certs_content as $cert_content) {
      if (openssl_x509_checkpurpose($cert_content, X509_PURPOSE_SMIME_ENCRYPT) == true) {
        $deserialized = openssl_x509_parse($cert_content);
        if ($deserialized['sbject']['emailAddress'] == $mail) {
          file_put_contents(shotnget_certificate::get_certificate($mail), $cert_content);
          break;
        }
      }
    }
    return $certs_content;
  }

};

?>
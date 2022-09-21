<?php
/*
UserSpice 5
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted 
require_once dirname(__DIR__).'/vendor/autoload.php';

use Kissdigitalcom\AppleSignIn\ClientSecret;

// make sure that settings are available
if(!isset($settings)){
	$settings = $db->query('SELECT * FROM settings')->first();
}

// fallback for lang strings
if(!isset($lang['JOIN_APPLE'])){
  $lang = array_merge($lang, [
    "JOIN_APPLE"                   =>"Sign In with Apple",
    "JOIN_APPLE_ABORTED"           =>"Sign In with Apple aborted",
    "JOIN_APPLE_AUTH_ERROR"        =>"Authentication failed, Please try again. If keeps happening try cleaning your browser cache.",
    "JOIN_APPLE_EMAIL_NOT_VERIFIED"=>"Registration failed because your email address has not been verified by Apple yet",
    "JOIN_APPLE_PRIVATE_EMAIL"     =>"Hiding your email address prevents registration from being completed. If you want to fix this, please go to appleid.apple.com, delete the settings for this site and then you can try registering again.",
    "JOIN_APPLE_TOKEN_ERROR"       =>"Error getting Apple an access token"
  ]);

}

/**
 * Apple Sign in controller
 * 
 * For more information please visit: 
 * https://developer.apple.com/documentation/sign_in_with_apple
 * 
 */
class APPLE {

  /**
   * returns apple authorize URL
   * 
   * @return string $url
   */
  public static function getAuthURL(){
    GLOBAL $settings;

    return 'https://appleid.apple.com/auth/authorize'.'?'.http_build_query([
      'response_type' => 'code',
      'response_mode' => 'form_post',
      'client_id' => $settings->apple_clientId,
      'redirect_uri' => $settings->apple_callback,
      'state' => self::getSessionKey(),
      'scope' => 'name email',
	  ]);

  }
  /**
   * returns generated apple secret
   * 
   * @return string $secret
   */
  public static function getSecret(){
    GLOBAL $settings;

    if(!isset($_SESSION)) session_start();

    if(isset($_SESSION['apple_secret'])){
      return $_SESSION['apple_secret'];
    }

    $generator = new ClientSecret(
      $settings->apple_clientId, 
      $settings->apple_teamId, 
      $settings->apple_keyId, 
      $settings->apple_certPath
    );

    return $_SESSION['apple_secret'] = $generator->generate();
  }
  /**
   * returns generated apple session key
   * 
   * @param bool $forceNewKey overrides old session key
   * 
   * @return string $key
   */
  public static function getSessionKey($forceNewKey=false){
    if(!isset($_SESSION)) session_start();

    if(!isset($_SESSION['apple_sess']) || $forceNewKey) {
      $_SESSION['apple_sess'] = bin2hex(random_bytes(5));
    }

    return $_SESSION['apple_sess'];
  }
  /**
   * returns generated apple access token
   * 
   * Token endpoint docs: 
   * https://developer.apple.com/documentation/signinwithapplerestapi/generate_and_validate_tokens
   * 
   * @param string $appleAuthCode
   * 
   * @return object $tokenResponse
   */
  public static function getAccessToken(string $appleAuthCode){
    global $settings;

    // request a token from apple
    $response = self::http('https://appleid.apple.com/auth/token', [
      'grant_type' => 'authorization_code',
      'code' => $appleAuthCode,
      'redirect_uri' => $settings->apple_callback,
      'client_id' => $settings->apple_clientId,
      'client_secret' => self::getSecret(),
    ]);

    /*
    response structure should be:
    stdClass Object
    (
      [access_token]  => STRING
      [token_type]    => STRING
      [expires_in]    => INT
      [refresh_token] => STRING
      [id_token]      => STRING
    )
    */

    // Error getting an access token
    if(!isset($response->access_token)) {
      return false;
    }

    // parse id_token
    $claims = explode('.', $response->id_token)[1];
    $claims = json_decode(base64_decode($claims));

    /*
    claims structure should be:
    stdClass Object
    (
        [iss]             => ISSUER_URL
        [aud]             => CLIENT_ID
        [exp]             => UNIX_TIMESTAMP
        [iat]             => UNIX_TIMESTAMP
        [sub]             => STRING
        [at_hash]         => STRING
        [email]           => STRING
        [email_verified]  => BOOLIAN
        [auth_time]       => UNIX_TIMESTAMP
        [nonce_supported] => BOOLIAN
    )
    */

    //store the original responce in claims
    $claims->tkn = $response;

    return $claims;

  }
  /**
   * refresh apple access token
   * 
   * Token endpoint docs: 
   * https://developer.apple.com/documentation/signinwithapplerestapi/generate_and_validate_tokens
   * 
   * @param string $appleAuthCode
   * 
   * @return object $tokenResponse
   */
  private static function refreshToken(string $refresh_token){
    global $settings;

    // request a token from apple
    $response = self::http('https://appleid.apple.com/auth/token', [
      'grant_type' => 'refresh_token',
      'refresh_token' => $refresh_token,
      'client_id' => $settings->apple_clientId,
      'client_secret' => self::getSecret(),
    ]);

    return $response;
  }
  /**
   * executes an HTTP request end returns response
   * 
   * @param string $url
   * @param array $params
   * @param array $headers
   * 
   * @return mixed $response
   */
  private static function http($url, $params=false, $headers=['Accept: application/json']) {

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if($params){
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    }

    $headers[] = 'User-Agent: curl'; # Apple requires a user agent header at the token endpoint
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    return json_decode($response);

  }
}
?>

<?php
if(count(get_included_files()) ==1) die(); //Direct Access Not Permitted 
require_once '../users/init.php';
global $settings, $user, $db, $authorize_url, $us_url_root, $abs_us_root;
if($settings->apple_login==1 && !$user->isLoggedIn()){
?>
  <div class="col-sm-6 text-center">
  <br/>
  <?PHP require_once $abs_us_root.$us_url_root.'usersc/plugins/apple_login/assets/apple_oauth_login.php'; ?>
  </div>
<?PHP
  }
?>

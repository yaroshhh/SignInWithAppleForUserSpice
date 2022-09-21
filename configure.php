<?php if(!in_array($user->data()->id,$master_account)){ Redirect::to($us_url_root.'users/admin.php');} //only allow master accounts to manage plugins! ?>

<?php
include "plugin_info.php";
pluginActive($plugin_name);
if(!empty($_POST['plugin_apple_login'])){
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
  }
  // Redirect::to('admin.php?err=I+agree!!!');
}
$token = Token::generate();
?>
<div class="content mt-3">
  <div class="row">
    <div class="col-6 offset-3">
      <h2>Apple Login Settings</h2>
      <strong>Please note:</strong> Social logins require that you do some 
      configuration on your own with Apple. It is strongly recommended that you 
      <a href="https://developer.apple.com/documentation/sign_in_with_apple/" target="_blank">
        <font color="blue">check the documentation at Apple development website.</font>
      </a><br><br>
      Basic instructions for implementation can be found over the internet. 
      For example: 
      <a href="https://arfasoftech.com/blog/How-to-Integrate-Sign-in-with-Apple-in-PHP-(5-minute-code)" target="_blank">
      <font color="blue">ARFASOFTECH rapid instructions</font>
      </a>.
      <br><br>


<div class="form-group">
      <label for="apple_login">Enable Apple Login</label>
      <span style="float:right;">
        <label class="switch switch-text switch-success">
          <input id="apple_login" type="checkbox" class="switch-input toggle" data-desc="Apple Login" <?php if($settings->apple_login==1) echo 'checked="true"'; ?>>
          <span data-on="Yes" data-off="No" class="switch-label"></span>
          <span class="switch-handle"></span>
        </label>
      </span>
    </div>

    <div class="form-group">
      <label for="apple_id">Apple Client ID</label>
      <input type="text" autocomplete="off" class="form-control ajxtxt" data-desc="Apple Client ID" name="apple_clientId" id="apple_clientId" value="<?=$settings->apple_clientId?>">
    </div>

    <div class="form-group">
      <label for="apple_id">Apple Team ID</label>
      <input type="text" autocomplete="off" class="form-control ajxtxt" data-desc="Apple Team ID" name="apple_teamId" id="apple_teamId" value="<?=$settings->apple_teamId?>">
    </div>

    <div class="form-group">
      <label for="apple_id">Apple Key ID</label>
      <input type="password" autocomplete="off" class="form-control ajxtxt" data-desc="Apple Key ID" name="apple_keyId" id="apple_keyId" value="<?=$settings->apple_keyId?>">
    </div>
  
    <div class="form-group">
      <label for="apple_secret">Apple Certification Path</label>
      <input type="text" autocomplete="off" class="form-control ajxtxt" data-desc="Apple Certification Path"  name="apple_certPath" id="apple_certPath" value="<?=$settings->apple_certPath?>">
    </div>

    <div class="form-group">
      <label for="apple_callback">Apple Callback URL (Path to oauth_success.php)</label>
      <input type="text" class="form-control ajxtxt" data-desc="Callback URL"  name="apple_callback" id="apple_callback" value="<?=$settings->apple_callback?>">
    </div>

This plugin made by <a href="https://yaronhelfer.com/" target="_blank">Yaron Helfer</a>.
</div>
</div>

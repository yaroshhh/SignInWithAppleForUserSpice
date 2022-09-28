<?php
define('SKIP_HEADERS',1);

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/users/init.php';
require_once __DIR__.'/apple_helpers.php';

// validate apple response varibles
if(!isset($_POST['code']) || !isset($_POST['state'])) {
	Redirect::to('/?err='.urlencode(lang('JOIN_APPLE_ABORTED')));
}

// validate apple session key
if(APPLE::getSessionKey() != $_POST['state']) {
	Redirect::to('/?err='.urlencode(lang('JOIN_APPLE_AUTH_ERROR')));
}

// claim for apple access token
if(!$apple_user = APPLE::getAccessToken($_POST['code'])){
	Redirect::to('/?err='.urlencode(lang('JOIN_APPLE_TOKEN_ERROR')));
}

// validate email address 
if(!$apple_user->email_verified){
	Redirect::to('/?err='.urlencode(lang('JOIN_APPLE_EMAIL_NOT_VERIFIED')));
}

/*
// validate private email address 
if(!empty($apple_user->is_private_email)){
	Redirect::to('/?err='.urlencode(lang('JOIN_APPLE_PRIVATE_EMAIL')));
}
*/

// DEBUG
//die('<code>'.json_encode($apple_user, JSON_PRETTY_PRINT).'</code>');

// authentication succeed
$apple_email = $apple_user->email;
$apple_id    = $apple_user->sub;
$whereNext   = isset($whereNext) ? $whereNext : '/';

$checkExistingQ = $db->query("SELECT * FROM users WHERE email = ?",array ($apple_email));

$CEQCount = $checkExistingQ->count();

//Existing UserSpice User Found
if ($CEQCount>0){
	$checkExisting = $checkExistingQ->first();
	$newLoginCount = $checkExisting->logins+1;
	$newLastLogin = date("Y-m-d H:i:s");
	
	$fields=array('oauth_uid'=>$apple_id, 'oauth_provider'=>'apple', 'logins'=>$newLoginCount, 'last_login'=>$newLastLogin);
	
	$db->update('users',$checkExisting->id,$fields);
	$sessionName = Config::get('session/session_name');
	Session::put($sessionName, $checkExisting->id);
	
	$hooks = getMyHooks(['page'=>'loginSuccess']);
	includeHook($hooks,'body');
	
	  $ip = ipCheck();
	  $q = $db->query("SELECT id FROM us_ip_list WHERE ip = ?",array($ip));
	  $c = $q->count();
	  if($c < 1){
		$db->insert('us_ip_list', array(
		  'user_id' => $checkExisting->id,
		  'ip' => $ip,
		));
	  }else{
		$f = $q->first();
		$db->update('us_ip_list',$f->id, array(
		  'user_id' => $checkExisting->id,
		  'ip' => $ip,
		));
	  }

	  Redirect::to($whereNext);

	}else{
	  if($settings->registration==0) {
		session_destroy();
		Redirect::to($us_url_root.'users/join.php');
		die();
	  } else {
		// //No Existing UserSpice User Found
		// if ($CEQCount<0){
		$date = date("Y-m-d H:i:s");
		$apple_fname = strstr($apple_email, '@', true);
		$apple_lname = '';
		if($settings->auto_assign_un==1) {
			$username=NULL;
		} else {
			$username=$apple_email;
		}
		$fields=array('email'=>$apple_email,'username'=>$username,'fname'=>$apple_fname,'lname'=>$apple_lname,'permissions'=>1,'logins'=>1,'join_date'=>$date,'last_login'=>$date,'email_verified'=>1,'password'=>NULL,'oauth_uid'=>$apple_id, 'oauth_provider'=>'apple');
	
		$db->insert('users',$fields);
		$theNewId = $db->lastId();
	
		$insert2 = $db->query("INSERT INTO user_permission_matches SET user_id = $theNewId, permission_id = 1");
	
		$ip = ipCheck();
		$q = $db->query("SELECT id FROM us_ip_list WHERE ip = ?",array($ip));
		$c = $q->count();
		if($c < 1){
		  $db->insert('us_ip_list', array(
			'user_id' => $theNewId,
			'ip' => $ip,
		  ));
		}else{
		  $f = $q->first();
		  $db->update('us_ip_list',$f->id, array(
			'user_id' => $theNewId,
			'ip' => $ip,
		  ));
		}
		include($abs_us_root.$us_url_root.'usersc/scripts/during_user_creation.php');
	
		$sessionName = Config::get('session/session_name');
		Session::put($sessionName, $theNewId);
		Redirect::to($whereNext);
	  }
	}
	
	
	?>
	
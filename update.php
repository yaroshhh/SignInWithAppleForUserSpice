<?php
//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
require_once("init.php");
	if (in_array($user->data()->id, $master_account)){
//all actions should be performed here.

//check which updates have been installed
$count = 0;
$db = DB::getInstance();
include "plugin_info.php";
pluginActive($plugin_name);


//Make sure the plugin is installed and get the existing updates
$checkQ = $db->query("SELECT id,updates FROM us_plugins WHERE plugin = ?",array($plugin_name));
$checkC = $checkQ->count();
if($checkC < 1){
	err($plugin_name." is not installed!");
	die();
}
$check = $checkQ->first();
if($check->updates == ''){
$existing = []; //deal with not finding any updates
}else{
$existing = json_decode($check->updates);
}
//list your updates here from oldest at the top to newest at the bottom.
//Give your update a unique update number/code.

//here is an example
$update = '00001';
if(!in_array($update,$existing)){
	
	$adds = [
		['settings', "`apple_callback` VARCHAR(255) DEFAULT '{$CFG->wwwroot}'","fbcallback"],
		['settings', "`apple_certPath` VARCHAR(255)","fbcallback"],
		['settings', "`apple_keyId` VARCHAR(255)","fbcallback"],
		['settings', "`apple_teamId` VARCHAR(255)","fbcallback"],
		['settings', "`apple_clientId` VARCHAR(255)","fbcallback"],
		['settings', "`apple_login` BIGINT(1) DEFAULT 0","fbcallback"]
	];
	  
	foreach($adds as $a){
		$db->query("ALTER TABLE `{$a[0]}` ADD COLUMN IF NOT EXISTS {$a[1]} AFTER `{$a[2]}`");
		if($db->error()){
		  throw new exception($db->errorString());
		}
	}

	$apple_callback = $settings->finalredir.'usersc/plugins/apple_login/assets/oauth_success.php';

	$db->query("UPDATE `settings` SET `apple_callback`='{$apple_callback}'");
	if($db->error()){
		throw new exception($db->errorString());
	}

	$existing[] = $update; //add the update you just did to the existing update array
	$count++;
}


//after all updates are done. Keep this at the bottom.
$new = json_encode($existing);
$db->update('us_plugins',$check->id,['updates'=>$new]);
if(!$db->error()) {
	if($count == 1){

	}else{
		err($count.' updates applied!');
	}
} else {
	err('Failed to save updates');
	logger($user->data()->id,"USPlugins","Failed to save updates, Error: ".$db->errorString());
}

} //do not perform actions outside of this statement

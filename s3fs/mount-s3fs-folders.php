#!/usr/bin/php
<?php

$deploy_hook_filename 	= '/opt/elasticbeanstalk/hooks/appdeploy/enact/02_s3fs.sh';
$deploy_hook_contents 	= '';
$ondeck_app_directory 	= '/var/app/ondeck';
$current_app_directory 	= '/var/app/current';
$s3fs_mounts 			= array();


// get the mounts out of the ondeck config file
if (file_exists("/var/app/ondeck/etc/s3fs.json")) {
	$s3fs_mounts = json_decode(file_get_contents("/var/app/ondeck/etc/s3fs.json"),true);
}

if (!empty($s3fs_mounts)) {

	$deploy_hook_contents =  "#!/usr/bin/env bash\n";
	$deploy_hook_contents .= ". /opt/elasticbeanstalk/support/envvars\n";

	echo "App directory: $ondeck_app_directory\n";

	// work from inside the app directory
	chdir($ondeck_app_directory);

	// loop through each of the mounts
	foreach($s3fs_mounts as $m) {

		if (empty($m['localfolder']) || empty($m['bucket'])) {
			echo "Error: missing localfolder or bucket in mount configuration file.\n";
			continue;
		}

		// does the local folder exist?
		// if not, create it.
		if (!file_exists($m['localfolder'])) {
			echo "Making the local folder: $ondeck_app_directory/{$m['localfolder']}\n";
			mkdir($m['localfolder'], 0775, TRUE);
			shell_exec("chown webapp.webapp {$m['localfolder']}");
		}
		
		/**
		 * Don't create the mounts, put a hook script into 
		 */
		$mount_command = 's3fs '.escapeshellarg($m['bucket']).' '.escapeshellarg($current_app_directory.'/'.$m['localfolder']).' -o use_cache=/tmp -o default_acl=public-read -o allow_other -o gid=501 -o uid=500';
		
		$deploy_hook_contents .= $mount_command."\n";

	}

	// write the contents of the file
	if (!empty($deploy_hook_contents)) {
		file_put_contents($deploy_hook_filename, $deploy_hook_contents);
		chmod($deploy_hook_filename, 0744);
	}

} else {
	echo "No mounts defined in yourapp /etc/s3fs.json";
}
?>
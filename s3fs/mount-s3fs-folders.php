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

		if (empty($m['local_folder']) || empty($m['bucket'])) {
			echo "Error: missing local_folder or bucket in mount configuration file.\n";
			continue;
		}

		// does the local folder exist?
		// if not, create it.
		if (!file_exists($m['local_folder'])) {
			echo "Making the local folder: $ondeck_app_directory/{$m['local_folder']}\n";
			mkdir($m['local_folder'], 0775, TRUE);
			shell_exec("chown webapp.webapp {$m['local_folder']}");
		}
		
		/**
		 * Don't create the mounts, put a hook script into elastic beanstalk's deploy process
		 */
		$mount_command = '/usr/local/bin/s3fs '.escapeshellarg($m['bucket']);
		$mount_command .= ' '.escapeshellarg($current_app_directory.'/'.$m['local_folder']);
		$mount_command .= ' -o '.(!empty($m['cache_folder'])?escapeshellarg('use_cache='.$m['cache_folder']):'use_cache=/tmp'); 
		$mount_command .= ' -o '.(!empty($m['default_acl'])?escapeshellarg('default_acl='.$m['default_acl']):'default_acl=public-read');
		$mount_command .= ' -o allow_other'; // needed to allow any other users access
		$mount_command .= ' -o '.(!empty($m['gid'])?escapeshellarg('gid='.$m['gid']):'gid=497');
		$mount_command .= ' -o '.(!empty($m['uid'])?escapeshellarg('uid='.$m['uid']):'uid=498');
		$mount_command .= ' -o '.(!empty($m['host'])?escapeshellarg('host='.$m['host']):'host=http://s3-us-east-1.amazonaws.com');

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
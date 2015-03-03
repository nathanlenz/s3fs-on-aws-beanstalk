#!/usr/bin/php
<?php

/**
 * First, always unmount all existing mounts.
 * This happens while the current app is still running in /var/app/current
 *
 * @todo  try putting this command in config pre-deploy before change permissions (12) or in 
 * another script that runs as a command, not container_command
 */
$existing_mounts = explode("\n", shell_exec("cat /proc/mounts|grep s3fs|awk '{print $2}'"));

if (!empty($existing_mounts)) {
	foreach($existing_mounts as $existing) {
		$existing = trim($existing);

		if (!empty($existing)) {
			echo "Unmount: $existing\n";
			shell_exec('fusermount -u '.escapeshellarg($existing));	
		}
	}
} else {
	echo "No S3FS volumes found to unmount.";
}

?>
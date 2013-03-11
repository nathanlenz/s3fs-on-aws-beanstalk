#!/usr/bin/php
<?php

if (empty($_SERVER['AWS_SECRET_KEY']) || empty($_SERVER['AWS_ACCESS_KEY_ID'])) {
	echo "Access keys are not in the environment";
	exit(1);
}

$credentials_file = '/etc/passwd-s3fs';

$new_credentials = $_SERVER['AWS_ACCESS_KEY_ID'].':'.$_SERVER['AWS_SECRET_KEY'];

if (!file_exists($credentials_file) || (file_get_contents($credentials_file) != $new_credentials)) {

	file_put_contents($credentials_file, $new_credentials);
	chmod($credentials_file, 0640);
	chown($credentials_file, 'webapp');
}

?>
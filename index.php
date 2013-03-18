<?php
echo "<h1>S3FS on Aws Beanstalk</h1><hr />";

if (empty($_SERVER['AWS_SECRET_KEY']) || empty($_SERVER['AWS_ACCESS_KEY_ID'])) {
	echo "<p>Error: Your access keys are not in the environment.  See Amazon's documentation on <a href=\"http://docs.aws.amazon.com/elasticbeanstalk/latest/dg/create_deploy_PHP.container.html\">how to add your credentials</a> to your Elastic Beanstalk Container. </p>";
	exit();
}

$s3fs_mounts = json_decode(file_get_contents("etc/s3fs.json"),true);

if (empty($s3fs_mounts)) {
	echo "<p>Warning: You haven't configured any buckets.  Add an etc/s3fs.json file to your project.</p>";
echo <<<JSONS3
<pre>
[
	{ 
		// this will mount the s3 bucket "name.of.bucket" to the /my-bucket/ folder in your application.
		"localfolder": "my-bucket",
		"bucket": "name.of.bucket" 
	}
]
</pre>
JSONS3;

} else {

	echo "<h2>You have configured the following buckets:</h2>
	<ul>";
	foreach($s3fs_mounts as $mount) {
		echo "<li>Bucket: <i>{$mount['bucket']}</i> should be mounted at <i>{$mount['localfolder']}</i><br>";

		if (file_exists($mount['localfolder'].'/bucket.png')) {
			echo "Image served from the bucket folder:<br>";
			echo "<img src='/{$mount['localfolder']}/bucket.png' alt='bucket'>";
		} else if ($_GET['place'] === $mount['bucket']) {
			copy('bucket.png', $mount['localfolder'].'/bucket.png');
			header('Location: /index.php');
			exit();
		} else {
			echo "<a href='index.php?place=".urlencode($mount['bucket'])."'>Try putting an image on the bucket.</a>";
		}

		echo "</li>";
	}
	echo "</ul>";

	echo "<h3>s3fs mounts in your /proc/mounts file:</h3>
	<pre>";
	echo `cat /proc/mounts|grep s3fs`;
	echo "</pre>";
	


}



?>
s3fs-on-aws-beanstalk
=====================

This is a simple example of how to configure and mount S3FS buckets into your Elastic Beanstalk application. It was built for a PHP app like Drupal or Wordpress that needs a local folder to persist across load balanced instances.

Features
--------
* Should be able to drop into any PHP app to enable S3FS folders.
* Downloads, compiles and installs everything using `ebextensions/*.config` files. No custom AMI required.
* Uses Elastic Beanstalk's deploy hooks to take actions at the appropriate times during deploy cycle.

How it works
------------
It reads the local folder to bucket mappings from your `_etc/s3fs.json` file and places the commands needed into the following files:

```
/opt/elasticbeanstalk/hooks/appdeploy/pre/11_unmount_s3fs.sh
/opt/elasticbeanstalk/hooks/appdeploy/enact/02_s3fs.sh
```

The `11_unmount_s3fs.sh` file is called right before the script that fixes the ownership of all your existing files.  If you don't unmount the s3fs buckets first, then the chown commands take so long that the application fails to launch.  It is also *very* important that this script runs before the `enact/09clean.sh` script is run because it will remove all the files from your bucket if the mounts are still connected.

Then the `02_s3fs.sh` script should be called right after your application has "flipped" into place.  Moving the new version from `/var/app/ondeck` to `/var/app/current`.  

The correct order to a deployment is:

1. Unpack your new version and make sure the environment is in place.
2. Unmount existing S3FS folders.
3. Move your new version into place.
4. Mount the S3FS folders into the new version.
5. Delete the old version.


Directions
----------
1. Check out the sample app into a local folder.
2. Create a PHP Elastic Beanstalk Application.
3. Put your S3 credentials into the Environment's container.
4. Add your bucket into the `etc/s3fs.json` file.
5. Use _git aws.push_ to push the application live.
6. Visit your application's domain to see if the buckets were mounted.

Notes
-----
Uses s3fs version 1.61.  Version 1.63 did not allow for non-root write permissions.  In the typical application, the apache user needs to be able to write files (like images) to the S3 bucket.

Tested with the "32bit Amazon Linux running PHP 5.3" AMI.

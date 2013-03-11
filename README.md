s3fs-on-aws-beanstalk
=====================

This is a simple example of how to configure and mount S3FS buckets into your Elastic Beanstalk application. It was built for a PHP app like Drupal or Wordpress that needs a local folder to persist across load balanced instances.

Features
--------
* Should be able to drop into any PHP app to enable S3FS folders.
* Downloads, compiles and installs everything using ebextensions/*.config files. No custom AMI required.
* Uses Elastic Beanstalk's deploy hooks to take actions at the appropriate times during deploy cycle.

Directions
----------
1. Check out the sample app into a local folder.
2. Create a PHP Elastic Beanstalk Application.
3. Put your S3 credentials into the Environment's container.
4. Add your bucket into the /etc/s3fs.json file.
5. Use _git aws.push_ to push the application live.
6. Visit your application's domain to see if the buckets were mounted.

Notes
-----
Uses s3fs version 1.61.  Version 1.63 did not allow for non-root write permissions.  In the typical application, the apache user needs to be able to write files (like images) to the S3 bucket.

Tested with the "32bit Amazon Linux running PHP 5.3" AMI.


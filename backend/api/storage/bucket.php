<?php
# Includes the autoloader for libraries installed with composer
require '../../vendor/autoload.php';

use Google\Cloud\Storage\StorageClient;

# Your Google Cloud Platform project ID

/**
 * Generate a v4 signed URL for uploading an object.
 *
 * @param string $bucketName The name of your Cloud Storage bucket.
 * @param string $objectName The name of your Cloud Storage object.
 */
function upload_object_v4_signed_url($bucketName, $objectName, $objectType)
{
		$projectId = 'crowdmeta';
		$home_dir = $_SERVER['HOME'];
		$storage = new StorageClient([
				'keyFilePath' => $home_dir. '/creds/crowdmeta-c7a67965dca5.json',
				'projectId' => $projectId
		]);
		
		$bucket = $storage->bucket($bucketName);
    $object = $bucket->object($objectName);
    $url = $object->signedUrl(
        # This URL is valid for 15 minutes
        new \DateTime('15 min'),
        [
            'method' => 'PUT',
						//'contentType' => 'application/octet-stream',
						'version' => 'v4',
        ]
    );

		return $url;
}
?>

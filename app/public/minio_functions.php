<?php
/**
 * MinIO Helper Functions
 * 
 * Functions for interacting with MinIO object storage
 */

require_once __DIR__ . '/minio_config.php';

/**
 * Upload file to MinIO
 * @param string $localPath Local file path
 * @param string $objectKey Object key (filename) in MinIO
 * @param array $metadata Additional metadata
 * @return array ['success' => bool, 'message' => string, 'url' => string]
 */
function minio_upload_file($localPath, $objectKey, $metadata = [])
{
    try {
        $client = get_minio_client();
        if (!$client) {
            return ['success' => false, 'message' => 'MinIO client not available'];
        }
        
        if (!file_exists($localPath)) {
            return ['success' => false, 'message' => 'Local file not found'];
        }
        
        // Prepare upload parameters
        $params = [
            'Bucket' => MINIO_BUCKET,
            'Key' => $objectKey,
            'SourceFile' => $localPath,
            'ContentType' => mime_content_type($localPath),
        ];
        
        // Add metadata if provided
        if (!empty($metadata)) {
            $params['Metadata'] = $metadata;
        }
        
        // Upload file
        $result = $client->putObject($params);
        
        // Get object URL
        $url = $client->getObjectUrl(MINIO_BUCKET, $objectKey);
        
        return [
            'success' => true,
            'message' => 'File uploaded to MinIO',
            'url' => $url,
            'object_key' => $objectKey,
            'etag' => $result['ETag'] ?? null
        ];
        
    } catch (Exception $e) {
        error_log('MinIO upload error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Upload failed: ' . $e->getMessage()];
    }
}

/**
 * Download file from MinIO
 * @param string $objectKey Object key in MinIO
 * @param string $savePath Local path to save file
 * @return array ['success' => bool, 'message' => string]
 */
function minio_download_file($objectKey, $savePath)
{
    try {
        $client = get_minio_client();
        if (!$client) {
            return ['success' => false, 'message' => 'MinIO client not available'];
        }
        
        $result = $client->getObject([
            'Bucket' => MINIO_BUCKET,
            'Key' => $objectKey,
            'SaveAs' => $savePath,
        ]);
        
        return [
            'success' => true,
            'message' => 'File downloaded from MinIO',
            'path' => $savePath
        ];
        
    } catch (Exception $e) {
        error_log('MinIO download error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Download failed: ' . $e->getMessage()];
    }
}

/**
 * Delete file from MinIO
 * @param string $objectKey Object key in MinIO
 * @return array ['success' => bool, 'message' => string]
 */
function minio_delete_file($objectKey)
{
    try {
        $client = get_minio_client();
        if (!$client) {
            return ['success' => false, 'message' => 'MinIO client not available'];
        }
        
        $client->deleteObject([
            'Bucket' => MINIO_BUCKET,
            'Key' => $objectKey,
        ]);
        
        return ['success' => true, 'message' => 'File deleted from MinIO'];
        
    } catch (Exception $e) {
        error_log('MinIO delete error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()];
    }
}

/**
 * Check if file exists in MinIO
 * @param string $objectKey Object key in MinIO
 * @return bool
 */
function minio_file_exists($objectKey)
{
    try {
        $client = get_minio_client();
        if (!$client) {
            return false;
        }
        
        return $client->doesObjectExist(MINIO_BUCKET, $objectKey);
        
    } catch (Exception $e) {
        error_log('MinIO file exists check error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get file metadata from MinIO
 * @param string $objectKey Object key in MinIO
 * @return array|null
 */
function minio_get_file_metadata($objectKey)
{
    try {
        $client = get_minio_client();
        if (!$client) {
            return null;
        }
        
        $result = $client->headObject([
            'Bucket' => MINIO_BUCKET,
            'Key' => $objectKey,
        ]);
        
        return [
            'size' => $result['ContentLength'] ?? 0,
            'mime' => $result['ContentType'] ?? 'application/octet-stream',
            'last_modified' => $result['LastModified'] ?? null,
            'etag' => $result['ETag'] ?? null,
            'metadata' => $result['Metadata'] ?? [],
        ];
        
    } catch (Exception $e) {
        error_log('MinIO metadata error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Get presigned URL for temporary file access
 * @param string $objectKey Object key in MinIO
 * @param int $expiresIn Expiration time in seconds (default: 1 hour)
 * @return string|null
 */
function minio_get_presigned_url($objectKey, $expiresIn = 3600)
{
    try {
        $client = get_minio_client();
        if (!$client) {
            return null;
        }
        
        $cmd = $client->getCommand('GetObject', [
            'Bucket' => MINIO_BUCKET,
            'Key' => $objectKey,
        ]);
        
        $request = $client->createPresignedRequest($cmd, "+{$expiresIn} seconds");
        
        return (string) $request->getUri();
        
    } catch (Exception $e) {
        error_log('MinIO presigned URL error: ' . $e->getMessage());
        return null;
    }
}

/**
 * List all files in MinIO bucket
 * @param string $prefix Filter by prefix (optional)
 * @return array
 */
function minio_list_files($prefix = '')
{
    try {
        $client = get_minio_client();
        if (!$client) {
            return [];
        }
        
        $params = ['Bucket' => MINIO_BUCKET];
        if ($prefix) {
            $params['Prefix'] = $prefix;
        }
        
        $result = $client->listObjects($params);
        
        $files = [];
        if (isset($result['Contents'])) {
            foreach ($result['Contents'] as $object) {
                $files[] = [
                    'key' => $object['Key'],
                    'size' => $object['Size'],
                    'last_modified' => $object['LastModified'],
                    'etag' => $object['ETag'],
                ];
            }
        }
        
        return $files;
        
    } catch (Exception $e) {
        error_log('MinIO list files error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Copy file within MinIO
 * @param string $sourceKey Source object key
 * @param string $destKey Destination object key
 * @return array ['success' => bool, 'message' => string]
 */
function minio_copy_file($sourceKey, $destKey)
{
    try {
        $client = get_minio_client();
        if (!$client) {
            return ['success' => false, 'message' => 'MinIO client not available'];
        }
        
        $client->copyObject([
            'Bucket' => MINIO_BUCKET,
            'Key' => $destKey,
            'CopySource' => MINIO_BUCKET . '/' . $sourceKey,
        ]);
        
        return ['success' => true, 'message' => 'File copied in MinIO'];
        
    } catch (Exception $e) {
        error_log('MinIO copy error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Copy failed: ' . $e->getMessage()];
    }
}

/**
 * Get public URL for file (if bucket is public)
 * @param string $objectKey Object key in MinIO
 * @return string
 */
function minio_get_public_url($objectKey)
{
    $endpoint = MINIO_ENDPOINT;
    $bucket = MINIO_BUCKET;
    return "{$endpoint}/{$bucket}/{$objectKey}";
}

/**
 * Stream file from MinIO (for preview/download)
 * @param string $objectKey Object key in MinIO
 * @param bool $inline True for inline display, false for download
 * @return bool Success status
 */
function minio_stream_file($objectKey, $inline = true)
{
    try {
        $client = get_minio_client();
        if (!$client) {
            return false;
        }
        
        $result = $client->getObject([
            'Bucket' => MINIO_BUCKET,
            'Key' => $objectKey,
        ]);
        
        // Set headers
        header('Content-Type: ' . ($result['ContentType'] ?? 'application/octet-stream'));
        header('Content-Length: ' . ($result['ContentLength'] ?? 0));
        
        $disposition = $inline ? 'inline' : 'attachment';
        $filename = basename($objectKey);
        header("Content-Disposition: {$disposition}; filename=\"{$filename}\"");
        
        // Output file content
        echo $result['Body'];
        
        return true;
        
    } catch (Exception $e) {
        error_log('MinIO stream error: ' . $e->getMessage());
        return false;
    }
}

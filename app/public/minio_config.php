<?php
/**
 * MinIO Configuration
 * 
 * This file contains MinIO S3-compatible object storage configuration.
 * MinIO SDK uses AWS SDK for PHP (S3 compatible API)
 */

// MinIO connection settings
define('MINIO_ENABLED', getenv('MINIO_ENABLED') !== false ? (bool)getenv('MINIO_ENABLED') : true);
define('MINIO_ENDPOINT', getenv('MINIO_ENDPOINT') ?: 'http://minio:9000');
define('MINIO_ACCESS_KEY', getenv('MINIO_ACCESS_KEY') ?: 'minioadmin');
define('MINIO_SECRET_KEY', getenv('MINIO_SECRET_KEY') ?: 'minioadmin123');
define('MINIO_BUCKET', getenv('MINIO_BUCKET') ?: 'clairocloud');
define('MINIO_REGION', getenv('MINIO_REGION') ?: 'us-east-1');

// Storage mode: 'minio', 'local', or 'hybrid' (try minio, fallback to local)
define('STORAGE_MODE', getenv('STORAGE_MODE') ?: 'hybrid');

/**
 * Get MinIO S3 Client instance
 * @return \Aws\S3\S3Client|null
 */
function get_minio_client()
{
    static $client = null;
    
    if ($client !== null) {
        return $client;
    }
    
    if (!MINIO_ENABLED) {
        return null;
    }
    
    // Check if AWS SDK is available
    $autoloadPath = __DIR__ . '/../vendor/autoload.php';
    if (!file_exists($autoloadPath)) {
        error_log('MinIO: AWS SDK not installed. Run composer install.');
        return null;
    }
    
    require_once $autoloadPath;
    
    try {
        $client = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region' => MINIO_REGION,
            'endpoint' => MINIO_ENDPOINT,
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => MINIO_ACCESS_KEY,
                'secret' => MINIO_SECRET_KEY,
            ],
        ]);
        
        return $client;
    } catch (Exception $e) {
        error_log('MinIO client error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Check if MinIO is available and bucket exists
 * @return bool
 */
function minio_is_available()
{
    static $checked = null;
    static $available = false;
    
    if ($checked !== null) {
        return $available;
    }
    
    $checked = true;
    
    try {
        $client = get_minio_client();
        if (!$client) {
            return false;
        }
        
        // Check if bucket exists
        $available = $client->doesBucketExist(MINIO_BUCKET);
        
        // Try to create bucket if it doesn't exist
        if (!$available) {
            try {
                $client->createBucket([
                    'Bucket' => MINIO_BUCKET,
                ]);
                $available = true;
            } catch (Exception $e) {
                error_log('MinIO: Could not create bucket: ' . $e->getMessage());
            }
        }
        
        return $available;
    } catch (Exception $e) {
        error_log('MinIO availability check failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Determine which storage to use based on mode and availability
 * @return string 'minio' or 'local'
 */
function get_active_storage()
{
    if (STORAGE_MODE === 'local') {
        return 'local';
    }
    
    if (STORAGE_MODE === 'minio' && minio_is_available()) {
        return 'minio';
    }
    
    if (STORAGE_MODE === 'hybrid') {
        return minio_is_available() ? 'minio' : 'local';
    }
    
    return 'local';
}

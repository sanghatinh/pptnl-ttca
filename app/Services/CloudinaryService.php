<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CloudinaryService
{
    private $cloudName;
    private $apiKey;
    private $apiSecret;
    private $uploadUrl;
    private $destroyUrl;

    public function __construct()
    {
        $this->cloudName = config('cloudinary.cloud_name', 'dhtgcccax');
        $this->apiKey = config('cloudinary.api_key', '163561997864559');
        $this->apiSecret = config('cloudinary.api_secret', 'PG3yyAHUdTWtq4EFDA7-3HZuJEs');
        $this->uploadUrl = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";
        $this->destroyUrl = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/destroy";
    }

    /**
     * Upload รูปแบบ optimized พร้อม auto-transformation
     */
    public function uploadImageOptimized(UploadedFile $file, array $options = []): array
{
    try {
        if (!$this->validateFile($file)) {
            return $this->errorResponse('Invalid file provided');
        }

        $uploadPreset = $options['upload_preset'] ?? 'ml_default';
        $folder = $options['folder'] ?? 'users';
        $quality = $options['quality'] ?? 'auto:good';
        $format = $options['format'] ?? 'auto';

        Log::info('Starting optimized Cloudinary upload', [
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'folder' => $folder
        ]);

        // ส่ง $folder เข้าไปด้วย
        $result = $this->uploadUnsigned($file, $uploadPreset, $quality, $format, $folder);

        if (!$result['success']) {
            Log::info('Unsigned upload failed, trying signed upload');
            $result = $this->uploadSigned($file, $folder, $quality, $format);
        }

        return $result;

    } catch (\Exception $e) {
        Log::error('Optimized upload error: ' . $e->getMessage());
        return $this->errorResponse($e->getMessage());
    }
}

    /**
     * Upload แบบ unsigned (เร็วกว่า)
     */
private function uploadUnsigned(UploadedFile $file, string $uploadPreset, string $quality, string $format, string $folder = 'users'): array
{
    $postData = [
        'file' => new \CURLFile($file->getPathname(), $file->getMimeType(), $file->getClientOriginalName()),
        'upload_preset' => $uploadPreset,
        'quality' => $quality,
        'fetch_format' => $format,
        'folder' => $folder // ตอนนี้ $folder จะไม่ undefined
    ];

    return $this->executeCurlRequest($this->uploadUrl, $postData, 'unsigned');
}

    /**
     * Upload แบบ signed
     */
    private function uploadSigned(UploadedFile $file, string $folder, string $quality, string $format): array
    {
        $timestamp = time();
        $public_id = $this->generatePublicId($file, $folder);
        
        // Parameters for signature
        $params = [
            'timestamp' => $timestamp,
            'public_id' => $public_id,
            'folder' => $folder,
            'quality' => $quality,
            'fetch_format' => $format,
            'overwrite' => 'true'
        ];
        
        $signature = $this->generateSignature($params);
        
        $postData = array_merge($params, [
            'file' => new \CURLFile($file->getPathname(), $file->getMimeType(), $file->getClientOriginalName()),
            'api_key' => $this->apiKey,
            'signature' => $signature
        ]);

        return $this->executeCurlRequest($this->uploadUrl, $postData, 'signed');
    }

    /**
     * Execute cURL request with optimized settings
     */
    private function executeCurlRequest(string $url, array $postData, string $type): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 120, // เพิ่ม timeout
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_ENCODING => '', // Enable compression
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_USERAGENT => 'TTCA-PTNL/1.0'
        ]);
        
        $startTime = microtime(true);
        $response = curl_exec($ch);
        $uploadTime = microtime(true) - $startTime;
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        Log::info("Cloudinary {$type} upload completed", [
            'upload_time' => round($uploadTime, 2) . 's',
            'http_code' => $httpCode,
            'upload_size' => $info['size_upload'] ?? 0
        ]);

        if ($curlError) {
            return $this->errorResponse($curlError, 'Network error');
        }

        if ($httpCode !== 200) {
            Log::error("Cloudinary upload failed", ['response' => $response, 'http_code' => $httpCode]);
            return $this->errorResponse($response, 'Upload failed');
        }

        $result = json_decode($response, true);
        
        if (isset($result['error'])) {
            return $this->errorResponse($result['error']['message'] ?? 'Unknown error', 'Upload failed');
        }

        return [
            'success' => true,
            'secure_url' => $result['secure_url'] ?? '',
            'public_id' => $result['public_id'] ?? '',
            'width' => $result['width'] ?? null,
            'height' => $result['height'] ?? null,
            'format' => $result['format'] ?? null,
            'bytes' => $result['bytes'] ?? null,
            'upload_time' => round($uploadTime, 2)
        ];
    }

    /**
     * Upload with progress callback
     */
    public function uploadWithProgress(UploadedFile $file, callable $progressCallback = null, array $options = []): array
    {
        try {
            if (!$this->validateFile($file)) {
                return $this->errorResponse('Invalid file provided');
            }

            $uploadPreset = $options['upload_preset'] ?? 'ml_default';
            
            $postData = [
                'file' => new \CURLFile($file->getPathname(), $file->getMimeType(), $file->getClientOriginalName()),
                'upload_preset' => $uploadPreset,
                'quality' => 'auto:good',
                'fetch_format' => 'auto'
            ];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->uploadUrl,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 120,
                CURLOPT_NOPROGRESS => false,
                CURLOPT_PROGRESSFUNCTION => function($resource, $download_size, $downloaded, $upload_size, $uploaded) use ($progressCallback) {
                    if ($upload_size > 0 && $progressCallback) {
                        $percentage = round(($uploaded / $upload_size) * 100);
                        $progressCallback($percentage, $uploaded, $upload_size);
                    }
                    return 0; // Continue upload
                }
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                return $this->errorResponse($curlError, 'Network error');
            }

            if ($httpCode !== 200) {
                return $this->errorResponse($response, 'Upload failed');
            }

            $result = json_decode($response, true);
            
            if (isset($result['error'])) {
                return $this->errorResponse($result['error']['message'] ?? 'Unknown error', 'Upload failed');
            }

            return [
                'success' => true,
                'secure_url' => $result['secure_url'] ?? '',
                'public_id' => $result['public_id'] ?? '',
                'width' => $result['width'] ?? null,
                'height' => $result['height'] ?? null,
                'format' => $result['format'] ?? null,
                'bytes' => $result['bytes'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error('Upload with progress error: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Batch upload หลายไฟล์พร้อมกัน
     */
    public function batchUpload(array $files, array $options = []): array
    {
        $results = [];
        $successful = 0;
        $failed = 0;

        foreach ($files as $index => $file) {
            Log::info("Batch upload: Processing file {$index}");
            
            $result = $this->uploadImageOptimized($file, $options);
            
            if ($result['success']) {
                $successful++;
            } else {
                $failed++;
            }
            
            $results[] = array_merge($result, ['file_index' => $index]);
        }

        return [
            'success' => $failed === 0,
            'total' => count($files),
            'successful' => $successful,
            'failed' => $failed,
            'results' => $results
        ];
    }

    /**
     * Enhanced delete with retry mechanism
     */
    public function deleteImageEnhanced(string $publicId, int $retries = 3): array
    {
        for ($attempt = 1; $attempt <= $retries; $attempt++) {
            $result = $this->deleteImage($publicId);
            
            if ($result['success']) {
                return $result;
            }
            
            if ($attempt < $retries) {
                Log::info("Delete attempt {$attempt} failed, retrying...", ['public_id' => $publicId]);
                sleep(1); // Wait 1 second before retry
            }
        }

        return $result;
    }

    /**
     * Helper methods
     */
    private function validateFile(UploadedFile $file): bool
    {
        if (!$file || !$file->isValid()) {
            return false;
        }

        // Check file size (10MB max)
        if ($file->getSize() > 10 * 1024 * 1024) {
            return false;
        }

        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return false;
        }

        return true;
    }

    private function generatePublicId(UploadedFile $file, string $folder): string
    {
        $extension = $file->getClientOriginalExtension();
        $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $timestamp = time();
        
        return "{$folder}/" . preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename) . "_{$timestamp}";
    }

    private function generateSignature(array $params): string
    {
        ksort($params);
        $stringToSign = '';
        
        foreach ($params as $key => $value) {
            if (!empty($value)) {
                $stringToSign .= "{$key}={$value}&";
            }
        }
        
        $stringToSign = rtrim($stringToSign, '&') . $this->apiSecret;
        return sha1($stringToSign);
    }

    private function errorResponse(string $error, string $message = 'Operation failed'): array
    {
        return [
            'success' => false,
            'error' => $error,
            'message' => $message
        ];
    }

    /**
     * Get cached transformation URL
     */
    public function getCachedTransformationUrl(string $publicId, array $transformations = []): string
    {
        $cacheKey = 'cloudinary_url_' . md5($publicId . serialize($transformations));
        
        return Cache::remember($cacheKey, 3600, function() use ($publicId, $transformations) {
            return $this->getTransformationUrl($publicId, $transformations);
        });
    }

    // Keep existing methods for backward compatibility
    public function uploadImageUnsigned(UploadedFile $file, string $uploadPreset = 'ml_default'): array
    {
        return $this->uploadImageOptimized($file, ['upload_preset' => $uploadPreset]);
    }

    public function uploadImageSimple(UploadedFile $file, string $folder = 'users', array $simpleOptions = []): array
    {
        return $this->uploadImageOptimized($file, array_merge(['folder' => $folder], $simpleOptions));
    }

    public function deleteImage(string $publicId): array
    {
        try {
            $timestamp = time();
            $signature = sha1("public_id={$publicId}&timestamp={$timestamp}{$this->apiSecret}");
            
            $postData = [
                'public_id' => $publicId,
                'api_key' => $this->apiKey,
                'timestamp' => $timestamp,
                'signature' => $signature
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->destroyUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                return [
                    'success' => false,
                    'error' => $curlError
                ];
            }
            
            if ($httpCode !== 200) {
                return [
                    'success' => false,
                    'error' => $response
                ];
            }
            
            $result = json_decode($response, true);
            
            return [
                'success' => $result['result'] === 'ok',
                'result' => $result['result'] ?? 'unknown'
            ];

        } catch (\Exception $e) {
            Log::error('Cloudinary delete error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getTransformationUrl(string $publicId, array $transformations = []): string
    {
        try {
            $baseUrl = "https://res.cloudinary.com/{$this->cloudName}/image/upload/";
            
            if (!empty($transformations)) {
                $transformParts = [];
                
                foreach ($transformations as $key => $value) {
                    switch ($key) {
                        case 'width':
                            $transformParts[] = "w_$value";
                            break;
                        case 'height':
                            $transformParts[] = "h_$value";
                            break;
                        case 'crop':
                            $transformParts[] = "c_$value";
                            break;
                        case 'gravity':
                            $transformParts[] = "g_$value";
                            break;
                        case 'quality':
                            $transformParts[] = "q_$value";
                            break;
                        default:
                            $transformParts[] = "$key" . "_$value";
                    }
                }
                
                if (!empty($transformParts)) {
                    $baseUrl .= implode(',', $transformParts) . '/';
                }
            }
            
            return $baseUrl . $publicId;
            
        } catch (\Exception $e) {
            Log::error('Cloudinary transformation URL error: ' . $e->getMessage());
            return "https://res.cloudinary.com/{$this->cloudName}/image/upload/{$publicId}";
        }
    }

    public function getImageUrl($publicId)
{
    $cloudName = env('CLOUDINARY_CLOUD_NAME', 'dhtgcccax');
    return "https://res.cloudinary.com/{$cloudName}/image/upload/{$publicId}";
}

}
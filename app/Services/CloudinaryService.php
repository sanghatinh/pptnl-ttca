<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{
    private $cloudName;
    private $apiKey;
    private $apiSecret;

    public function __construct()
    {
        $this->cloudName = config('cloudinary.cloud_name', 'dhtgcccax');
        $this->apiKey = config('cloudinary.api_key', '163561997864559');
        $this->apiSecret = config('cloudinary.api_secret', 'PG3yyAHUdTWtq4EFDA7-3HZuJEs');
    }

    /**
     * Upload an image to Cloudinary using direct API
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param array $options
     * @return array
     */
   public function uploadImageUnsigned(UploadedFile $file, string $uploadPreset = 'ml_default'): array
{
    try {
        if (!$file || !$file->isValid()) {
            return [
                'success' => false,
                'error' => 'Invalid file provided'
            ];
        }

        Log::info('Starting unsigned Cloudinary upload', [
            'original_name' => $file->getClientOriginalName(),
            'upload_preset' => $uploadPreset
        ]);

        $postData = [
            'file' => new \CURLFile($file->getPathname(), $file->getMimeType(), $file->getClientOriginalName()),
            'upload_preset' => $uploadPreset
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            return [
                'success' => false,
                'error' => $curlError,
                'message' => 'Network error'
            ];
        }
        
        if ($httpCode !== 200) {
            return [
                'success' => false,
                'error' => $response,
                'message' => 'Upload failed'
            ];
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['error'])) {
            return [
                'success' => false,
                'error' => $result['error']['message'] ?? 'Unknown error',
                'message' => 'Upload failed'
            ];
        }
        
        return [
            'success' => true,
            'secure_url' => $result['secure_url'] ?? '',
            'public_id' => $result['public_id'] ?? '',
            'width' => $result['width'] ?? null,
            'height' => $result['height'] ?? null,
            'format' => $result['format'] ?? null,
            'bytes' => $result['bytes'] ?? null,
        ];

    } catch (\Exception $e) {
        Log::error('Cloudinary unsigned upload error: ' . $e->getMessage());
        
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'message' => 'Failed to upload image to Cloudinary'
        ];
    }
}

    /**
     * Upload แบบง่ายๆ โดยใช้ signature แบบพื้นฐาน
     */
    public function uploadImageSimple(UploadedFile $file, string $folder = 'users', array $simpleOptions = []): array
    {
        try {
            // ตรวจสอบไฟล์ก่อน
            if (!$file || !$file->isValid()) {
                return [
                    'success' => false,
                    'error' => 'Invalid file provided'
                ];
            }

            Log::info('Starting simple Cloudinary upload', [
                'original_name' => $file->getClientOriginalName(),
                'folder' => $folder
            ]);

            $timestamp = time();
            
            // สร้าง signature แบบง่าย
           $signature = sha1("timestamp={$timestamp}{$this->apiSecret}");
            
          $postData = [
    'file' => new \CURLFile($file->getPathname(), $file->getMimeType(), $file->getClientOriginalName()),
    'api_key' => $this->apiKey,
    'timestamp' => $timestamp,
    'signature' => $signature
];
            
            // เพิ่ม folder ถ้ามี
            if ($folder) {
                $postData['folder'] = $folder;
            }
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                return [
                    'success' => false,
                    'error' => $curlError,
                    'message' => 'Network error'
                ];
            }
            
            if ($httpCode !== 200) {
                return [
                    'success' => false,
                    'error' => $response,
                    'message' => 'Upload failed'
                ];
            }
            
            $result = json_decode($response, true);
            
            if (isset($result['error'])) {
                return [
                    'success' => false,
                    'error' => $result['error']['message'] ?? 'Unknown error',
                    'message' => 'Upload failed'
                ];
            }
            
            return [
                'success' => true,
                'secure_url' => $result['secure_url'] ?? '',
                'public_id' => $result['public_id'] ?? '',
                'width' => $result['width'] ?? null,
                'height' => $result['height'] ?? null,
                'format' => $result['format'] ?? null,
                'bytes' => $result['bytes'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Cloudinary simple upload error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to upload image to Cloudinary'
            ];
        }
    }

    /**
     * Delete an image from Cloudinary
     */
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
            curl_setopt($ch, CURLOPT_URL, "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/destroy");
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

    /**
     * Generate transformation URL
     */
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
}
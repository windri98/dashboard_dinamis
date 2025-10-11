<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class FileUploadService
{
    protected $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    protected $allowedFileTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar'];
    protected $maxFileSize = 10 * 1024 * 1024; // 10MB
    protected $maxImageSize = 5 * 1024 * 1024; // 5MB

    /**
     * Handle file upload untuk dynamic table
     */
    public function handleFileUploads(Request $request, string $tableName, array $data): array
    {
        $uploadedFiles = [];

        foreach ($request->allFiles() as $fieldName => $file) {
            if ($file && $file->isValid()) {
                try {
                    $uploadResult = $this->uploadFile($file, $tableName, $fieldName);
                    $data[$fieldName] = $uploadResult['path'];
                    $uploadedFiles[] = $uploadResult;
                } catch (\Exception $e) {
                    // Cleanup already uploaded files on error
                    $this->cleanupUploadedFiles($uploadedFiles);
                    throw new \Exception("File upload error for {$fieldName}: " . $e->getMessage());
                }
            }
        }

        return $data;
    }

    /**
     * Upload single file
     */
    public function uploadFile($file, string $tableName, string $fieldName): array
    {
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        // Validate file type and size
        $this->validateFile($file, $extension, $size);

        // Generate unique filename
        $fileName = $this->generateFileName($originalName, $extension);
        
        // Determine storage path
        $storagePath = "uploads/{$tableName}/{$fieldName}";
        
        // Handle image files - create thumbnails
        if ($this->isImage($extension)) {
            return $this->handleImageUpload($file, $storagePath, $fileName, $originalName, $size);
        } else {
            return $this->handleFileUpload($file, $storagePath, $fileName, $originalName, $size);
        }
    }

    /**
     * Handle image upload with thumbnail generation
     */
    protected function handleImageUpload($file, string $storagePath, string $fileName, string $originalName, int $size): array
    {
        // Store original image
        $originalPath = $file->storeAs($storagePath . '/original', $fileName, 'public');
        
        // Create thumbnails
        $thumbnails = $this->createThumbnails($file, $storagePath, $fileName);

        return [
            'type' => 'image',
            'path' => $originalPath,
            'url' => Storage::url($originalPath),
            'thumbnails' => $thumbnails,
            'original_name' => $originalName,
            'size' => $size,
            'metadata' => [
                'width' => null,
                'height' => null,
                'dimensions' => $this->getImageDimensions($file)
            ]
        ];
    }

    /**
     * Handle regular file upload
     */
    protected function handleFileUpload($file, string $storagePath, string $fileName, string $originalName, int $size): array
    {
        $filePath = $file->storeAs($storagePath, $fileName, 'public');

        return [
            'type' => 'file',
            'path' => $filePath,
            'url' => Storage::url($filePath),
            'original_name' => $originalName,
            'size' => $size,
            'extension' => $file->getClientOriginalExtension()
        ];
    }

    /**
     * Create image thumbnails
     */
    protected function createThumbnails($file, string $storagePath, string $fileName): array
    {
        $thumbnails = [];
        $sizes = [
            'small' => 150,
            'medium' => 300,
            'large' => 600
        ];

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getPathname());

            foreach ($sizes as $sizeName => $maxDimension) {
                // Proportional resize
                $resized = $image->scale(width: $maxDimension);
                
                $thumbnailPath = $storagePath . "/thumbnails/{$sizeName}";
                $thumbnailFileName = $sizeName . '_' . $fileName;
                
                // Create directory if not exists
                Storage::disk('public')->makeDirectory($thumbnailPath);
                
                // Save thumbnail
                $fullThumbnailPath = storage_path('app/public/' . $thumbnailPath . '/' . $thumbnailFileName);
                $resized->save($fullThumbnailPath);

                $thumbnails[$sizeName] = [
                    'path' => $thumbnailPath . '/' . $thumbnailFileName,
                    'url' => Storage::url($thumbnailPath . '/' . $thumbnailFileName)
                ];
            }
        } catch (\Exception $e) {
            // Log error but don't fail the upload
            \Log::warning("Thumbnail generation failed: " . $e->getMessage());
        }

        return $thumbnails;
    }

    /**
     * Validate uploaded file
     */
    protected function validateFile($file, string $extension, int $size): void
    {
        // Check if extension is allowed
        $allowedTypes = array_merge($this->allowedImageTypes, $this->allowedFileTypes);
        if (!in_array($extension, $allowedTypes)) {
            throw new \Exception("File type '{$extension}' not allowed. Allowed types: " . implode(', ', $allowedTypes));
        }

        // Check file size
        $maxSize = $this->isImage($extension) ? $this->maxImageSize : $this->maxFileSize;
        if ($size > $maxSize) {
            $maxMB = round($maxSize / (1024 * 1024), 1);
            throw new \Exception("File size too large. Maximum allowed: {$maxMB}MB");
        }

        // Additional security check
        if (!$file->isValid()) {
            throw new \Exception("Invalid file upload");
        }
    }

    /**
     * Check if file is an image
     */
    protected function isImage(string $extension): bool
    {
        return in_array($extension, $this->allowedImageTypes);
    }

    /**
     * Generate unique filename
     */
    protected function generateFileName(string $originalName, string $extension): string
    {
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $safeFileName = Str::slug($name);
        $timestamp = now()->format('YmdHis');
        $random = Str::random(6);
        
        return "{$safeFileName}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Get image dimensions
     */
    protected function getImageDimensions($file): ?array
    {
        try {
            $imageInfo = getimagesize($file->getPathname());
            return [
                'width' => $imageInfo[0] ?? null,
                'height' => $imageInfo[1] ?? null
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Delete uploaded file and its thumbnails
     */
    public function deleteFile(string $filePath): bool
    {
        try {
            // Delete original file
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            // Delete thumbnails if it's an image
            $pathInfo = pathinfo($filePath);
            $thumbnailsPath = $pathInfo['dirname'] . '/thumbnails';
            
            if (Storage::disk('public')->exists($thumbnailsPath)) {
                Storage::disk('public')->deleteDirectory($thumbnailsPath);
            }

            return true;
        } catch (\Exception $e) {
            \Log::error("File deletion failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cleanup uploaded files (for rollback)
     */
    protected function cleanupUploadedFiles(array $uploadedFiles): void
    {
        foreach ($uploadedFiles as $fileInfo) {
            $this->deleteFile($fileInfo['path']);
        }
    }

    /**
     * Get file info for API response
     */
    public function getFileInfo(string $filePath): ?array
    {
        if (!Storage::disk('public')->exists($filePath)) {
            return null;
        }

        $size = Storage::disk('public')->size($filePath);
        $pathInfo = pathinfo($filePath);
        
        $info = [
            'path' => $filePath,
            'url' => Storage::url($filePath),
            'size' => $size,
            'extension' => $pathInfo['extension'] ?? '',
            'name' => $pathInfo['basename'] ?? ''
        ];

        // Add thumbnail info if image
        if ($this->isImage($pathInfo['extension'] ?? '')) {
            $info['type'] = 'image';
            $info['thumbnails'] = $this->getThumbnailUrls($filePath);
        } else {
            $info['type'] = 'file';
        }

        return $info;
    }

    /**
     * Get thumbnail URLs for an image
     */
    protected function getThumbnailUrls(string $originalPath): array
    {
        $pathInfo = pathinfo($originalPath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['basename'];
        
        $thumbnails = [];
        $sizes = ['small', 'medium', 'large'];

        foreach ($sizes as $size) {
            $thumbnailPath = $directory . "/thumbnails/{$size}/{$size}_{$filename}";
            if (Storage::disk('public')->exists($thumbnailPath)) {
                $thumbnails[$size] = Storage::url($thumbnailPath);
            }
        }

        return $thumbnails;
    }
}
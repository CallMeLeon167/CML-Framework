<?php

namespace CML\Classes;

/**
 * Class FileUploader
 * 
 * A class for handling file uploads.
 */
class FileUploader
{
    use Functions\Functions;

    /**
     * Size constants
     */
    const MB = 1048576; // 1 MB in bytes
    const GB = 1073741824; // 1 GB in bytes

    /**
     * @var string The directory where uploaded files will be stored.
     */
    protected $uploadDirectory;

    /**
     * @var array The allowed file extensions.
     */
    protected $allowedExtensions;

    /**
     * @var int The maximum file size allowed in bytes.
     */
    protected $maxFileSize;

    /**
     * @var array Error messages with their corresponding IDs.
     */
    protected $errorMessages = [
        'FILE_NOT_UPLOADED' => 'No file uploaded with name %s',
        'UPLOAD_ERROR' => 'Upload error: %s',
        'FILE_SIZE_EXCEEDED' => 'File size exceeds the maximum allowed size: %s',
        'FILE_EXTENSION_NOT_ALLOWED' => "File extension '%s' is not allowed. Allowed extensions: %s",
        'MOVE_FAILED' => 'Failed to move uploaded file',
        'INVALID_UPLOAD_DIRECTORY' => 'Invalid upload directory: %s',
        'UPLOAD_DIRECTORY_NOT_WRITABLE' => 'Upload directory is not writable: %s',
        'FILE_ALREADY_EXISTS' => 'A file with the name %s already exists',
        'EMPTY_FILE' => 'The uploaded file is empty',
        'PARTIAL_UPLOAD' => 'The file was only partially uploaded',
        'NO_TMP_DIR' => 'Missing a temporary folder for file upload',
        'CANT_WRITE_FILE' => 'Failed to write file to disk',
        'PHP_EXTENSION_STOPPED_UPLOAD' => 'A PHP extension stopped the file upload',
    ];

    /**
     * FileUploader constructor.
     *
     * @param string $uploadDirectory The directory where uploaded files will be stored.
     * @param array $allowedExtensions The allowed file extensions.
     * @param int $maxFileSize The maximum file size allowed in bytes. Default is 5MB.
     * @param array $customErrorMessages Optional custom error messages to override defaults.
     * @throws \InvalidArgumentException If the upload directory is invalid or not writable.
     */
    public function __construct($uploadDirectory, $allowedExtensions = [], $maxFileSize = 5 * self::MB, $customErrorMessages = [])
    {
        $this->uploadDirectory = $this->getRootPath($uploadDirectory) . '/';
        if (!is_dir($this->uploadDirectory)) {
            throw new \InvalidArgumentException(sprintf($this->errorMessages['INVALID_UPLOAD_DIRECTORY'], $this->uploadDirectory));
        }
        if (!is_writable($this->uploadDirectory)) {
            throw new \InvalidArgumentException(sprintf($this->errorMessages['UPLOAD_DIRECTORY_NOT_WRITABLE'], $this->uploadDirectory));
        }
        $this->allowedExtensions = $allowedExtensions;
        $this->maxFileSize = $maxFileSize;
        $this->errorMessages = array_merge($this->errorMessages, $customErrorMessages);
    }

    /**
     * Uploads a file.
     *
     * @param string $fileInputName The name of the file input field.
     * @param string|null $customFilename Optional custom filename (without extension).
     * @return array An array containing the upload status, errors (if any), and filename.
     */
    public function upload(string $fileInputName, $customFilename = null)
    {
        $status = true;
        $errors = [];
        $filename = '';
        if (!isset($_FILES[$fileInputName])) {
            $status = false;
            $errors['FILE_NOT_UPLOADED'] = sprintf($this->errorMessages['FILE_NOT_UPLOADED'], $fileInputName);
        } else {
            $file = $_FILES[$fileInputName];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $status = false;
                $errors['UPLOAD_ERROR'] = sprintf($this->errorMessages['UPLOAD_ERROR'], $this->getUploadErrorMessage($file['error']));
            } else {
                if ($file['size'] == 0) {
                    $status = false;
                    $errors['EMPTY_FILE'] = $this->errorMessages['EMPTY_FILE'];
                }

                $sizeError = $this->validateFileSize($file['size']);
                if ($sizeError !== '') {
                    $status = false;
                    $errors['FILE_SIZE_EXCEEDED'] = $sizeError;
                }

                $extensionError = $this->validateFileExtension($file['name']);
                if ($extensionError !== '') {
                    $status = false;
                    $errors['FILE_EXTENSION_NOT_ALLOWED'] = $extensionError;
                }

                if ($status) {
                    $filename = $this->generateFilename($file['name'], $customFilename);
                    $destination = $this->uploadDirectory . $filename;

                    if (file_exists($destination)) {
                        $status = false;
                        $errors['FILE_ALREADY_EXISTS'] = sprintf($this->errorMessages['FILE_ALREADY_EXISTS'], $filename);
                    } elseif (!move_uploaded_file($file['tmp_name'], $destination)) {
                        $status = false;
                        $errors['MOVE_FAILED'] = $this->errorMessages['MOVE_FAILED'];
                    }
                }
            }
        }

        return [
            'status' => $status,
            'errors' => $errors,
            'filename' => $filename,
        ];
    }

    /**
     * Validates the file size.
     *
     * @param int $fileSize The size of the file in bytes.
     * @return string The error message if the file size exceeds the maximum allowed size, otherwise an empty string.
     */
    protected function validateFileSize($fileSize)
    {
        if ($fileSize > $this->maxFileSize) {
            return sprintf($this->errorMessages['FILE_SIZE_EXCEEDED'], $this->formatFileSize($this->maxFileSize));
        }
        return '';
    }

    /**
     * Formats a file size in bytes to a human-readable format.
     *
     * @param int $bytes The file size in bytes.
     * @return string The formatted file size.
     */
    protected function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Validates the file extension.
     *
     * @param string $filename The name of the file.
     * @return string The error message if the file extension is not allowed, otherwise an empty string.
     */
    protected function validateFileExtension($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!empty($this->allowedExtensions) && !in_array($extension, $this->allowedExtensions)) {
            return sprintf($this->errorMessages['FILE_EXTENSION_NOT_ALLOWED'], $extension, implode(', ', $this->allowedExtensions));
        }
        return '';
    }

    /**
     * Generates a filename, using a custom name if provided.
     *
     * @param string $originalFilename The original filename.
     * @param string|null $customFilename Optional custom filename (without extension).
     * @return string The generated filename.
     */
    protected function generateFilename($originalFilename, $customFilename = null)
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);

        if ($customFilename !== null) {
            $safeCustomFilename = $this->sanitizeFilename($customFilename);
            return $safeCustomFilename . '.' . $extension;
        } else {
            return $this->generateUniqueFilename($originalFilename);
        }
    }

    /**
     * Generates a unique filename.
     *
     * @param string $originalFilename The original filename.
     * @return string The generated unique filename.
     */
    protected function generateUniqueFilename($originalFilename)
    {
        $filename = pathinfo($originalFilename, PATHINFO_FILENAME);
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        return $filename . '_' . uniqid() . '.' . $extension;
    }

    /**
     * Sanitizes a filename to make it safe for use.
     *
     * @param string $filename The filename to sanitize.
     * @return string The sanitized filename.
     */
    protected function sanitizeFilename($filename)
    {
        // Remove any character that isn't a word character, a space, or a hyphen
        $filename = preg_replace('/[^\w\s-]/', '', $filename);
        // Replace multiple spaces or hyphens with a single hyphen
        $filename = preg_replace('/[\s-]+/', '-', $filename);
        // Trim hyphens from the beginning and end of the filename
        return trim($filename, '-');
    }

    /**
     * Gets the error message for a specific upload error code.
     *
     * @param int $errorCode The upload error code.
     * @return string The error message.
     */
    protected function getUploadErrorMessage($errorCode)
    {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => $this->errorMessages['FILE_SIZE_EXCEEDED'],
            UPLOAD_ERR_FORM_SIZE => $this->errorMessages['FILE_SIZE_EXCEEDED'],
            UPLOAD_ERR_PARTIAL => $this->errorMessages['PARTIAL_UPLOAD'],
            UPLOAD_ERR_NO_FILE => $this->errorMessages['FILE_NOT_UPLOADED'],
            UPLOAD_ERR_NO_TMP_DIR => $this->errorMessages['NO_TMP_DIR'],
            UPLOAD_ERR_CANT_WRITE => $this->errorMessages['CANT_WRITE_FILE'],
            UPLOAD_ERR_EXTENSION => $this->errorMessages['PHP_EXTENSION_STOPPED_UPLOAD'],
        ];

        return $errorMessages[$errorCode] ?? "Unknown upload error";
    }

    /**
     * Sets custom error messages.
     *
     * @param array $customErrorMessages An array of custom error messages.
     */
    public function setCustomErrorMessages($customErrorMessages)
    {
        $this->errorMessages = array_merge($this->errorMessages, $customErrorMessages);
    }
}

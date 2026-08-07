<?php
namespace Neo\Core;

use Intervention\Image\ImageManagerStatic as Image;

class Upload
{
    protected $file;
    protected $error;
    protected $result = [];

    /**
     * Initialize upload with file data
     * @param array $file $_FILES['field']
     */
    public function __construct($file)
    {
        $this->file = $file;

        // Basic Error Checking
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            $this->error = "No file uploaded.";
        } else if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->error = $this->codeToMessage($file['error']);
        }
    }

    /**
     * Save/Process file
     * @param string $destinationDir Absolute path
     * @param array $options Manipulation options
     */
    public function save($destinationDir, $options = [])
    {
        if ($this->error) {
            return false;
        }

        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        // 1. Validation (Secure Mime Type Check)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($this->file['tmp_name']);

        if (!str_starts_with($mime, 'image/')) {
            $this->error = "Invalid file type. Only images are allowed.";
            return false;
        }

        // 2. Generate Safe Name
        $ext = pathinfo($this->file['name'], PATHINFO_EXTENSION);
        $newName = uniqid('img_') . '.' . $ext;
        $targetPath = $destinationDir . '/' . $newName;

        try {
            // 3. Image Manipulation (Intervention)
            if (isset($options['image_resize']) && $options['image_resize']) {
                $img = Image::make($this->file['tmp_name']);

                $width = $options['image_x'] ?? null;
                $height = $options['image_y'] ?? null;

                $img->resize($width, $height, function ($constraint) use ($options) {
                    if (isset($options['image_ratio_y']) && $options['image_ratio_y']) {
                        $constraint->aspectRatio();
                    }
                });

                $img->save($targetPath);
            } else {
                // Just Move
                if (!move_uploaded_file($this->file['tmp_name'], $targetPath)) {
                    $this->error = "Failed to move uploaded file.";
                    return false;
                }
            }

            $this->result = [
                'name' => $newName,
                'path' => $targetPath,
                'mime' => $mime
            ];
            return true;

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getError()
    {
        return $this->error;
    }

    private function codeToMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                return "The uploaded file exceeds the upload_max_filesize directive in php.ini";
            case UPLOAD_ERR_FORM_SIZE:
                return "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
            case UPLOAD_ERR_PARTIAL:
                return "The uploaded file was only partially uploaded";
            case UPLOAD_ERR_NO_FILE:
                return "No file was uploaded";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Missing a temporary folder";
            case UPLOAD_ERR_CANT_WRITE:
                return "Failed to write file to disk";
            case UPLOAD_ERR_EXTENSION:
                return "File upload stopped by extension";
            default:
                return "Unknown upload error";
        }
    }
}

<?php

    namespace QCubed\Plugin;

    use Exception;
    use GdImage;

    /**
     * Class FileHandler
     *
     * Note: the "upload" folder must already exist in /project/assets/ and this folder has 777 permissions.
     *
     * @property string $RootPath Default root path APP_UPLOADS_DIR. You may change the location of the file repository
     *                             at your own risk.*
     * Note: If you want to change TempPath, TempUrl and StoragePath, you have to rewrite the setup() function in the FileUpload class.
     * This class is located in the /project/includes/plugins folder.
     * @property string $TempPath = Default temp path APP_UPLOADS_TEMP_DIR. If necessary, the temp dir must be specified.
     * @property string $StoragePath Default dir named _files. This dir is generated together with the dirs
     *                               /thumbnail, /medium, /large when the corresponding page is opened for the first time.
     * @property string $FullStoragePath Please a see the setup() function! This can only be changed in this function.
     *
     * @property integer $ThumbnailResizeDimensions Default resized image dimensions. Default 320 is a good balance between
     *                                              visible quality and file size.
     * @property integer $MediumResizeDimensions Default 480. Resize image dimensions for high-density (retina) screens.
     *                                           This allows you to serve higher quality images for HiDPI screens, at the
     *                                           cost of slightly larger file size. For example, generated for site preview.
     * @property integer $LargeResizeDimensions Default 1500. Resize image dimensions for high-density (retina) screens.
     *                                          This allows you to serve higher quality images for HiDPI (e.g., 27- and 30-inch
     *                                          monitors) screens, at the cost of slightly larger file size.*
     *
     * @property integer $ImageResizeQuality Default 90. JPG compression level for resized images.
     *
     * @property integer $PngLevel Default 6. PNG compression level for resized images.
     *                               Acceptable values are 0 (no compression, largest file size, fastest write)
     *                              through 9 (maximum compression, smallest file size, slowest write).
     *                              PNG is a lossless format, so compression does not reduce image quality,
     *                              but higher compression values mean smaller files at the cost of performance.
     *                              The PHP default is 6, which usually gives a good balance between file size and speed.
     *
     *                              Use a lower value like 0–3 if you prioritize faster image generation
     *                              and can accept larger files (e.g., for temporary images, internal use, or very fast servers).
     *                              Use a higher value like 7–9 if disk space or network transfer size is more important,
     *                              and slower save speed is acceptable (e.g., for long-term storage or many images).
     *                              In most web projects, a value between 5 and 7 is recommended.
     *
     * @property string $ImageResizeFunction Default 'imagecopyresampled'. Choose between 'imagecopyresampled' (smoother)
     *                                       and 'imagecopyresized' (faster). The difference is minimal, but you could use
     *                                       imagecopyresized, for example, if you want faster resizing when not using image
     *                                       resize cache.
     * @property boolean $ImageResizeSharpen Default true. Creates sharper (less blurry) preview images.
     * @property array $TempFolders Default '['thumbnail', 'medium', 'large']'. If you want to change the names of
     *                              the temporary folders, you need to override the setup() function of the FileUpload class
     *                              ($strCreateDirs = ['/thumbnail', '/medium', '/large'];).
     *
     * @property array $ResizeDimensions Default '[320, 480, 1500]'. Note: Here you need to set the ResizeDimensions
     *                                   [320, 480, 1500] in the order of TempFolders ['thumbnail', 'medium', 'large'].
     *
     *                                   Default 320 is a good balance between visible quality and file size.
     *
     *                                   Default 480. Resize image dimensions for high-density (retina) screens. This allows
     *                                   you to serve higher quality images for HiDPI screens, at the cost of slightly
     *                                   larger file size. For example, generated for site preview.
     *
     *                                   Default 1500. Resize image dimensions for high-density (retina) screens. This allows
     *                                   you to serve higher quality images for HiDPI (e.g., 27- and 30-inch monitors) screens,
     *                                   at the cost of slightly larger file size.
     *
     * @property array $AcceptFileTypes Default null. The output form of the array looks like this:
     *                                  '['gif', 'jpg', 'jpeg', 'png', 'pdf']'. If necessary, specify the allowed file types.
     *                                  When empty (default), all file types are allowed.
     * @property integer $MaxFileSize Default null. Sets the maximum file size (bytes) allowed for uploads. Default value null
     *                                it means no limit, but the maximum file size will always be limited by your server's
     *                                PHP upload_max_filesize value.
     *
     * @property integer $MinFileSize Default 1. If necessary, you can limit the minimum bytes of the uploaded image in order
     *                                not to degrade the quality of the image processing.
     * @property string $UploadExists Default 'increment'. Decides what to do if the uploaded filename already exists in upload
     *                                target folder. Default 'increment' will rename uploaded files by appending a number,
     *                                'overwrite' will overwrite existing files.
     *                                Usage:
     *                                $this->UploadExists = 'increment'; // increment filename, for example, filename.jpg => filename-2.jpg
     *                                $this->UploadExists = 'overwrite', // overwrite an existing file if the filename exists
     *
     * @property-read string $FileName is the name of the file that the user uploads?
     * @property-read string $FileType is the MIME type of the file?
     * @property-read integer $FileSize is the size in bytes of the file?
     * @property string $DestinationPath Default null. This is a prepared option. If there is a need to create new subfolders
     *                                   and save images there. Then you need to make your own function to create new folders.
     *                                   For example,
     *                                   [folder1]
     *                                   |___ [folder2]
     *                                        |___ [folder3]
     *                                   Then write $this->DestinationPath = 'folder1/folder2/folder3' etc...
     *
     * @package QCubed\Plugin
     */

    class VauuFileHandler
    {
        protected array $options;
        // PHP File Upload error message codes:
        // https://www.php.net/manual/en/features.file-upload.errors.php
        protected array $uploadErrors;
        protected string $chunkEnabled;
        protected int $index;
        protected string $chunk;
        protected int $count;
        protected int $counter = 0;

        /**
         * Constructor method for initializing upload options and handling file uploads.
         *
         * @param array|null $options An optional array to override default options. Example keys include:
         *                             - 'RootPath': Directory where files are stored.
         *                             - 'TempPath': Directory where temporary files are stored.
         *                             - 'StoragePath': Subdirectory for file storage.
         *                             - 'ImageResizeQuality': Quality of resized images.
         *                             - 'PngLevel Default 6. PNG compression level for resized images.
         *                             - 'ImageResizeFunction': Function used for an image resizing.
         *                             - 'AcceptFileTypes': Allowed file types for uploads.
         *                             - 'MaxFileSize': Maximum file size for uploads.
         *                             - 'MinFileSize': Minimum file size for uploads.
         *                             - Other configuration options related to uploads.
         *
         * @return void
         * @throws Exception
         */
        public function __construct(?array $options = null)
        {
            $this->options = array(
                'RootPath' => APP_UPLOADS_DIR,
                'TempPath' => APP_UPLOADS_TEMP_DIR,
                'StoragePath' => '_files',
                'FullStoragePath' => null,
                'ChunkPath' => null,

                'ImageResizeQuality' => 90,
                'PngLevel' => 6,
                'ImageResizeFunction' => 'imagecopyresampled', // imagecopyresampled || imagecopyresized
                'ImageResizeSharpen' => true,

                'TempFolders' =>  ['thumbnail', 'medium', 'large'],
                'ResizeDimensions' => [320, 480, 1500],
                'DestinationPath' => null,
                'AcceptFileTypes' => null,
                'MaxFileSize' => null,
                'MinFileSize' => 1,
                'UploadExists' => 'increment', // increment || overwrite

                'File' => null,
                'FileName' => null,
                'FileType' => null,
                'FileSize' => null,
                'FileError' => null,
            );

            $this->uploadErrors = array(
                1 => t('Uploaded file exceeds upload_max_filesize directive in php.ini'),
                2 => t('An uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form'),
                3 => t('The uploaded file was only partially uploaded'),
                4 => t('Failed to move an uploaded file'),
                6 => t('Missing a temporary folder'),
                7 => t('Failed to write a file to disk'),
                8 => t('A PHP extension stopped the file upload'),
                //'post_max_size' => t('The uploaded file exceeds the post_max_size directive in php.ini'),
                'max_file_size' => 'File is too big',
                'min_file_size' => 'File is too small',
                'accept_file_types' => t('Filetype not allowed'),
                'invalid_image_type' => t('Invalid image type'),
                'invalid_file_size' => t('Invalid file size'),
                'post_max_size' => t('File size exceeds max filesize %s'),
                'overwritten' => t('This file has been overwritten'),
                'invalid_image' => t('Invalid image / failed getimagesize()'),
                'failed_to_resize_image' => t('Failed to resize image'),
                'resizeimage_failed_to_create_and_resize_the_image' => t('The resizeImage() function failed to create and resize the image'),
                'invalid_chunk_size' => t('Invalid chunk size'),
                'failed_to_open_stream' => t('Failed to open stream: No such directory to put into'),
                'could-not_write_output' => t('Failed to open the output stream'),
                'could_not_read_input' => t('Failed to open the input stream'),
                'failed_to_move_uploaded_file' => t('Failed to move an uploaded file'),
                'file_not_found' => t('File not found')
            );

            if ($options) {
                $this->options = array_merge($this->options, $options);
            }

            $this->options['FullStoragePath'] = $this->options['TempPath'] . '/' . $this->options['StoragePath'];
            $this->options['ChunkPath'] = $this->options['FullStoragePath'] . '/' . 'temp';

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $this->header();
                $this->handleFileUpload();
            }
        }

        /**
         * Sends HTTP headers to prevent caching and define the content type as JSON.
         *
         * This method ensures that the client does not cache the response by setting
         * appropriate headers. It also specifies the content type of the response
         * as JSON.
         *
         * @return void
         */
        protected function header(): void
        {
            // Make sure a file is not cached (as it happens, for example, on iOS devices)
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            header('Content-Type: application/json');
        }

        /**
         * Handles a file upload process, including regular and chunked uploads, and validates the uploaded file.
         *
         * This method processes uploaded files by checking for chunked uploads,
         * validating file attributes, detecting duplicate file names, and saving
         * the file to the specified destination. It supports features such as file
         * name incrementation and error handling.
         *
         * - The method determines if the upload is chunk-enabled and processes accordingly.
         * - For regular uploads, it validates the file, handles duplicate filenames, and uploads the file.
         * - For chunked uploads, it processes and saves the file in parts.
         *
         * @return void
         * @throws Exception
         */
        public function handleFileUpload(): void
        {
            $this->chunkEnabled = $_REQUEST['chunkEnabled'] ?? "false";

            $this->index = intval($_REQUEST['index']) ?? 0;
            $this->chunk = intval($_REQUEST['chunk']) ?? 0;
            $this->count = intval($_REQUEST['count']) ?? 0;

            $this->options['FileName'] = $this->options['RootPath'] . '/' . $_FILES["files"]["name"];
            $this->options['File'] = $_FILES["files"]["tmp_name"];
            $this->options['FileType'] = $_FILES["files"]["type"];
            $this->options['FileSize'] = $_FILES["files"]["size"];
            $this->options['FileError'] = $_FILES["files"]["error"];

            // If DestinationPath is set
            if ($this->options['DestinationPath'] !== null) {
                $this->options['FileName'] = $this->options['RootPath'] . $this->options['DestinationPath'] . '/' . basename($this->options['FileName']);
            }

            if ($this->chunkEnabled === "false") {
                // Check for duplicate filenames and increment if necessary
                $newFileName = $this->checkDuplicateFile($this->options['FileName']);

                // Make sure the new file name is received and then validate the file
                if ($newFileName !== null) {
                    $this->options['FileName'] = $newFileName;
                }

                // Validate the file with the updated filename
                if ($this->regularValidate($this->options['File'], $this->options['FileName'], $this->options['FileError'])) {
                    // Upload a file with a new name
                    $this->handleRegularUpload($this->options['File'], $this->options['FileName']);
                }
            } else {
                $this->handleChunkUpload($this->options['File'], $this->options['FileName']);
            }
        }

        /**
         * Validates an uploaded file against defined size and type constraints.
         *
         * @param mixed $uploadedFile The uploaded file data to be validated.
         * @param string $fileName The name of the uploaded file.
         * @param int $error Error code associated with the file upload process.
         *
         * @return bool|null Returns true if the file passes all validation checks, false otherwise.
         */
        public function regularValidate(mixed $uploadedFile, string $fileName, int $error): ?bool
        {
            if ($error) {
                $this->handleError($this->getErrorMessage($error), $fileName);
            }

            // Get the value of post_max_size in bytes
            $postMaxSize = $this->getConfigBytes(ini_get('post_max_size'));

            // Check if the file size exceeds the post_max_size limit
            if ($postMaxSize && ($_SERVER['CONTENT_LENGTH'] > $postMaxSize)) {
                $this->handleError($this->getErrorMessage('post_max_size'), $fileName);
            }

            // Check file size
            $fileSize = $this->getFileSize($uploadedFile);

            if (!empty($this->options['MaxFileSize']) && $fileSize > $this->options['MaxFileSize']) {
                $this->handleError($this->getErrorMessage('max_file_size'), $fileName);
            }

            if (!empty($this->options['MinFileSize']) && $fileSize < $this->options['MinFileSize']) {
                $this->handleError($this->getErrorMessage('min_file_size'), $fileName);
            }

            if (!empty($this->options['AcceptFileTypes'])) {
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if (!in_array($ext, $this->options['AcceptFileTypes'], true)) {
                    $this->handleError($this->getErrorMessage('accept_file_types'), $fileName);
                }
            }

            return true;
        }

        /**
         * Validates a file chunk or the final merged file based on various conditions such as file size, type, or errors.
         *
         * @param mixed $uploadedFile The currently uploaded chunk file being validated.
         * @param string $fileName The name or path of the file being processed.
         * @param int $error An error code indicating issues with the file upload process.
         * @param bool $isLastChunk Indicates if this is the last chunk of the file being uploaded.
         *
         * @return bool|null Returns true if the validation is successful, null if no validation is performed, or throws an error otherwise.
         * @throws Exception
         */

        public function chunkValidate(mixed $uploadedFile, string $fileName, int $error, bool $isLastChunk): ?bool
        {
            if ($error) {
                $this->handleError($this->getErrorMessage($error), $fileName);
            }

            // If this is the last piece, we can check the size and type of the final file
            if ($isLastChunk) {
                $fileSize = $this->getFileSize($fileName);

                if (!empty($this->options['MaxFileSize']) && $fileSize > $this->options['MaxFileSize']) {
                    $this->handleError($this->getErrorMessage('max_file_size'), $fileName);
                }

                if (!empty($this->options['MinFileSize']) && $fileSize < $this->options['MinFileSize']) {
                    $this->handleError($this->getErrorMessage('min_file_size'), $fileName);
                }

                if (!empty($this->options['AcceptFileTypes'])) {
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    if (!in_array($ext, $this->options['AcceptFileTypes'], true)) {
                        $this->handleError($this->getErrorMessage('accept_file_types'), $fileName);
                    }
                }
            }

            // If you want to check anything about the chunk itself (like the chunk size), add that logic here.
            // Currently, the size or type of regular chunks is not checked - only after all the chunks are merged.
            return true;
        }

        /**
         * Handles the upload of a regular file by moving it to the target location,
         * clearing the file status cache, performing any necessary image resizing, and
         * updating relevant upload information.
         *
         * @param string $uploadedFile The temporary file path of the uploaded file.
         * @param string $file The target file path where the uploaded file will be moved.
         *
         * @return void
         * @throws Exception
         */
        protected function handleRegularUpload(string $uploadedFile, string $file): void
        {
            move_uploaded_file($uploadedFile, $file);

            clearstatcache();

            $this->resizeImageProcess($file);
            $this->uploadInfo();
        }

        /**
         * Handles the uploading and processing of file chunks, merging them into a complete file.
         *
         * @param string $uploadedFile The path to the currently uploaded chunk file.
         * @param string $file The full path of the final intended file after all chunks are merged.
         *
         * @return void
         * @throws Exception
         */
        protected function handleChunkUpload(string $uploadedFile, string $file): void
        {
            $chunkFile = $this->options['ChunkPath'] . '/' . basename($file);

            // Move the file to a temporary location
            if (!move_uploaded_file($uploadedFile, $chunkFile . '.part' . $this->chunk)) {
                $this->handleError($this->getErrorMessage('failed_to_move_uploaded_file'), $file);
                return;
            }

            clearstatcache();

            $filePath = $chunkFile . '.part*';
            $fileParts = glob($filePath);
            sort($fileParts, SORT_NATURAL);
            $_SESSION['parts'] = $fileParts; // We keep the parts in the session

            // Merge chunks
            $finalFile = fopen($chunkFile, 'wb');

            foreach ($fileParts as $filePart) {
                $chunk = file_get_contents($filePart);
                fwrite($finalFile, $chunk);
                $this->counter++;
            }

            fclose($finalFile);

            // When all parts are received
            if ($this->count == $this->counter) {

                $this->partFilesToDelete($_SESSION['parts']);

                // Final validation after merging files
                if ($this->chunkValidate($finalFile, $chunkFile , 0, true)) {

                    // If the filename already exists, check for duplicates
                    if (!file_exists($file)) {
                        rename($chunkFile, $file);
                        $this->options['FileSize'] = filesize($file);
                    } else {
                        $newFileName = $this->checkDuplicateFile($file);
                        rename($chunkFile, $newFileName);
                        $this->options['FileName'] = $newFileName;
                        $this->options['FileSize'] = filesize($newFileName);
                    }

                    // Further processing
                    $this->resizeImageProcess($this->options['FileName']);
                    $this->uploadInfo();
                }
            }
        }

        /**
         * Deletes temporary files created during the file chunk upload process and clears related session data.
         *
         * @param array $tempFiles An array of paths to the temporary files to be deleted.
         *
         * @return void
         */
        protected function partFilesToDelete(array $tempFiles): void
        {
            foreach ($tempFiles as $tempFile) {
                unlink($tempFile);
            }

            unset($_SESSION['parts']);
        }

        /**
         * Checks for duplicate files in the specified directory and handles them based on the upload configuration.
         *
         * If a file with the same name already exists, the behavior is determined by the 'UploadExists' option:
         * - If set to 'overwrite', the method overwrites the existing file.
         * - If set to 'increment', the method assigns a unique incremented filename to the new file.
         *
         * @param string $fileName The full path of the file to check for duplicates.
         *
         * @return string|null Returns the original or newly generated filename if a duplicate is handled,
         *                     or null if the file is overwritten.
         */
        protected function checkDuplicateFile(string $fileName): ?string
        {
            // Set dirname, name and ext
            $dirname = $this->removeFileName($fileName);
            $name = pathinfo($fileName, PATHINFO_FILENAME);
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);

            $isExists = false;
            $files = glob($dirname . '/*', GLOB_NOSORT);

            if (in_array($fileName, $files)) {
                $isExists = true;
            }

            if ($isExists === true) {
                if (file_exists($dirname . '/' . $name . '.' . $ext)) {

                    // If 'overwrite' is selected, the file will be overwritten
                    if ($this->options['UploadExists'] == 'overwrite') {
                        $this->handleError($this->getErrorMessage('overwritten'), $name);
                        return null;
                    }

                    // If 'increment' is selected, the filename is incremented
                    if ($this->options['UploadExists'] == 'increment') {
                        $inc = 1;
                        while (file_exists($dirname . '/' . $name . '-' . $inc . '.' . $ext)) {
                            $inc++;
                        }
                        return $dirname . '/' . $name . '-' . $inc . '.' . $ext;
                    }
                }

                return $fileName;
            }

            return null;
        }

        /**
         * Retrieves a user-friendly error message for a given error code or returns the original error if no message is available.
         *
         * @param mixed $error The error code or message to retrieve a corresponding user-friendly error message for.
         *
         * @return mixed
         */
        protected function getErrorMessage(mixed $error): mixed
        {
            return $this->uploadErrors[$error] ?? $error;
        }

        /**
         * Processes and resizes an image file based on specified dimensions for different temporary folders.
         *
         * @param string $fileName The path to the image file to be processed and resized.
         *
         * @return void
         * @throws Exception
         */

        protected function resizeImageProcess(string $fileName): void
        {
            $associatedParameters = array_combine($this->options['TempFolders'], $this->options['ResizeDimensions']);

            $ext = strtolower($this->getExtension($fileName));

            // Only "visually observable" images in temp folders
            if (is_file($fileName) && in_array($ext, $this->getImageExtensions(), true)) {
                // For SVG just copy to all temp folders
                if ($ext === 'svg') {
                    foreach ($associatedParameters as $tempFolder => $resizeDimension) {
                        if ($this->options['DestinationPath'] == null) {
                            $newPath = $this->options['FullStoragePath'] . '/' . $tempFolder . '/' . basename($fileName);
                        } else {
                            $newPath = $this->options['FullStoragePath'] . '/' . $tempFolder . '/' . $this->options['DestinationPath'] . '/' . basename($fileName);
                        }
                        copy($fileName, $newPath);
                    }
                    return;
                }

                // Raster image (jpeg/png/gif/bmp/webp): resize or copy depending on size
                $size = getimagesize($fileName);
                foreach ($associatedParameters as $tempFolder => $resizeDimension) {
                    if ($this->options['DestinationPath'] == null) {
                        $newPath = $this->options['FullStoragePath'] . '/' . $tempFolder . '/' . basename($fileName);
                    } else {
                        $newPath = $this->options['FullStoragePath'] . '/' . $tempFolder . '/' . $this->options['DestinationPath'] . '/' . basename($fileName);
                    }

                    if (($resizeDimension > $size[0]) && $size[0] !== 0) {
                        copy($fileName, $newPath);
                    } else {
                        $this->resizeImage($fileName, $newPath, $resizeDimension);
                    }
                }
            }
        }

        /**
         * Creates a new GD image resource from a file based on the specified image type.
         *
         * @param string $path The path to the image file to be loaded.
         * @param int $type The type of the image (e.g., IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, etc.).
         *
         * @return GdImage|false Returns a GD image resource on success or false on failure.
         */
        protected function imageCreateFrom(string $path, int $type): GdImage|false
        {
            if (empty($path) || empty($type)) return false;

            return match ($type) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($path),
                IMAGETYPE_PNG  => imagecreatefrompng($path),
                IMAGETYPE_GIF  => imagecreatefromgif($path),
                18             => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : false, // IMAGETYPE_WEBP
                IMAGETYPE_BMP  => function_exists('imagecreatefrombmp') ? imagecreatefrombmp($path) : false,
                default        => false,
            };
        }

        /**
         * Applies a sharpening filter to the given image using a convolution matrix.
         *
         * @param resource $image The image resource to be sharpened.
         *
         * @return void
         */
        protected function sharpenImage($image): void
        {
            $matrix = array(
                array(-1, -1, -1),
                array(-1, 20, -1),
                array(-1, -1, -1),
            );
            $divisor = array_sum(array_map('array_sum', $matrix));
            $offset = 0;
            imageconvolution($image, $matrix, $divisor, $offset);
        }

        /**
         * Resizes an image to the specified dimensions and saves the resized image to a new path.
         *
         * @param string $path The full path of the source image to be resized.
         * @param string $newPath The full path where the resized image will be saved.
         * @param int $resizeDimensions The maximum width or height of the resized image.
         *
         * @return void
         * @throws Exception If the image type is unsupported or an error occurs during processing.
         */
        protected function resizeImage(string $path, string $newPath, int $resizeDimensions): void
        {
            if (!function_exists('exif_imagetype') || exif_imagetype($path) === false) {
                $this->handleError($this->getErrorMessage('invalid_image'), $path);
                return;
            }

            $size = getimagesize($path);
            if (empty($size) || !is_array($size)) {
                $this->handleError($this->getErrorMessage('invalid_image'), $path);
                return;
            }

            $resizeRatio = max($size[0], $size[1]) / $resizeDimensions;

            $resizeWidth = round($size[0] / $resizeRatio);
            $resizeHeight = round($size[1] / $resizeRatio);

            $newImage = imagecreatetruecolor($resizeWidth, $resizeHeight);
            if ($newImage === false) {
                $this->handleError($this->getErrorMessage('failed_to_create_image'), $path);
                return;
            }

            $image = $this->imageCreateFrom($path, $size[2]);
            if ($image === false) {
                $this->handleError($this->getErrorMessage('failed_to_load_image'), $path);
                imagedestroy($newImage);
                return;
            }

            imageAlphaBlending($newImage, false);
            imageSaveAlpha($newImage, true);

            if (!call_user_func($this->options['ImageResizeFunction'], $newImage, $image, 0, 0, 0, 0, $resizeWidth, $resizeHeight, $size[0], $size[1])) {
                $this->handleError($this->getErrorMessage('failed_to_resize_image'), $path);
                imagedestroy($image);
                imagedestroy($newImage);
                return;
            }

            imagedestroy($image);

            if ($this->options['ImageResizeSharpen']) {
                $this->sharpenImage($newImage);
            }

            // Handling quality/processing
            $quality = isset($this->options['ImageResizeQuality']) ? (int)$this->options['ImageResizeQuality'] : null;

            switch ($size[2]) {
                case IMAGETYPE_JPEG:
                    // For JPEG, only the ImageResizeQuality value is used (default 90)
                    imagejpeg($newImage, $newPath, $quality ?? 90);
                    break;
                case IMAGETYPE_GIF:
                    // You cannot provide parameters for saving a GIF
                    imagegif($newImage, $newPath);
                    break;
                case IMAGETYPE_PNG:
                    // PNG compression level (0–9) is taken only from PngLevel, regardless of JPEG quality
                    $pngLevel = isset($this->options['PngLevel']) ? (int)$this->options['PngLevel'] : 6;
                    // Make sure the value is within the allowed range
                    $pngLevel = min(max($pngLevel, 0), 9);
                    imagepng($newImage, $newPath, $pngLevel);
                    break;
                default:
                    imagedestroy($newImage);
                    throw new Exception(t("Unable to deal with an image type"));
            }

            // destroy image
            imagedestroy($newImage);
        }

        /**
         * Outputs information about the uploaded file in JSON format.
         *
         * @return void
         */
        protected function uploadInfo(): void
        {
            print json_encode(array(
                'filename' =>  basename($this->options['FileName']),
                'path' => $this->getRelativePath($this->options['FileName']),
                'extension' => $this->getExtension($this->options['FileName']),
                'type' => $this->options['FileType'],
                'error' => $this->options['FileError'],
                'size' => $this->options['FileSize'],
                'mtime' => filemtime($this->options['FileName']),
                'dimensions' => $this->getDimensions($this->options['FileName'])
            ));
        }

        /**
         * Handles errors encountered during the file upload process, outputs an error response in JSON format,
         * and performs cleanup of temporary files if necessary.
         *
         * @param string $errorMessage The error message describing the issue.
         * @param string|null $file The path to the file associated with the error, if available.
         *
         * @return void
         */
        public function handleError(string $errorMessage, ?string $file = null): void
        {
            $json['filename'] = basename($file);
            $json['size'] = $this->options['FileSize'];
            $json['type'] = $this->options['FileType'];
            $json['error'] = $errorMessage;
            print json_encode($json);

            // Check and remove temporary files

            // If there is an error, delete the final file and all temporary parts
            if (!empty($this->options['ChunkPath']) && $file) {
                $chunkFile = $this->options['ChunkPath'] . '/' . basename($file);

                if ($errorMessage && file_exists($chunkFile)) {
                    unlink($chunkFile);
                }
            }

            // NB! If you want testability, don't use exit here,
            // just throw an Exception or return some value - this is already need-based.
            //exit;
        }

        /**
         * Fixes integer overflow for a given size by adjusting negative values caused by exceeding PHP's integer range.
         *
         * @param float|int $size The value to process, which might have overflowed into a negative integer.
         *
         * @return int|float The corrected size value after resolving the overflow.
         */
        protected function fixIntegerOverflow(float|int $size): float|int
        {
            if ($size < 0) {
                $size += 2.0 * (PHP_INT_MAX + 1);
            }
            return $size;
        }

        /**
         * Retrieves the size of a specified file, with an option to clear the filesystem cache beforehand.
         *
         * @param string $filePath The path to the file whose size is to be determined.
         * @param bool $clearStatCache Whether to clear the filesystem cache before getting the file size. Defaults to false.
         *
         * @return float|false|int The file size in bytes on success, or false on failure.
         */
        protected function getFileSize(string $filePath, ?bool $clearStatCache = false): float|false|int
        {
            if ($clearStatCache) {
                if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                    clearstatcache(true, $filePath);
                } else {
                    clearstatcache();
                }
            }
            return $this->fixIntegerOverflow(filesize($filePath));
        }

        /**
         * Converts a given value to its corresponding size in bytes.
         * The method supports plain numeric values or values with suffixes 'k', 'm', or 'g'
         * representing kilobytes, megabytes, and gigabytes, respectively.
         *
         * @param mixed $val The input value to be converted. It can be numeric or a string ending with a size suffix.
         *
         * @return int Returns the size in bytes as an integer.
         */
        public function getConfigBytes(mixed $val): int
        {
            $val = trim((string)$val);

            if (is_numeric($val)) {
                return (int)$val;
            }

            $last = strtolower($val[strlen($val)-1]);
            $num = (int)substr($val, 0, -1);

            return match ($last) {
                'g' => $num * 1024 * 1024 * 1024,
                'm' => $num * 1024 * 1024,
                'k' => $num * 1024,
                default => (int)$val,
            };
        }


        /**
         * Removes the file name from the given file path, returning the directory path.
         *
         * @param string $path The full file path from which the file name should be removed.
         *
         * @return string The directory path without the file name.
         */
        protected function removeFileName(string $path): string
        {
            return substr($path, 0, (int) strrpos($path, '/'));
        }

        /**
         * Retrieves the relative path of a file or directory based on the configured root path.
         *
         * @param string $path The absolute path to be converted into a relative path.
         *
         * @return string The relative path derived from the provided absolute path.
         */
        public function getRelativePath(string $path): string
        {
            return substr($path, strlen($this->options['RootPath']));
        }

        /**
         * Retrieves the extension of a given file path.
         *
         * @param string $path The file path from which to extract the extension.
         *
         * @return string|null The file extension in lowercase, or null if the path is not a valid file.
         */
        public static function getExtension(string $path): ?string
        {
            if(!is_dir($path) && is_file($path)){
                return strtolower(substr(strrchr($path, '.'), 1));
            }

            return null;
        }

        /**
         * Determines the MIME type of file at the specified path.
         *
         * @param string $path The file path for which the MIME type needs to be determined.
         *
         * @return string|false Returns the MIME type of the file as a string or false on failure.
         */
        public static function getMimeType(string $path): false|string
        {
            if(function_exists('mime_content_type')) {
                return mime_content_type($path);
            } else {
                return function_exists('finfo_file') ? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path) : false;
            }
        }

        /**
         * Retrieves the dimensions of an image file in the format "width x height".
         *
         * @param string $path The file path of the image whose dimensions need to be retrieved.
         *
         * @return string|null The dimensions of the image as a string in the format "width x height",
         *                     or null if the file is not an image or dimensions cannot be determined.
         */
        public static function getDimensions(string $path): ?string
        {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            if ($ext === 'svg') {
                // Finding SVG dimensions from XML
                if (!is_file($path)) {
                    return null;
                }
                $svg = @simplexml_load_file($path);
                if ($svg === false) {
                    return null;
                }

                $attributes = $svg->attributes();

                $width = isset($attributes->width) ? (string)$attributes->width : null;
                $height = isset($attributes->height) ? (string)$attributes->height : null;

                // If width/height is missing, take from viewBox
                if (!$width || !$height) {
                    if (isset($attributes->viewBox)) {
                        $viewBox = preg_split('/[\s,]+/', (string)$attributes->viewBox);
                        if (count($viewBox) === 4) {
                            $width  = $viewBox[2];
                            $height = $viewBox[3];
                        }
                    }
                }

                // Remove possible units (e.g. "100px" -> "100")
                if ($width !== null) {
                    $width = preg_replace('/[a-z%]+/i', '', $width);
                }
                if ($height !== null) {
                    $height = preg_replace('/[a-z%]+/i', '', $height);
                }

                if ($width && $height) {
                    return $width . ' x ' . $height;
                }

                return null;
            }

            // Other visual image (e.g. jpg, png, etc.)
            $imageSize = getimagesize($path);

            if ($imageSize && in_array($ext, self::getImageExtensions(), true)) {
                $width = ($imageSize[0] ?? '0');
                $height = ($imageSize[1] ?? '0');
                return $width . ' x ' . $height;
            }

            return null;
        }

        /**
         * Retrieves a list of supported image file extensions.
         *
         * @return array A list of image file extensions as strings.
         */
        public static function getImageExtensions(): array
        {
            return array('jpg', 'jpeg', 'bmp', 'png', 'webp', 'gif', 'svg');
        }

        /**
         * Converts a byte size into a human-readable format with appropriate units.
         *
         * @param float|int $bytes The size in bytes to be converted.
         *
         * @return string The formatted size with its respective unit (e.g., KB, MB, GB).
         */
        protected function readableBytes(float|int $bytes): string
        {
            $i = floor(log($bytes) / log(1024));
            $sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
            return sprintf('%.02F', $bytes / pow(1024, $i)) * 1 . ' ' . $sizes[$i];
        }

        /**
         * Cleans and normalizes a file path by trimming unwanted characters, removing unsafe sequences,
         * and standardizing directory separators.
         *
         * @param string $path The file path to be cleaned and normalized.
         *
         * @return string The cleaned and normalized file path.
         */
        public static function cleanPath(string $path): string
        {
            $path = trim($path);
            $path = trim($path, '\\/');
            $path = str_replace(array('../', '..\\'), '', $path);
            if ($path == '..') {
                $path = '';
            }
            return str_replace('\\', '/', $path);
        }

        /**
         * Renames a file or directory, moving it from the old path to the new path.
         *
         * @param string $old The current path of the file or directory to be renamed.
         * @param string $new The new path for the file or directory.
         *
         * @return bool|null Returns true if the rename was successful, false if it failed, or null if the conditions were not met.
         */
        protected function rename(string $old, string $new): ?bool
        {
            return (!file_exists($new) && file_exists($old)) ? rename($old, $new) : null;
        }
    }
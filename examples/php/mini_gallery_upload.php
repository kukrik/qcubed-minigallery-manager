<?php

    require_once('../qcubed.inc.php');
    require('../../src/VauuFileHandler.php');

    use QCubed\Exception\Caller;
    use QCubed\Exception\InvalidCast;
    use QCubed\Plugin\VauuFileHandler;
    use QCubed\QDateTime;

    $options = array(
        //'ImageResizeQuality' => 75, // Default 85
        //'ImageResizeFunction' => 'imagecopyresized', // Default imagecopyresampled
        //'ImageResizeSharpen' => false, // Default true
        //'TempFolders' => ['thumbnail', 'medium', 'large'], // Please read the FileHandler description and manual
        //'ResizeDimensions' => [320, 480, 1500], // Please read the FileHandler description and manual
        //'DestinationPath' => null, // Please read the FileHandler description and manual
        'AcceptFileTypes' => ['jpg', 'jpeg', 'bmp', 'png', 'webp', 'gif'], // Default null
        'DestinationPath' => !empty($_SESSION['folderPath']) ? $_SESSION['folderPath'] : null, // Default null
        //'MaxFileSize' => 1024 * 1024 * 2, // 2 MB // Default null
        //'MinFileSize' => 500000, // 500 kb // Default null
        //'UploadExists' => 'overwrite', // increment || overwrite Default 'increment'
    );

    /**
     * Class responsible for handling file operations specifically for MiniGallery,
     * extending the functionality of the VauuFileHandler.
     */
    class MiniGalleryFileHandler extends VauuFileHandler
    {
        /**
         * Handles the upload process and updates related information in the system.
         *
         * This method processes the upload by creating and saving file details and associated metadata,
         * including a folder, mini gallery, and content cover media information.
         * It ensures that the relevant files and folders are properly locked,
         * and their details are updated.
         *
         * @return void This method does not return a value.
         * @throws Caller
         * @throws InvalidCast
         */
        protected function uploadInfo(): void
        {
            parent::uploadInfo();

            if ($this->options['FileError'] == 0) {
                $objFile = new Files();
                $objFile->setFolderId($_SESSION['folderId']);
                $objFile->setName(basename($this->options['FileName']));
                $objFile->setPath($this->getRelativePath($this->options['FileName']));
                $objFile->setType("file");
                $objFile->setDescription(null);
                $objFile->setExtension($this->getExtension($this->options['FileName']));
                $objFile->setMimeType($this->getMimeType($this->options['FileName']));
                $objFile->setSize($this->options['FileSize']);
                $objFile->setMtime(filemtime($this->options['FileName']));
                $objFile->setDimensions($this->getDimensions($this->options['FileName']));
                $objFile->setWidth($this->getImageWidth($this->options['FileName']));
                $objFile->setHeight($this->getImageHeight($this->options['FileName']));
                $objFile->setLockedFile(1);
                $objFile->setActivitiesLocked(1);
                $objFile->save(true);

                $objMiniGallery = new MiniGallery();
                $objMiniGallery->setContentCoverMediaId($_SESSION['coverMediaId']);
                $objMiniGallery->setFolderId($_SESSION['folderId']);
                $objMiniGallery->setFileId($objFile->getId());
                $objMiniGallery->setName(basename($this->options['FileName']));
                $objMiniGallery->setPath($this->getRelativePath($this->options['FileName']));
                $objMiniGallery->setStatus(1);
                $objMiniGallery->setPostDate(QCubed\QDateTime::now());
                $objMiniGallery->save();
            }

            $objContentCoverMedia = ContentCoverMedia::loadById($_SESSION['coverMediaId']);
            $objContentCoverMedia->setPostUpdateDate(QDateTime::now());
            $objContentCoverMedia->save();

            $objFolder = Folders::loadById($_SESSION['folderId']);

            if ($objFolder->getLockedFile() == 0) {
                $objFolder->setLockedFile(1);
                $objFolder->save();
            }
        }

        /**
         * Get width of an image from a given file path
         *
         * @param string $path Path to the image file
         *
         * @return int|string Width of the image in pixels, or 0 if the width could not be determined
         */
        public static function getImageWidth(string $path): int|string
        {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $ImageSize = getimagesize($path);

            if (in_array($ext, self::getImageExtensions())) {
                return ($ImageSize[0] ?? '0');
            }

            return '0';
        }

        /**
         * Get the height of an image
         *
         * @param string $path The file path of the image
         *
         * @return int|string The height of the image in pixels, or 0 if the height could not be determined
         */
        public static function getImageHeight(string $path): int|string
        {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $ImageSize = getimagesize($path);

            if (in_array($ext, self::getImageExtensions())) {
                return ($ImageSize[1] ?? '0');
            }

            return '0';
        }

        /**
         * Retrieves the list of supported image file extensions.
         *
         * @return array An array of supported image file extensions.
         */
        public static function getImageExtensions(): array
        {
            return array('jpg', 'jpeg', 'bmp', 'png', 'webp', 'gif');
        }
    }

    try {
        $objHandler = new MiniGalleryFileHandler($options);
    } catch (Exception $e) {
        http_response_code(500);
        error_log('Upload handler creation error: ' . $e->getMessage());
        echo json_encode(['error' => 'Failed to start the file/s upload system.']);
        exit;
    }

















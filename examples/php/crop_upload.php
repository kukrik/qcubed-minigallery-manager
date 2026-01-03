<?php

    require_once('../qcubed.inc.php');

    use QCubed\Plugin\CroppieHandler;
    use QCubed\Exception\Caller;
    use QCubed\Exception\InvalidCast;

    $options = array(
        //'TempFolders' =>  ['thumbnail', 'medium', 'large'], // Please read the CroppieHandler description and manual
        'ResizeDimensions' => [320, 480, 800], // Please read the CroppieHandler description and manual
    );

    /**
     * Class CustomCroppieHandler
     *
     * This class extends CroppieHandler and overrides the uploadInfo method to perform additional functionality,
     * specifically for handling file uploads and associated metadata updates.
     */
    class CustomCroppieHandler extends CroppieHandler
    {
        /**
         * Processes and uploads file information based on the provided options.
         * It sets file properties such as name, type, path, and dimensions and saves the related folder updates if applicable.
         *
         * @return void
         * @throws Caller
         * @throws InvalidCast
         */
        protected function uploadInfo(): void
        {
            parent::uploadInfo();

            if ($this->options['OriginalImageName']) {

                $obj = new Files();
                $obj->setFolderId($this->options['FolderId']);
                $obj->setName(basename($this->options['OriginalImageName']));
                $obj->setType('file');
                $obj->setPath($this->getRelativePath($this->replaceDoubleSlashWithSlash($this->options['OriginalImageName'])));
                $obj->setDescription(null);
                $obj->setExtension($this->getExtension($this->options['OriginalImageName']));
                $obj->setMimeType($this->getMimeType($this->options['OriginalImageName']));
                $obj->setSize(filesize($this->options['OriginalImageName']));
                $obj->setMtime(filemtime($this->options['OriginalImageName']));
                $obj->setDimensions($this->getDimensions($this->options['OriginalImageName']));
                $obj->setWidth($this->getImageWidth($this->options['OriginalImageName']));
                $obj->setHeight($this->getImageHeight($this->options['OriginalImageName']));
                $obj->save(true);

                if ($this->options['FolderId']) {
                    $objFolder = Folders::loadById($this->options['FolderId']);

                    // Check if the folder exists before updating properties
                    if ($objFolder) {
                        $objFolder->setLockedFile(1);
                        $objFolder->setMtime(filemtime($this->options['OriginalImageName']));
                        $objFolder->save();
                    }
                }
            }
        }
    }

    try {
        $objHandler = new CustomCroppieHandler($options);
    } catch (Exception $e) {
        http_response_code(500);
        error_log('Upload handler creation error: ' . $e->getMessage());
        echo json_encode(['error' => 'Failed to start the file/s upload system.']);
        exit;
    }

<?php

    namespace QCubed\Plugin;

    require ('GalleryUploadHandlerGen.php');

    use QCubed\Control\ControlBase;
    use QCubed\Control\FormBase;
    use QCubed\Exception\Caller;
    use QCubed\Exception\InvalidCast;
    use QCubed\Folder;
    use QCubed\Type;


    /**
     * Class FileUpload
     *
     * Note: the "upload" folder must already exist in /project/assets/ and this folder has 777 permissions.
     *
     * @property string $RootPath Default root path APP_UPLOADS_DIR. You may change the location of the file repository
     *                             at your own risk.
     * @property string $RootUrl Default root url APP_UPLOADS_URL. If necessary, the root url must be specified.
     *
     * Note: If you want to change TempPath, TempUrl and StoragePath, you have to rewrite the setup() function in the FileUpload class.
     * This class is located in the /project/includes/plugins folder.
     *
     * @property string $TempPath = Default temp path APP_UPLOADS_TEMP_DIR. If necessary, the temp dir must be specified.
     * @property string $TempUrl Default temp url APP_UPLOADS_TEMP_URL. If necessary, the temp url must be specified.
     * @property string $StoragePath Default dir named _files. This dir is generated together with the dirs
     *                               /thumbnail,  /medium,  /large when the corresponding page is opened for the first time.
     * @property string $FullStoragePath Please a see the setup() function! Can only be changed in this function.
     *
     * @package QCubed\Plugin
     */

    class GalleryUploadHandler extends GalleryUploadHandlerGen
    {
        /** @var string */
        protected string $strRootPath = APP_UPLOADS_DIR;
        /** @var string */
        protected string $strRootUrl = APP_UPLOADS_URL;
        /** @var string */
        protected string $strTempPath = APP_UPLOADS_TEMP_DIR;
        /** @var string */
        protected string $strTempUrl = APP_UPLOADS_TEMP_URL;
        /** @var string */
        protected string $strStoragePath = '_files';
        /** @var string */
        protected string $strFullStoragePath;

        /**
         * Constructor for initializing the control.
         *
         * This method performs the following operations:
         * - Calls the parent constructor for the base setup.
         * - Registers necessary files required for the control.
         * - Sets up the environment and configuration for the control.
         *
         * @param ControlBase|FormBase $objParentObject The parent object to which this control belongs.
         * @param string|null $strControlId The optional ID for the control. If null, an ID will be automatically
         *     generated.
         *
         * @return void
         * @throws Caller
         */
        public function  __construct(ControlBase|FormBase $objParentObject, ?string $strControlId = null)
        {
            parent::__construct($objParentObject, $strControlId);

            $this->registerFiles();
            $this->setup();
        }

        /**
         * Registers necessary JavaScript and CSS files for the application.
         *
         * @return void
         * @throws Caller
         */
        protected function registerFiles(): void
        {
            $this->AddJavascriptFile(QCUBED_QCUBED_MINIGALLERY_MANAGER_ASSETS_URL . "/js/qcubed.galleryupload.js");
            $this->addCssFile(QCUBED_QCUBED_MINIGALLERY_MANAGER_ASSETS_URL . "/css/qcubed.uploadhandler.css");
            //$this->addCssFile(QCUBED_NESTEDSORTABLE_ASSETS_URL. "/css/style.css");
            //$this->addCssFile(QCUBED_NESTEDSORTABLE_ASSETS_URL . "/css/vauu-table.css");
            $this->AddCssFile(QCUBED_BOOTSTRAP_CSS); // make sure they know
        }

        /**
         * Generates the HTML for the control.
         *
         * @return string Generated HTML string for the control.
         */
        protected function getControlHtml(): string
        {
            return _nl('<div class="files"></div>');
        }

        /**
         * Sets up the directory structure and ensures the necessary paths are writable and accessible.
         *
         * This method performs the following operations:
         * - Constructs the full storage path from temporary and storage paths.
         * - Creates necessary directories if they do not exist.
         * - Verifies write permissions for the root path and storage path.
         * - Exits script if the request method is POST to prevent loading the entire page.
         * - Validates and cleans the root and temporary URLs and paths.
         * - Throws exceptions if the necessary permissions or directories are not properly set.
         *
         * @return void
         * @throws Caller if the root or storage paths are not writable or not found with appropriate permissions.
         */
        protected function setup(): void
        {
            $this->strFullStoragePath = $this->strTempPath . '/' . $this->strStoragePath;
            $strCreateDirs = ['/thumbnail', '/medium', '/large', '/zip', '/temp'];

            if (!is_dir($this->strRootPath)) {
                Folder::makeDirectory(QCUBED_PROJECT_DIR . '/assets/upload', 0777);
            }

            if (!is_dir($this->strFullStoragePath)) {
                Folder::makeDirectory($this->strFullStoragePath, 0777);
                foreach ($strCreateDirs as $strCreateDir) {
                    Folder::makeDirectory($this->strFullStoragePath . $strCreateDir, 0777);
                }
            }

            if($_SERVER['REQUEST_METHOD'] == "POST") {exit;} // prevent loading entire page in the echo

            $isHttps = isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)
                || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';

            /** Clean and check $strRootPath */
            $this->strRootPath = rtrim($this->strRootPath, '\\/');
            $this->strRootPath = str_replace('\\', '/', $this->strRootPath);

            $permissions = fileperms($this->strRootPath);
            $permissions = substr(sprintf('%o', $permissions), -4);
            if (!Folder::isWritable($this->strRootPath)) {
                throw new Caller('Root path "' . $this->strRootPath . '" not writable or not found, and
            it has the following directory permissions: ' . $permissions . '. Please set 0755 or 0777 permissions to the
            directory or create a directory "upload" into the location "/project/assets" and grant permissions 0755 or 0777!');
            }

            if (!Folder::isWritable($this->strRootPath)) {
                throw new Caller('Root path "' . $this->strRootPath . '" not writable or not found, and
            it has the following directory permissions: ' . $permissions . '. Please set 0755 or 0777 permissions to the
            directory or create a directory "upload" into the location "/project/assets" and grant permissions 0755 or 0777!');
            }

            if (!Folder::isWritable($this->strFullStoragePath)) {
                throw new Caller('Storage path "' . $this->strTempPath . '/' . $this->strStoragePath .
                    '" not writable or not found." Please set permissions to the 0777 directory "/project/tmp", the "_files" folder and subfolders!');
            }

            clearstatcache();
            /** clean $strRootUrl */
            $this->strRootUrl = $this->cleanPath($this->strRootUrl);
            /** clean $strTempUrl */
            $this->strTempUrl = $this->cleanPath($this->strTempUrl);
            /** Server hostname. Can set manually if wrong. Don't change! */
            $strHttpHost = $_SERVER['HTTP_HOST'];

            $this->strRootUrl = $isHttps ? 'https' : 'http' . '://' . $strHttpHost . (!empty($this->strRootUrl) ? '/' . $this->strRootUrl : '');
            $this->strTempUrl = $isHttps ? 'https' : 'http' . '://' . $strHttpHost . (!empty($this->strTempUrl) ? '/' . $this->strTempUrl : '');
        }

        /**
         * Cleans the given path by removing redundant characters and unnecessary elements.
         *
         * This method performs the following operations:
         * - Trims whitespace from the beginning and end of the path.
         * - Trims slashes and backslashes from the beginning and end of the path.
         * - Removes '../' and '..\' sequences to prevent directory traversal.
         * - Converts all backslashes to forward slashes.
         * - Replaces '..' with an empty string to further sanitize the path.
         *
         * @param string $path The file path to be cleaned.
         *
         * @return string The cleaned file path.
         */
        protected function cleanPath(string $path): string
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
         * Magic method for retrieving the value of a requested property.
         *
         * This method handles dynamic property access for the class. It provides
         * predefined property names and their associated values, and delegates
         * any unhandled properties to the parent class's __get() method.
         *
         * @param string $strName The name of the property being accessed.
         *
         * @return mixed Returns the value of the requested property if it matches a predefined attribute.
         *               If the property is not matched, attempts to retrieve it via the parent::__get().
         * @throws Caller if the requested property does not exist in the current or parent class.
         */
        public function __get(string $strName): mixed
        {
            switch ($strName) {
                case "RootPath": return $this->strRootPath;
                case "RootUrl": return $this->strRootUrl;
                case "TempPath": return $this->strTempPath;
                case "TempUrl": return $this->strTempUrl;
                case "StoragePath": return $this->strStoragePath;

                default:
                    try {
                        return parent::__get($strName);
                    } catch (Caller $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }
            }
        }

        /**
         * Magic method to set the value of a property.
         *
         * This method allows object properties to be dynamically assigned based on their name.
         * It performs the following operations:
         * - Validates and casts the provided value to the expected type for known properties.
         * - Marks the object as modified after successfully setting the property value.
         * - Delegates setting unknown properties to the parent class if applicable.
         * - Throws exceptions for invalid casts or unsupported property names.
         *
         * @param string $strName The name of the property to set.
         * @param mixed $mixValue The value to assign to the property.
         *
         * @return void
         * @throws InvalidCast If the provided value cannot be cast to the expected type.
         * @throws Caller If the property name is unsupported or cannot be set in the parent class.
         */
        public function __set(string $strName, mixed $mixValue): void
        {
            switch ($strName) {
                case "RootPath":
                    try {
                        $this->strRootPath = Type::cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case "RootUrl":
                    try {
                        $this->strRootUrl = Type::cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case "TempPath":
                    try {
                        $this->strTempPath = Type::cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case "StoragePath":
                    try {
                        $this->strStoragePath = Type::cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }

                default:
                    try {
                        parent::__set($strName, $mixValue);
                        break;
                    } catch (Caller $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }
            }
        }
    }
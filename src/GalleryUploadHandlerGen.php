<?php

    namespace QCubed\Plugin;

    use QCubed\Control\Panel;
    use QCubed\Exception\Caller;
    use QCubed\Exception\InvalidCast;
    use QCubed\Type;

    /**
     * Class GalleryUploadHandlerGen
     *
     * @see GalleryUploadHandler
     * @package QCubed\Plugin
     */

    /**
     * @property string $Language	Default: empty. Optional language selection, default is set to English (en).
     * @property boolean $MultipleUploads Default: false. Optional, must be set to true to allow multiple file uploads.
     * @property boolean $ShowIcons Default: false. If necessary, if you do not want to display the preview, you can turn on
     *                              Only the display of icons.
     * @property array $AcceptFileTypes Default: null. The output form of the array looks like this:
     *                                 '['gif', 'jpg', 'jpeg', 'png', 'pdf']'. If necessary, specify the allowed file types.
     *                                  When empty (default), all file types are allowed.
     * @property integer $MaxNumberOfFiles Default: null. If necessary, limit the upper limit of uploaded files
     * @property integer $MaxFileSize Default: null. The maximum allowed file size in bytes, for example (1024 * 1024 * 5, // 5 MB)
     * @property integer $MinFileSize Default: null, The minimum allowed file size in bytes. No minimal file size
     * @property boolean $ChunkUpload Default: true. A chunked upload is on by default. Can switch to normal upload if needed
     * @property integer $MaxChunkSize Default: '1024 * 1024 (1 MB)'. If possible, the size of bytes can be increased
     * @property string $LimitConcurrentUploads Default: '2'. Limited to 2 simultaneous uploads by default. Can be increased if necessary
     * @property string $Url Default: null. If necessary, you can refer to the file processing php
     * @property integer $PreviewMaxWidth Default: '80' (maximum preview width limit)
     * @property integer $PreviewMaxHeight Default: '80' (maximum preview height limit)
     *
     * @package QCubed\Plugin
     */

    class GalleryUploadHandlerGen extends Panel
    {
        /** @var string|null */
        protected ?string $strLanguage = null;
        /** @var boolean */
        protected ?bool $blnMultipleUploads = null;
        /** @var boolean */
        protected ?bool $blnShowIcons = null;
        /** @var array|null */
        protected ?array $arrAcceptFileTypes = null;
        /** @var integer|null */
        protected ?int $intMaxNumberOfFiles = null;
        /** @var integer|null */
        protected ?int $intMaxFileSize = null;
        /** @var integer|null */
        protected ?int $intMinFileSize = null;
        /** @var boolean */
        protected ?bool $blnChunkUpload = null;
        /** @var integer|null */
        protected ?int $intMaxChunkSize = null;
        /** @var integer|null */
        protected ?int $intLimitConcurrentUploads = null;
        /** @var string|null */
        protected ?string $strUrl = null;
        /** @var string|null */
        protected ?string $intPreviewMaxWidth = null;
        /** @var string|null */
        protected ?string $intPreviewMaxHeight = null;
        /** @var boolean */
        protected ?bool $blnWithCredentials = null;

        /**
         * Generates and returns an array of jQuery options based on the class properties.
         * Each option is included if its corresponding property is not null.
         * Calls the parent implementation to include any options from the parent class.
         *
         * @return array The generated array of jQuery options.
         */
        protected function makeJqOptions(): array
        {
            $jqOptions = parent::MakeJqOptions();
            if (!is_null($val = $this->Language)) {$jqOptions['language'] = $val;}
            if (!is_null($val = $this->MultipleUploads)) {$jqOptions['multipleUploads'] = $val;}
            if (!is_null($val = $this->ShowIcons)) {$jqOptions['showIcons'] = $val;}
            if (!is_null($val = $this->AcceptFileTypes)) {$jqOptions['acceptFileTypes'] = $val;}
            if (!is_null($val = $this->MaxNumberOfFiles)) {$jqOptions['maxNumberOfFiles'] = $val;}
            if (!is_null($val = $this->MaxFileSize)) {$jqOptions['maxFileSize'] = $val;}
            if (!is_null($val = $this->MinFileSize)) {$jqOptions['minFileSize'] = $val;}
            if (!is_null($val = $this->ChunkUpload)) {$jqOptions['chunkUpload'] = $val;}
            if (!is_null($val = $this->MaxChunkSize)) {$jqOptions['maxChunkSize'] = $val;}
            if (!is_null($val = $this->LimitConcurrentUploads)) {$jqOptions['limitConcurrentUploads'] = $val;}
            if (!is_null($val = $this->Url)) {$jqOptions['url'] = $val;}
            if (!is_null($val = $this->PreviewMaxWidth)) {$jqOptions['previewMaxWidth'] = $val;}
            if (!is_null($val = $this->PreviewMaxHeight)) {$jqOptions['previewMaxHeight'] = $val;}
            return $jqOptions;
        }

        /**
         * Retrieves the name of the JavaScript function to be used for setting up a jQuery-based component.
         *
         * @return string Returns the name of the jQuery setup function as a string.
         */
        public function getJqSetupFunction(): string
        {
            return 'galleryUpload';
        }

        /**
         * Magic method to retrieve the value of a specified property dynamically.
         *
         * Maps property names to their corresponding class members and returns their values.
         * If the property does not exist, attempts to retrieve it from the parent class.
         *
         * @param string $strName The name of the property to retrieve.
         *
         * @return mixed Returns the value of the requested property or throws an exception
         *               if the property is not defined.
         * @throws Caller
         */
        public function __get(string $strName): mixed
        {
            switch ($strName) {
                case 'Language': return t($this->strLanguage);
                case 'MultipleUploads': return $this->blnMultipleUploads;
                case 'ShowIcons': return $this->blnShowIcons;
                case 'AcceptFileTypes': return $this->arrAcceptFileTypes;
                case 'MaxNumberOfFiles': return $this->intMaxNumberOfFiles;
                case 'MaxFileSize': return $this->intMaxFileSize;
                case 'MinFileSize': return $this->intMinFileSize;
                case 'ChunkUpload': return $this->blnChunkUpload;
                case 'MaxChunkSize': return $this->intMaxChunkSize;
                case 'LimitConcurrentUploads': return $this->intLimitConcurrentUploads;
                case 'Url': return $this->strUrl;
                case 'PreviewMaxWidth': return $this->intPreviewMaxWidth;
                case 'PreviewMaxHeight': return $this->intPreviewMaxHeight;

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
         * Sets the value of a class property dynamically and applies appropriate transformations
         * or type casts based on the property being set. Updates associated configuration or
         * attributes when applicable.
         *
         * @param string $strName The name of the property to be set.
         * @param mixed $mixValue The value to assign to the property. This value is cast to the
         *                        appropriate type based on the property being set.
         *
         * @return void
         *
         * @throws InvalidCast If the value provided cannot be cast to the expected type.
         * @throws Caller If the property name is invalid or cannot be set.
         */
        public function __set(string $strName, mixed $mixValue): void
        {
            switch ($strName) {
                case 'Language':
                    try {
                        $this->strLanguage = Type::cast($mixValue, Type::STRING);
                        $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'language', $this->strLanguage);
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }

                case 'MultipleUploads':
                    try {
                        $this->blnMultipleUploads = Type::cast($mixValue, Type::BOOLEAN);
                        $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'multipleUploads', $this->blnMultipleUploads);
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }

                case 'ShowIcons':
                    try {
                        $this->blnShowIcons = Type::cast($mixValue, Type::STRING);
                        $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'showIcons', $this->blnShowIcons);
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }

                case 'AcceptFileTypes':
                    try {
                        $this->arrAcceptFileTypes = Type::cast($mixValue, Type::ARRAY_TYPE);
                        $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'acceptFileTypes', $this->arrAcceptFileTypes);
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }

                case 'MaxNumberOfFiles':
                    try {
                        $this->intMaxNumberOfFiles = Type::cast($mixValue, Type::INTEGER);
                        $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'maxNumberOfFiles', $this->intMaxNumberOfFiles);
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }

                case 'MaxFileSize':
                    try {
                        $this->intMaxFileSize = Type::cast($mixValue, Type::INTEGER);
                        $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'maxFileSize', $this->intMaxFileSize);
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }

                case 'MinFileSize':
                    try {
                        $this->intMinFileSize = Type::cast($mixValue, Type::INTEGER);
                        $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'minFileSize', $this->intMinFileSize);
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }

                case 'ChunkUpload':
                    try {
                        $this->blnChunkUpload = Type::cast($mixValue, Type::BOOLEAN);
                        $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'chunkUpload', $this->blnChunkUpload);
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }

                case 'MaxChunkSize':
                    try {
                        $this->intMaxChunkSize = Type::cast($mixValue, Type::INTEGER);
                        $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'maxChunkSize', $this->intMaxChunkSize);
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }

                case 'LimitConcurrentUploads':
                    try {
                        $this->intLimitConcurrentUploads = Type::cast($mixValue, Type::INTEGER);
                        $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'limitConcurrentUploads', $this->intLimitConcurrentUploads);
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }

                case 'Url':
                    try {
                        $this->strUrl = Type::cast($mixValue, Type::STRING);
                        $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'url', $this->strUrl);
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }

                case 'PreviewMaxWidth':
                    try {
                        $this->intPreviewMaxWidth = Type::cast($mixValue, Type::INTEGER);
                        $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'previewMaxWidth', $this->intPreviewMaxWidth);
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }

                case 'PreviewMaxHeight':
                    try {
                        $this->intPreviewMaxHeight = Type::cast($mixValue, Type::INTEGER);
                        $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'previewMaxHeight', $this->intPreviewMaxHeight);
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
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

        /**
         * Retrieves and merges model connector parameters from the parent class with additional parameters.
         *
         * @return array An associative array containing the combined model connector parameters from
         *               the parent class and any additional parameters defined in the current class.
         */
        public static function getModelConnectorParams(): array
        {
            return array_merge(parent::GetModelConnectorParams(), array());
        }
    }
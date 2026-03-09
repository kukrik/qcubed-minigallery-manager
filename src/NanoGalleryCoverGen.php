<?php
    namespace QCubed\Plugin;

    use QCubed\ApplicationBase;
    use QCubed\Exception\Caller;
    use QCubed\Control\Panel;
    use QCubed\Type;
    use QCubed\Exception\InvalidCast;
    use QCubed\Project\Application;

    use ContentCoverMedia;
    use MiniGallery;
    use QCubed\Query\QQ;
    use QQN;

    /**
     * Class NanoGalleryCoverGen
     *
     * A class that extends the Panel class and facilitates the generation of a cover and an optional mini-gallery
     * using nanoGallery2 functionalities. This class provides features for rendering dynamic HTML outputs,
     * JavaScript initialization, and managing gallery configuration options.
     *
     * @property string $TempUrl The URL of the temporary folder used for storing uploaded files.
     * @property int $ContentCoverMediaId The ID of the content cover media object.
     * @property string $ItemsBaseURL The base URL for the gallery items.
     * @property array $ViewerToolbar The viewer toolbar configuration options.
     * @property array $ViewerTools The viewer tools configuration options.
     * @property bool $LocationHash Whether to enable location hash support for the gallery.
     * @property string $StartItem The starting item for the gallery.
     * @property bool $Debug Whether to enable debug mode for the gallery.
     * @property string $MultiImagesIconPath The path to the icon used for displaying multiple images.
     *
     */
    class NanoGalleryCoverGen extends Panel
    {
        /** @var string|null */
        protected ?string $strTempUrl = null;

        /** @var int|null */
        protected ?int $intContentCoverMediaId = null;

        /** @var string|null */
        protected ?string $strItemsBaseURL = null;

        /** @var array|null nanogallery2 viewerToolbar config */
        protected ?array $arrViewerToolbar = null;

        /** @var array|null nanogallery2 viewerTools config */
        protected ?array $arrViewerTools = null;

        /** @var bool */
        protected bool $blnLocationHash = false;

        /** @var string */
        protected string $strStartItem = '0/1';

        /** @var bool */
        protected bool $blnDebug = false;

        /** @var null|string */
        protected ?string $strMultiImagesIconPath = QCUBED_MINIGALLERY_MANAGER_ASSETS_URL . "/images/multi-images-icon.svg";

        /**
         * Generates the HTML for the control that includes a cover and an optional mini-gallery with multiple images.
         *
         * @return string The HTML content for the control, including the cover image and the mini-gallery if applicable.
         * @throws Caller
         * @throws InvalidCast
         */
        protected function getControlHtml(): string
        {
            if (!$this->intContentCoverMediaId) {
                return '';
            }

            /** @var ContentCoverMedia|null $objCoverMedia */
            $objCoverMedia = ContentCoverMedia::load($this->intContentCoverMediaId);
            if (!$objCoverMedia) {
                return '';
            }

            $coverPath = (string)$objCoverMedia->getPreviewFilePath();
            if ($coverPath === '') {
                return '';
            }

            $listDescription = $objCoverMedia->getDescription();
            $listAuthor = $objCoverMedia->getAuthor();

            $coverIcoPath = $this->MultiImagesIconPath ? '<img src="' . $this->MultiImagesIconPath  . '">' : '';

            // Items (mini-gallery)
            $arrImages = MiniGallery::loadArrayByContentCoverMediaId(
                $objCoverMedia->getId(),
                QQ::Clause(QQ::OrderBy(QQN::MiniGallery()->Name))
            );

            $coverId = $this->getCoverId();

            $galleryId = $this->getGalleryId();

            $safeCover = htmlspecialchars($this->ItemsBaseURL . $coverPath, ENT_QUOTES);

            $itemsHtml = '';
            foreach ($arrImages as $objImg) {
                $status = (int)$objImg->getStatus();
                if ($status !== 1) {
                    continue;
                }

                $path = (string)$objImg->getPath();
                if ($path === '') {
                    continue;
                }

                // MiniGallery item fields
                $description = trim((string)$objImg->getDescription());
                $author = trim((string)$objImg->getAuthor());

                // fallback from cover media (list level)
                $finalDescription = $description ?: trim((string)$listDescription);
                $finalAuthor = $author ?: trim((string)$listAuthor);

                $safePath = htmlspecialchars($path, ENT_QUOTES);

                $attrNgDesc = '';
                if ($finalAuthor !== '') {
                    $attrNgDesc = ' data-ngdesc="' . htmlspecialchars($finalAuthor, ENT_QUOTES) . '"';
                }

                $innerTitle = '';
                if ($finalDescription !== '') {
                    $innerTitle = htmlspecialchars($finalDescription, ENT_QUOTES);
                }

                $itemsHtml .= _nl("<a href=\"$safePath\" data-ngthumb=\"$safePath\"$attrNgDesc>$innerTitle</a>");
            }

            $strHtml = '';
            $strHtml .= _nl('<div id="'.$this->ControlId . '" class="multi-images-container">');
            $strHtml .= _nl(_indent('<a id="' . $coverId . '" class="mini-gallery-wrapper" href="#" aria-label="Open mini gallery">', 1));
            $strHtml .= _nl(_indent('<img src="' . $safeCover . '" alt="" class="image img-responsive" />', 2));
            $strHtml .= _nl(_indent('<span class="gallery-img" aria-hidden="true">', 2));
            $strHtml .= _nl(_indent($coverIcoPath, 3));
            $strHtml .= _nl(_indent('</span>', 2));
            $strHtml .= _nl(_indent(' </a>', 1));
            $strHtml .= _nl(_indent('<div id="' . $galleryId . '" style="display:none;">', 1));
            $strHtml .= _nl(_indent($itemsHtml, 2));
            $strHtml .= _nl(_indent('</div>',1));
            $strHtml .= _nl('</div>');

            return $strHtml;
        }

        /**
         * Retrieves the cover ID for the control.
         *
         * @return string The unique cover ID associated with the control.
         */
        protected function getCoverId(): string
        {
            return $this->ControlId . '_cover';
        }

        /**
         * Retrieves the unique identifier for the gallery.
         *
         * @return string The gallery ID, which is a combination of the control ID and a suffix.
         */
        protected function getGalleryId(): string
        {
            return $this->ControlId . '_ng2';
        }

        /**
         * Generates and returns JavaScript code to initialize the nanoGallery2 instance for this component.
         * This includes applying configured options such as item base URL, viewer toolbar, viewer tools,
         * location hash, and thumbnail dimensions. If no content covering media ID is set, it returns the
         * parent JavaScript code without modifications.
         *
         * @return string The JavaScript code required to initialize the gallery component.
         * @throws Caller
         */
        public function getEndScript(): string
        {
            $strJs = parent::getEndScript();

            if (!$this->intContentCoverMediaId) {
                return $strJs;
            }

            // JS options nanogallery2
            $nanoOptions = [];

            if ($this->strItemsBaseURL) {
                $nanoOptions['itemsBaseURL'] = $this->strItemsBaseURL;
            }

            if ($this->arrViewerToolbar) {
                $nanoOptions['viewerToolbar'] = $this->arrViewerToolbar;
            }

            if ($this->arrViewerTools) {
                $nanoOptions['viewerTools'] = $this->arrViewerTools;
            }

            if ($this->blnLocationHash) {
                $nanoOptions['locationHash'] = $this->blnLocationHash;
            }

            // Minimal defaults (can change)
            $nanoOptions += [
                'thumbnailHeight' => 100,
                'thumbnailWidth'  => 100,
            ];

            $options = [
                'coverSelector' => '#' . $this->getCoverId(),
                'startItem'     => $this->strStartItem,
                'nanoOptions'   => $nanoOptions,
                'debug'         => $this->blnDebug
            ];

            $json = json_encode($options, JSON_UNESCAPED_SLASHES);

            // Init wrapper for this instance
            $gallerySelector = '#' . $this->getGalleryId();

            Application::executeJavaScript(
                "jQuery('$gallerySelector').nanoGalleryCover($json);",
                ApplicationBase::PRIORITY_HIGH
            );

            return $strJs;
        }

        /**
         * Magic method to retrieve protected or private property values.
         *
         * @param string $strName The name of the property to retrieve.
         *
         * @return mixed The value of the requested property, if it exists, or the result of the parent::__get() method
         *     otherwise.
         * @throws Caller
         */
        public function __get(string $strName): mixed
        {
            return match ($strName) {
                'TempUrl' => $this->strTempUrl,
                'ContentCoverMediaId' => $this->intContentCoverMediaId,
                'ItemsBaseURL' => $this->strItemsBaseURL,
                'ViewerToolbar' => $this->arrViewerToolbar,
                'ViewerTools' => $this->arrViewerTools,
                'LocationHash' => $this->blnLocationHash,
                'StartItem' => $this->strStartItem,
                'Debug' => $this->blnDebug,
                'MultiImagesIconPath' => $this->strMultiImagesIconPath,
                default => parent::__get($strName),
            };
        }

        /**
         * Sets the value of a property dynamically.
         *
         * @param string $strName The name of the property to set.
         * @param mixed $mixValue The value to assign to the property.
         *
         * @return void
         * @throws InvalidCast If the value cannot be cast to the required type.
         * @throws Caller
         */
        public function __set(string $strName, mixed $mixValue): void
        {
            try {
                switch ($strName) {
                    case 'ContentCoverMediaId':
                        $this->intContentCoverMediaId = Type::Cast($mixValue, Type::INTEGER);
                        $this->blnModified = true;
                        break;

                    case 'ItemsBaseURL':
                        $this->strItemsBaseURL = Type::Cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;

                    case 'ViewerToolbar':
                        $this->arrViewerToolbar = Type::Cast($mixValue, Type::ARRAY_TYPE);
                        $this->blnModified = true;
                        break;

                    case 'ViewerTools':
                        $this->arrViewerTools = Type::Cast($mixValue, Type::ARRAY_TYPE);
                        $this->blnModified = true;
                        break;

                    case 'LocationHash':
                        $this->blnLocationHash = Type::Cast($mixValue, Type::BOOLEAN);
                        $this->blnModified = true;
                        break;

                    case 'StartItem':
                        $this->strStartItem = Type::Cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;

                    case 'Debug':
                        $this->blnDebug = Type::Cast($mixValue, Type::BOOLEAN);
                        $this->blnModified = true;
                        break;

                    case 'MultiImagesIconPath':
                        $this->strMultiImagesIconPath = Type::Cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;

                    default:
                        parent::__set($strName, $mixValue);
                        break;
                }
            } catch (InvalidCast $objExc) {
                $objExc->IncrementOffset();
                throw $objExc;
            }
        }
    }
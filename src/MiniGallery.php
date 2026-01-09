<?php

    namespace QCubed\Plugin;

    use QCubed\ApplicationBase;
    use QCubed\Project\Control\FormBase;
    use QCubed\Project\Control\ControlBase;
    use QCubed\Exception\InvalidCast;
    use QCubed\Exception\Caller;
    use QCubed\Project\Application;
    use QCubed\Type;

    /**
     * Class MiniGallery
     *
     * @property string $EmptyImagesPath Default predefined image can be overridden and replaced with another image if desired
     * @property string $EmptyImagesAlt Default null. The recommendation is to add the following text: "Choose a mini gallery"
     * @property integer $SelectedImagesId Default null. For selected images, the image group ID is entered.
     *                                      The IDs of the selected images are also entered into the selected table column
     *                                      in the database, along with the ID of the image group.
     * @property integer $CoverImageId Default null. In the case of a selected cover image, the id of the cover image is pushed.
     * @property string $TempUrl Default APP_UPLOADS_TEMP_URL. The path to the temporary folder where the uploaded images are stored.
     * @property string $CoverImagePath Default null. The path of the selected cover image with the file name.
     * @property string $RemoveAssociation Default "Remove association"
     * @property array $Items
     *
     * @package QCubed\Plugin
     */

    class MiniGallery extends MiniGalleryGen
    {
        /** @var null|array Items */
        protected ?array $strItems  = null;
        /** @var string TempUrl */
        protected string $strTempUrl = APP_UPLOADS_TEMP_URL . '/_files/thumbnail';

        /** @var string EmptyImagesPath */
        protected string $strEmptyImagesPath = QCUBED_QCUBED_MINIGALLERY_MANAGER_ASSETS_URL . "/images/empty-multi-images-icon.png";
        /** @var null|string EmptyImagesAlt */
        protected ?string $strEmptyImagesAlt = null;
        /** @var null|integer SelectedImagesId */
        protected ?int $intSelectedImagesId = null;
        /** @var null|integer CoverImageId */
        protected ?int $intCoverImageId = null;
        /** @var null|string CoverImagePath */
        protected ?string $strCoverImagePath = null;
        /** @var string RemoveAssociation */
        protected string $strRemoveAssociation = "Remove association";

        /**
         * Constructor method for initializing the class.
         *
         * @param ControlBase|FormBase $objParentObject The parent object, which must be an instance of ControlBase or FormBase.
         * @param string|null $strControlId An optional control ID for identifying the object.
         *
         * @throws Caller
         */
        public function __construct(ControlBase|FormBase $objParentObject, ?string $strControlId = null)
        {
            parent::__construct($objParentObject, $strControlId);

            $this->registerFiles();
        }

        /**
         * Registers necessary JavaScript and CSS files used by the application.
         *
         * This method loads JavaScript and CSS files required for the functionality
         * of the video manager. It ensures the inclusion of scripts for a video embed, custom logic, and necessary CSS styles, including Bootstrap.
         *
         * @return void
         * @throws Caller
         */
        protected function registerFiles(): void
        {
            $this->AddCssFile(QCUBED_QCUBED_MINIGALLERY_MANAGER_ASSETS_URL . "/css/qcubed.coverimages.css");
            $this->AddJavascriptFile(QCUBED_QCUBED_MINIGALLERY_MANAGER_ASSETS_URL . "/js/qcubed.minigallery.js");
            $this->AddJavascriptFile(QCUBED_QCUBED_MINIGALLERY_MANAGER_ASSETS_URL . "/js/qcubed.minigallery-helper.js");
            $this->AddCssFile(QCUBED_BOOTSTRAP_CSS); // make sure they know
        }

        /**
         * Generates and returns the HTML content for the control.
         *
         * @return string The constructed HTML string for the control, including image container and templates.
         */
        protected function getControlHtml(): string
        {
            $strHtml = _nl('<div id="' . $this->ControlId . '" class="multi-images-container">');
            $strHtml .= $this->chooseMiniGalleryTemplate();
            $strHtml .= $this->selectedMiniGalleryTemplate();
            $strHtml .= '</div>';

            return $strHtml;
        }

        /**
         * Builds and returns the HTML string for the mini gallery UI component based on the current state.
         *
         * The method generates a container element for the mini gallery and conditionally applies
         * classes and attributes depending on the presence of a selected image ID and alternate text for images.
         *
         * @return string The generated HTML string for the mini gallery component.
         */
        protected function chooseMiniGalleryTemplate(): string
        {
            $strHtml = '';

            if (!$this->intSelectedImagesId) {
                $strHtml .= _nl(_indent('<div class="choose-mini-gallery">', 1));
            } else {
                $strHtml .= _nl(_indent('<div class="choose-mini-gallery hidden">', 1));
            }

            if ($this->strEmptyImagesAlt) {
                $strHtml .= _nl(_indent('<img src="' . $this->strEmptyImagesPath . '" alt="' . $this->strEmptyImagesAlt . '" class="image img-responsive">', 2));
            } else {
                $strHtml .= _nl(_indent('<img src="' . $this->strEmptyImagesPath . '" class="image img-responsive">', 2));
            }

            $strHtml .= _nl(_indent('</div>', 1));

            return $strHtml;
        }

        /**
         * Generates an HTML string for the selected mini-gallery template, including
         * the cover image, gallery wrapper, SVG icons, and interactive overlay components.
         *
         * @return string The generated HTML string representing the selected mini-gallery.
         */
        protected function selectedMiniGalleryTemplate(): string
        {
            $strHtml = '';

            $strDataId = $this->intSelectedImagesId ? (string)$this->intSelectedImagesId: '';
            $strHiddenClass = $this->intSelectedImagesId ? '' : ' hidden';

            $strHtml .= _nl(_indent('<div id="' . $this->ControlId . '" class="selected-cover-image' . $strHiddenClass . '" data-id="' . $strDataId . '">',1));

            $strHtml .= _nl(_indent('<div class="mini-gallery-wrapper" data-id="' . $strDataId . '">', 2));
            $strHtml .= _nl(_indent('<img src="' . $this->strCoverImagePath . '" data-id ="' . $strDataId . '" class="image selected-path img-responsive">',3));

            $strHtml .= _nl(_indent('<span class="gallery-img">',3));
            $strHtml .= _nl(_indent('<svg viewBox="0 0 25 19" class="files-svg">',4));
            $strHtml .= _nl(_indent('<g fill="none" fill-rule="evenodd">',5));
            $strHtml .= _nl(_indent('<path fill="#fff" fill-opacity=".869" d="M4 4h20v14H4z"></path>',6));
            $strHtml .= _nl(_indent(' <path stroke="#0f6ca9" fill-opacity=".869" stroke-width="1.444" class="atlas-svg-white atlas-svg-stroke" d="M23.862 18H4.332c-.075 0-.137-.06-.137-.134V4.164c0-.074.062-.134.137-.134h19.53c.076 0 .138.06.138.134v13.702c0 .074-.062.134-.138.134z"></path>',6));
            $strHtml .= _nl(_indent('<path fill="#0f6ca9" d="M20.452.007H.922c-.475 0-.86.376-.86.84v13.7c0 .464.385.84.86.84h1.402v-1.41h-.818V1.417h18.36v.773h1.445V.846c0-.463-.383-.84-.857-.84zm-4.47 8.073l2.62 3.694 1.382-1.92 1.967 5.58H6.41c.047-.302.164-.745.474-1.175.956-1.345 2.93-1.41 3.273-1.426.59-.02 1.616.06 1.78.074.324.026.586.053.764.073l3.283-4.902z"></path>',6));
            $strHtml .= _nl(_indent('<ellipse cx="9.765" cy="8.886" fill="#0f6ca9" rx="1.719" ry="1.679"></ellipse>',6));
            $strHtml .= _nl(_indent('</g>',5));
            $strHtml .= _nl(_indent('</svg>',4));
            $strHtml .= _nl(_indent('</span>',3));

            $strHtml .= _nl(_indent('<div class="selected-overlay" data-id="' . $strDataId . '" data-event="edit"></div>',3));
            $strHtml .= _nl(_indent('</div>', 2));

            $strHtml .= _nl(_indent('<div class="delete-wrapper" data-id="' . $strDataId . '" data-event="delete">', 2));
            $strHtml .= _nl(_indent('<div class="delete-overlay" data-id="' . $strDataId . '">',3));
            $strHtml .= _nl(_indent('<span class="overLay-right" aria-label="' . t($this->strRemoveAssociation) . '">', 4));
            $strHtml .= _nl(_indent('<svg viewBox="-15 -15 56 56" class="svg-delete files-svg" focusable="false" aria-hidden="true">', 5));
            $strHtml .= _nl(_indent('<path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"></path>', 6));
            $strHtml .= _nl(_indent('</svg>', 5));
            $strHtml .= _nl(_indent('</span>', 4));
            $strHtml .= _nl(_indent('</div>', 3));
            $strHtml .= _nl(_indent('</div>', 2));
            $strHtml .= _nl(_indent('</div>', 1));

            return $strHtml;
        }

        /**
         * Generates and returns the necessary JavaScript code to manage video-related UI interactions.
         *
         * This method constructs client-side JavaScript functionality that handles the behaviors of
         * video selection and deletion in a web-based interface. It ensures proper communication
         * between the frontend and backend by triggering appropriate events and sending modifications.
         *
         * @return string The finalized JavaScript code to be executed for video handling.
         * @throws Caller
         */

        public function getEndScript(): string
        {
            $strJS = parent::getEndScript();

            $strCtrlJs = <<<FUNC
$(document).ready(function() {
    var choose_mini_gallery = document.querySelector(".choose-mini-gallery ");
    var selected_cover_image = document.querySelector(".selected-cover-image");
    var mini_gallery_wrapper = document.querySelector(".mini-gallery-wrapper");
    var selected_path = document.querySelector(".selected-path");
    var selected_overlay = document.querySelector(".selected-overlay");
    var delete_wrapper = document.querySelector(".delete-wrapper");
    var delete_overlay = document.querySelector(".delete-overlay");
    
    function getImagesParams(params) {
        var data = JSON.parse(params);
        console.log(data);
        var id = data.id;
        var path = data.path;
        
        if (id && path) {
            choose_mini_gallery.classList.add('hidden');
            selected_cover_image.classList.remove('hidden');
            mini_gallery_wrapper.setAttribute('data-id', id);
            selected_path.src = '$this->strTempUrl' + path;
            selected_overlay.setAttribute('data-id', id);
            delete_wrapper.setAttribute('data-id', id);
            delete_overlay.setAttribute('data-id', id);
        } else {
            choose_mini_gallery.classList.remove('hidden');
            selected_cover_image.classList.add('hidden');
            mini_gallery_wrapper.setAttribute('data-id', '');
            selected_path.src = '';
            selected_overlay.setAttribute('data-id', '');
            delete_wrapper.setAttribute('data-id', '');
            delete_overlay.setAttribute('data-id', '');
        }
        
       gallerySave(data);
    }
    
    window.getImagesParams = getImagesParams;

    gallerySave = function(params) {
        var selected_path = $(".selected-path");
        qcubed.recordControlModification("$this->ControlId", "_Items", params);
        var GallerySaveEvent = $.Event("gallerysave");
        selected_path.trigger(GallerySaveEvent);
    }
});
FUNC;
            Application::executeJavaScript($strCtrlJs, ApplicationBase::PRIORITY_HIGH);

            return $strJS;
        }

        /**
         * Magic method to retrieve the value of specified properties dynamically.
         *
         * This method provides access to certain defined properties of the object, such as item details,
         * image-related paths, names, alt texts, and other configurations. If the requested property cannot
         * be found in the current class, it attempts to fetch it from the parent class.
         *
         * @param string $strName The name of the property to retrieve.
         *
         * @return mixed The value of the requested property, or an exception if the property does not exist.
         * @throws Caller
         * @throws \Exception
         */
        public function __get(string $strName): mixed
        {
            switch ($strName) {
                case 'Items': return $this->strItems;
                case "EmptyImagesPath": return $this->strEmptyImagesPath;
                case "EmptyImagesAlt": return $this->strEmptyImagesAlt;
                case 'SelectedImagesId': return $this->intSelectedImagesId;
                case 'CoverImageId': return $this->intCoverImageId;
                case 'TempUrl': return $this->strTempUrl;
                case 'CoverImagePath': return $this->strCoverImagePath;
                case "RemoveAssociation": return $this->strRemoveAssociation;

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
         * Overrides the magic __set method to handle dynamically setting the values of defined properties.
         *
         * This method manages property assignments and performs validation or type casting where necessary
         * for supported properties. If the property is not specifically handled, it delegates the call to the parent
         * class.
         *
         * @param string $strName The name of the property being set.
         * @param mixed $mixValue The value being assigned to the property. The type is validated based on the
         *     property.
         *
         * @return void
         *
         * @throws InvalidCast If the value being assigned cannot be cast to the required type.
         * @throws Caller If the property name is not recognized or the parent class cannot handle the assignment.
         * @throws \Exception
         */
        public function __set(string $strName, mixed $mixValue): void
        {
            switch ($strName) {
                case "_Items": // Internal only. Do not use. Used by JS above to track selections.
                    try {
                        $this->strItems = Type::cast($mixValue, Type::ARRAY_TYPE);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case "EmptyImagesPath":
                    try {
                        $this->strEmptyImagesPath = Type::Cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case "EmptyImagesAlt":
                    try {
                        $this->strEmptyImagesAlt = Type::Cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case "SelectedImagesId":
                    try {
                        $this->intSelectedImagesId = Type::Cast($mixValue, Type::INTEGER);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case "CoverImageId":
                    try {
                        $this->intCoverImageId = Type::Cast($mixValue, Type::INTEGER);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case "TempUrl":
                    try {
                        $this->strTempUrl = Type::Cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case "CoverImagePath":
                    try {
                        $this->strCoverImagePath = Type::Cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }
                case "RemoveAssociation":
                    try {
                        $this->strRemoveAssociation = Type::Cast($mixValue, Type::STRING);
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
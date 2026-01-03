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
     * @property string $CoverImagePath Default null. The path of the selected cover image with the file name.
     *
     * @property string $SelectedVideoEmbed  Default null. For the selected video, the video embed will be pulled here.
     * @property string $SelectedVideoAlt Default null. The recommendation is to add the following text: "Selected video"
     *
     * @property string $RemoveAssociation Default "Remove association"
     * @property array $Items
     *
     * @package QCubed\Plugin
     */

    class MiniGallery extends MiniGalleryGen
    {
        protected ?array $strItems  = null;

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

        /** @var null|string SelectedVideoEmbed */
        protected ?string $strSelectedVideoEmbed = null;
        /** @var null|string SelectedVideoAlt */
        protected ?string $strSelectedVideoAlt = null;
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
            $this->AddJavascriptFile(QCUBED_QCUBED_MINIGALLERY_MANAGER_ASSETS_URL . "/js/qcubed.minigallery.js");
            $this->addCssFile(QCUBED_QCUBED_MINIGALLERY_MANAGER_ASSETS_URL . "/css/qcubed.coverimages.css");
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
         * Generates an HTML string for displaying or hiding an image element
         * based on the selected image ID and alternate text availability.
         *
         * @return string The generated HTML string containing the image element.
         */
        protected function chooseMiniGalleryTemplate(): string
        {
            $strHtml = '';

            if (!$this->intSelectedVideoId) {
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
         * Generates an HTML template for displaying the selected image, including its details and controls.
         *
         * The method constructs an HTML structure that visually represents a selected image
         * along with its properties such as ID, path, and optional name and alt text.
         * It also includes overlay controls for handling actions like deletion.
         *
         * @return string The generated HTML for the selected image template.
         */

        protected function selectedMiniGalleryTemplate(): string
        {
            $strHtml = '';

            $strDataId = $this->intSelectedImagesId ? (string)$this->intSelectedImagesId : '';
            $strHiddenClass = $this->intSelectedImagesId ? '' : ' hidden';

            $strHtml .= _nl(_indent(
                '<div id="' . $this->ControlId . '" class="selected-cover-image' . $strHiddenClass . '" data-id="' . $strDataId . '">',
                1
            ));

            $strHtml .= _nl(_indent('<div class="embed-responsive embed-responsive-16by9">', 2));

            if ($this->strSelectedVideoEmbed) {
                $strHtml .= _nl(_indent(" $this->strSelectedVideoEmbed ", 3));
            }

            $strHtml .= _nl(_indent('</div>', 2));

            $strHtml .= _nl(_indent(
                '<div class="selected-overlay" data-id="' . $strDataId . '" data-event="edit"></div>',
                2
            ));

            $strHtml .= _nl(_indent('</div>', 1));

            $strHtml .= _nl(_indent(
                '<div class="delete-wrapper' . $strHiddenClass . '" data-id="' . $strDataId . '" data-event="delete">',
                1
            ));
            $strHtml .= _nl(_indent(
                '<div class="delete-overlay" data-id="' . $strDataId . '">',
                2
            ));
            $strHtml .= _nl(_indent('<span class="overLay-right" aria-label="' . t($this->strRemoveAssociation) . '">', 3));
            $strHtml .= _nl(_indent('<svg viewBox="-15 -15 56 56" class="svg-delete files-svg" focusable="false" aria-hidden="true">', 4));
            $strHtml .= _nl(_indent('<path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z"></path>', 5));
            $strHtml .= _nl(_indent('</svg>', 4));
            $strHtml .= _nl(_indent('</span>', 3));
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
    var choose_video = document.querySelector(".choose-video");
    var selected_video = document.querySelector(".selected-video");
    var embed_wrap = document.querySelector(".embed-responsive");
    var selected_overlay = document.querySelector(".selected-overlay");
    var delete_wrapper = document.querySelector(".delete-wrapper");
    var delete_overlay = document.querySelector(".delete-overlay");

    function getVideoParams(params) {
        var data = JSON.parse(params);
        console.log(data);
        var id = data.id;
        var embed = data.embed;

        if (id && embed) {
            choose_video.classList.add('hidden');
            selected_video.classList.remove('hidden');
            delete_wrapper.classList.remove('hidden');
            embed_wrap.innerHTML = embed;
            selected_video.setAttribute('data-id', id);
            delete_wrapper.setAttribute('data-id', id);
            delete_overlay.setAttribute('data-id', id);
        } else {
            choose_video.classList.remove('hidden');
            selected_video.classList.add('hidden');
            delete_wrapper.classList.add('hidden');
            embed_wrap.innerHTML = '';
            selected_video.setAttribute('data-id', '');
            delete_wrapper.setAttribute('data-id', '');
            delete_overlay.setAttribute('data-id', '');
        }

        videoSave(data);
    }

    window.getVideoParams = getVideoParams;

    videoSave = function(params) {
        var selected_video = $(".selected-video");
        qcubed.recordControlModification("$this->ControlId", "_Items", params);
        var VideoSaveEvent = $.Event("videosave");
        selected_video.trigger(VideoSaveEvent);
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
                case 'CoverImagePath': return $this->strCoverImagePath;


                case "SelectedVideoAlt": return $this->strSelectedVideoAlt;
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
                case "SelectedSelectedId":
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
                case "CoverImagePath":
                    try {
                        $this->strCoverImagePath = Type::Cast($mixValue, Type::STRING);
                        $this->blnModified = true;
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->IncrementOffset();
                        throw $objExc;
                    }



                case "SelectedVideoAlt":
                    try {
                        $this->strSelectedVideoAlt = Type::Cast($mixValue, Type::STRING);
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
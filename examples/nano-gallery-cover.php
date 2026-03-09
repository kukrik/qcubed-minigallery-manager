<?php
    require('qcubed.inc.php');
    //require_once('../src/NanoGalleryCover.php');

    use QCubed as Q;

    use QCubed\Project\Control\FormBase as Form;
    use QCubed\Exception\Caller;

    /**
     * Handles the form and configuration characteristics for NanoGalleryCover.
     *
     * This class extends the base Form class to facilitate the initialization and configuration
     * of the NanoGalleryCover plugin, enabling media content cover customization and display.
     */
    class NanoGalleryCoverForm extends Form
    {
        protected string $strTempUrl = APP_UPLOADS_TEMP_URL;
        protected Q\Plugin\NanoGalleryCover $objCover;

        /**
         * Configures and initializes the NanoGalleryCover object for the component.
         *
         * This method sets up the NanoGalleryCover with predefined configurations, including
         * defining the content cover media ID, setting the item base URL, and configuring
         * the viewer toolbar and tools. Additionally, it specifies the default start item for the viewer.
         *
         * @return void This method does not return a value.
         * @throws Caller
         */
        protected function formCreate(): void
        {
            // For development purposes

            $this->objCover = new Q\Plugin\NanoGalleryCover($this);
            $this->objCover->ContentCoverMediaId = 281; //281; 268; 284 // content_cover_media.id
            $this->objCover->ItemsBaseURL = $this->strTempUrl . "/_files/large";
            $this->objCover->MultiImagesIconPath = QCUBED_QCUBED_MINIGALLERY_MANAGER_ASSETS_URL . "/images/multi-images-icon.svg";

            $this->objCover->ViewerToolbar = [
                "display" => true,
                "standard"=> "label",
                "fullWidth" => true,
            ];

            $this->objCover->ViewerTools = [
                "topLeft" => "pageCounter",
                "topRight" => "playPauseButton, zoomButton, rotateLeftButton, rotateRightButton, fullscreenButton, shareButton, downloadButton, closeButton"
            ];

            $this->objCover->StartItem = "0/1";

        }
    }
    NanoGalleryCoverForm::run('NanoGalleryCoverForm');

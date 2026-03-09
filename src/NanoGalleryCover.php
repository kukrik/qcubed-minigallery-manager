<?php
    namespace QCubed\Plugin;

    use QCubed\Exception\Caller;
    use QCubed\Project\Control\ControlBase;
    use QCubed\Project\Control\FormBase;

    /**
     * NanoGalleryCover class provides a specialized component for managing
     * and displaying a mini-gallery using the NanoGallery library.
     *
     * This class extends from NanoGalleryCoverGen, inheriting its properties
     * and methods, and adds custom functionalities required for the mini-gallery
     * manager. It also ensures the inclusion of required CSS and JavaScript files
     * for the proper functioning and styling of the gallery.
     */
    class NanoGalleryCover extends NanoGalleryCoverGen
    {
        /**
         * Constructor method for initializing the object.
         *
         * @param ControlBase|FormBase $objParentObject The parent object of this control.
         * @param string|null $strControlId Optional control ID for this control.
         *
         * @return void
         * @throws Caller
         */
        public function __construct(ControlBase|FormBase $objParentObject, ?string $strControlId = null)
        {
            parent::__construct($objParentObject, $strControlId);

            $this->registerFiles();
        }

        /**
         * Registers necessary CSS and JavaScript files for the mini-gallery manager.
         *
         * This method includes external and internal resources required for the
         * styling and functionality of the mini-gallery component.
         *
         * @return void
         * @throws Caller
         */
        protected function registerFiles(): void
        {
            $this->AddCssFile(QCUBED_MINIGALLERY_MANAGER_ASSETS_URL . "/nanogallery2/src/css/nanogallery2.css");
            $this->AddCssFile(QCUBED_MINIGALLERY_MANAGER_ASSETS_URL . "/css/nano-gallery-cover.css");
            $this->AddJavascriptFile(QCUBED_MINIGALLERY_MANAGER_ASSETS_URL . "/js/jquery.nanogallery2-3.0.5-patched.js");
            $this->AddJavascriptFile(QCUBED_MINIGALLERY_MANAGER_ASSETS_URL . "/js/nano-gallery-cover.js");
            $this->AddCssFile(QCUBED_BOOTSTRAP_CSS); // make sure they know
        }
    }

<?php

    namespace QCubed\Plugin;

    use QCubed\Control\Panel;
    use QCubed\Exception\Caller;
    use QCubed\Exception\InvalidCast;
    use QCubed\Type;

    /**
     * Class MiniGalleryGen
     * @see MiniGallery
     * @package QCubed\Plugin
     */

    /**
     * @property string $PopupUrl
     * @property string $PopupWidth
     * @property string $PopupHeight
     *
     * @package QCubed\Plugin
     */

    class MiniGalleryGen extends Panel
    {
        protected ?string $strPopupUrl = null;
        protected ?string $strPopupWidth = null;
        protected ?string $strPopupHeight = null;

        /**
         * Generates an array of jQuery options by inheriting from the parent method
         * and adding additional properties if they are set.
         *
         * @return array An associative array containing jQuery options,
         *               which may include `url`, `popupWidth`, and `popupHeight`.
         */
        protected function makeJqOptions(): array
        {
            $jqOptions = parent::MakeJqOptions();
            if (!is_null($val = $this->PopupUrl)) {$jqOptions['url'] = $val;}
            if (!is_null($val = $this->PopupWidth)) {$jqOptions['popupWidth'] = $val;}
            if (!is_null($val = $this->PopupHeight)) {$jqOptions['popupHeight'] = $val;}
            return $jqOptions;
        }

        /**
         * Returns the name of the jQuery setup function used for initialization.
         *
         * @return string The name of the jQuery setup function, which is `miniGallery'`.
         */
        protected function getJqSetupFunction(): string
        {
            return 'miniGallery';
        }

        /**
         * Magic method to retrieve the value of a property dynamically.
         * Handles specific cases for `PopupUrl`, `PopupWidth`, and `PopupHeight`,
         * and falls back to the parent implementation for other properties.
         *
         * @param string $strName The name of the property to retrieve.
         *
         * @return mixed The value of the requested property, or the result of the parent implementation.
         * @throws Caller If the property does not exist and cannot be resolved by the parent method.
         * @throws \Exception
         */
        public function __get(string $strName): mixed
        {
            switch ($strName) {
                case 'PopupUrl': return $this->strPopupUrl;
                case 'PopupWidth': return $this->strPopupWidth;
                case 'PopupHeight': return $this->strPopupHeight;

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
         * Sets the value of a property dynamically, performing type casting and
         * updating associated jQuery options when applicable.
         *
         * @param string $strName The name of the property to set.
         * @param mixed $mixValue The value to assign to the property.
         *
         * @return void
         *
         * @throws InvalidCast Thrown if the value cannot be cast to the required type.
         * @throws Caller Thrown if the property does not exist or cannot be set.
         * @throws \Exception
         */
        public function __set(string $strName, mixed $mixValue): void
        {
            switch ($strName) {
                case 'PopupUrl':
                    try {
                        $this->strPopupUrl = Type::Cast($mixValue, Type::STRING);
                        $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'popupUrl', $this->strPopupUrl);
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }
                case 'PopupWidth':
                    try {
                        $this->strPopupWidth = Type::Cast($mixValue, Type::STRING);
                        $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'popupWidth', $this->strPopupWidth);
                        break;
                    } catch (InvalidCast $objExc) {
                        $objExc->incrementOffset();
                        throw $objExc;
                    }
                case 'PopupHeight':
                    try {
                        $this->strPopupHeight = Type::Cast($mixValue, Type::STRING);
                        $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'popupHeight', $this->strPopupHeight);
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
    }
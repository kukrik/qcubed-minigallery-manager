<?php
    require_once('qcubed.inc.php');

    error_reporting(E_ALL); // Error engine - always ON!
    ini_set('display_errors', TRUE); // Error display - OFF in production env or real server
    ini_set('log_errors', TRUE); // Error logging

    use QCubed as Q;
    use QCubed\Control\TextBoxBase;
    use QCubed\Project\Control\FormBase as Form;
    use QCubed\Bootstrap as Bs;
    use QCubed\Exception\Caller;
    use QCubed\Exception\InvalidCast;
    use Random\RandomException;
    use QCubed\Event\Click;
    use QCubed\Action\Ajax;
    use QCubed\QDateTime;
    use QCubed\Action\ActionParams;
    use QCubed\Project\Application;

    /**
     * Class SampleForm
     *
     * Represents a form designed for file management and upload functionalities.
     * It includes various modals and components that facilitate operations such as
     * file uploading, renaming, moving, copying, deleting, and image cropping.
     * The form also integrates validation and error-handling features.
     *
     * Key attributes and components:
     * - Modals for providing feedback and handling specific actions (e.g., adding folders, cropping images).
     * - File upload handler to manage the uploading process with support for configurations (e.g., max file size).
     * - Buttons and labels for user interaction and form navigation.
     * - Text boxes and dropdowns for user inputs related to file and folder operations.
     * - Arrays and properties to manage file and directory states, allowed file formats, and temporary storage during interactions.
     *
     * This class extends the `Form` class and implements additional functionalities specific to file operations.
     */
    class SampleForm extends Form
    {
        protected Bs\Modal $dlgModal1; // CSRF Token is invalid

        protected Q\Plugin\Control\Label $lblTitle;
        protected Bs\TextBox $txtTitle;

        protected Q\Plugin\Control\Label $lblEmbedCode;
        protected Bs\TextBox $txtEmbedCode;

        protected Q\Plugin\Control\Label $lblVideo;
        protected Q\Plugin\Control\Label $strVideo;

        protected Q\Plugin\Control\Label $lblDescription;
        protected Bs\TextBox $txtDescription;

        protected Q\Plugin\Control\Label $lblAuthor;
        protected Bs\TextBox $txtAuthor;

        protected Bs\Button $btnEmbed;
        protected Bs\Button $btnReplace;
        protected Bs\Button $btnSave;
        protected Bs\Button $btnCancel;

        protected ?int $intId = null;
        protected ?int $intGroup = null;
        protected ?object $objVideo = null;
        protected ?object $objVideosSettings = null;
        protected ?int $intLoggedUserId = null;
        protected ?object $objUser = null;

        /**
         * Initializes and configures the form component for video management.
         *
         * @return void
         * @throws Caller
         * @throws \Throwable
         */
        protected function formCreate(): void
        {
            parent::formCreate();

            $this->intId = Application::instance()->context()->queryStringItem('id');
            $this->intGroup = Application::instance()->context()->queryStringItem('group');

            $this->objVideo = ContentCoverMedia::loadByIdFromPopupId($this->intId);

            $this->createInputs();
            $this->createButtons();
        }

        ///////////////////////////////////////////////////////////////////////////////////////////

        /**
         * Updates and saves the user's activity information.
         *
         * This method updates the user's last active timestamp to the current date and time
         * and saves the modified user object to persist the changes. It is typically used
         * to track the user's activity within the system.
         *
         * @return void
         */
        private function userOptions(): void
        {
            $this->objUser->setLastActive(QDateTime::now());
            $this->objUser->save();
        }

        ///////////////////////////////////////////////////////////////////////////////////////////

        /**
         * Initializes and configures various input controls for video-related data.
         *
         * This method creates and configures several input fields and labels, providing a user interface
         * for entering and displaying data such as video title, embed code, video content, description,
         * and author information. The labels serve as descriptors for each input field, while the text boxes
         * include predefined properties such as placeholder text, cross-scripting protection, autocomplete settings,
         * and styling attributes.
         *
         * @return void
         * @throws Caller
         */
        public function createInputs(): void
        {
            $this->lblTitle  = new Q\Plugin\Control\Label($this);
            $this->lblTitle->Text = t('Video title');
            $this->lblTitle->addCssClass('col-md-3');
            $this->lblTitle->setCssStyle('font-weight', 'normal');

            $this->txtTitle = new Bs\TextBox($this);
            $this->txtTitle->CrossScripting = TextBoxBase::XSS_HTML_PURIFIER;
            $this->txtTitle->Placeholder = t('Video title');
            $this->txtTitle->setHtmlAttribute('autocomplete', 'off');

            $this->lblEmbedCode  = new Q\Plugin\Control\Label($this);
            $this->lblEmbedCode->Text = t('Embed code (</>)');
            $this->lblEmbedCode->addCssClass('col-md-3');
            $this->lblEmbedCode->setCssStyle('font-weight', 'normal');

            $this->txtEmbedCode = new Bs\TextBox($this);
            $this->txtEmbedCode->CrossScripting = TextBoxBase::XSS_HTML_PURIFIER;
            $this->txtEmbedCode->Placeholder = t('Embed code (</>)');
            $this->txtEmbedCode->setHtmlAttribute('autocomplete', 'off');
            $this->txtEmbedCode->TextMode = TextBoxBase::MULTI_LINE;
            $this->txtEmbedCode->CrossScripting =  TextBoxBase::XSS_ALLOW;
            $this->txtEmbedCode->Rows = 2;
            $this->txtEmbedCode->Width = '80%';
            $this->txtEmbedCode->setCssStyle('float', 'left');

            $this->lblVideo  = new Q\Plugin\Control\Label($this);
            $this->lblVideo ->Text = t('Video');
            $this->lblVideo ->addCssClass('col-md-3');
            $this->lblVideo ->setCssStyle('font-weight', 'normal');

            $this->strVideo  = new Q\Plugin\Control\Label($this);
            $this->strVideo->HtmlEntities = false;

            $this->lblDescription  = new Q\Plugin\Control\Label($this);
            $this->lblDescription->Text = t('Description');
            $this->lblDescription->addCssClass('col-md-3');
            $this->lblDescription->setCssStyle('font-weight', 'normal');

            $this->txtDescription = new Bs\TextBox($this);
            $this->txtDescription->CrossScripting = TextBoxBase::XSS_HTML_PURIFIER;
            $this->txtDescription->Placeholder = t('Description');
            $this->txtDescription->setHtmlAttribute('autocomplete', 'off');
            $this->txtDescription->TextMode = TextBoxBase::MULTI_LINE;
            $this->txtDescription->CrossScripting =  TextBoxBase::XSS_ALLOW;
            $this->txtDescription->Rows = 2;
            $this->txtDescription->Width = '100%';
            $this->txtDescription->setCssStyle('float', 'left');

            $this->lblAuthor = new Q\Plugin\Control\Label($this);
            $this->lblAuthor->Text = t('Author');
            $this->lblAuthor->addCssClass('col-md-3');
            $this->lblAuthor->setCssStyle('font-weight', 'normal');

            $this->txtAuthor = new Bs\TextBox($this);
            $this->txtAuthor->CrossScripting = TextBoxBase::XSS_HTML_PURIFIER;
            $this->txtAuthor->Placeholder = t('Author');
            $this->txtAuthor->setHtmlAttribute('autocomplete', 'off');

            if (!is_null($this->objVideo)) {
                Application::executeJavaScript("
                    $('.js-video').removeClass('hidden');
                    $('.js-embed-code').addClass('hidden');
                ");

                $this->txtTitle->Text = $this->objVideo->getTitle() ?? null;
                $this->strVideo->Text = $this->objVideo->getVideoEmbed() ?? null;
                $this->txtDescription->Text = $this->objVideo->getDescription() ?? null;
                $this->txtAuthor->Text = $this->objVideo->getAuthor() ?? null;
            }
        }

        /**
         * Creates multiple button instances with preconfigured properties.
         *
         * This method initializes and configures various buttons for different purposes,
         * such as embedding content, saving data, deleting items, or canceling actions.
         * Each button instance is assigned specific properties such as text, CSS classes,
         * styles, and validation handling for tailored user interactions.
         *
         * @return void
         * @throws Caller
         */
        public function createButtons(): void
        {
            $this->btnEmbed = new Bs\Button($this);
            $this->btnEmbed->Text = t('Embed');
            $this->btnEmbed->CssClass = 'btn btn-orange';
            $this->btnEmbed->setCssStyle('float', 'right');
            $this->btnEmbed->addAction(new Click(), new Ajax('btnEmbed_Click'));

            $this->btnReplace = new Bs\Button($this);
            $this->btnReplace->Text = t('Replace video');
            $this->btnReplace->CssClass = 'btn btn-darkblue';
            $this->btnReplace->addAction(new Click(), new Ajax('btnReplace_Click'));

            $this->btnSave = new Bs\Button($this);
            $this->btnSave->Text = t('Save');
            $this->btnSave->CssClass = 'btn btn-orange';
            $this->btnSave->addAction(new Click(), new Ajax('btnSave_Click'));

            $this->btnCancel = new Bs\Button($this);
            $this->btnCancel->Text = t('Cancel');
            $this->btnCancel->CssClass = 'btn btn-default';
            $this->btnCancel->CausesValidation = false;
            $this->btnCancel->addAction(new Click(), new Ajax('btnCancel_Click'));

            if (!$this->strVideo->Text) {
                $this->btnReplace->Enabled = false;
                $this->btnSave->Enabled = false;
            }
        }

        ///////////////////////////////////////////////////////////////////////////////////////////

        /**
         * Handles the embed button click event, processing the provided embed code, storing it in session data,
         * updating relevant UI elements, and enabling associated buttons.
         *
         * @param ActionParams $params Parameters associated with the action event, including the context of the button click.
         *
         * @return void
         */
        protected function btnEmbed_Click(ActionParams $params): void
        {
            $cleanedEmbedCode = $this->cleanEmbedCode($this->txtEmbedCode->Text);
            $_SESSION['video_data'] = $cleanedEmbedCode;

            $this->strVideo->Text = $_SESSION['video_data'];

            if ($this->strVideo->Text) {
                Application::executeJavaScript("
                    $('.js-video').removeClass('hidden');
                    $('.js-embed-code').addClass('hidden');
                ");
            }

            if ($this->txtEmbedCode->Text) {
                $this->btnReplace->Enabled = true;
                $this->btnSave->Enabled = true;
            }
        }

        /**
         * Handles the click event for the "Replace" button. Resets video-related fields, updates UI elements,
         * and clears session data associated with video information.
         *
         * @param ActionParams $params Parameters passed along with the button click action.
         *
         * @return void
         */
        protected function btnReplace_Click(ActionParams $params): void
        {
            $this->txtEmbedCode->Text = '';
            $this->strVideo->Text = '';

            $this->btnReplace->Enabled = false;
            $this->btnSave->Enabled = false;

            Application::executeJavaScript("
                $('.js-video').addClass('hidden');
                $('.js-embed-code').removeClass('hidden');
            ");

            if (!empty($_SESSION['video_data'])) unset($_SESSION['video_data']);
        }

        ///////////////////////////////////////////////////////////////////////////////////////////

        /**
         * Handles the click event for the "Save" button.
         *
         * This method is responsible for processing and saving video-related data. It determines if a
         * new video record needs to be created or if an existing video record should be updated.
         * Once the data is saved, it encodes the relevant parameters into JSON format and triggers
         * client-side JavaScript to return the data to a parent window. The method also clears
         * any temporary session data related to the video.
         *
         * @param ActionParams $params The parameters associated with the button click action.
         *
         * @return void
         */
        public function btnSave_Click(ActionParams $params): void
        {
            if (!is_null($this->objVideo)){
                $this->objVideo->setVideoEmbed($this->strVideo->Text ?? $_SESSION['video_data']);
                $this->objVideo->setTitle($this->txtTitle->Text ?? null);
                $this->objVideo->setAuthor($this->txtAuthor->Text ?? null);
                $this->objVideo->setDescription($this->txtDescription->Text ?? null);
                $this->objVideo->setPostUpdateDate(QDateTime::now());
                $this->objVideo->save();
            }

            if (is_null($this->objVideo)) {
                $objContentCoverMedia = new ContentCoverMedia();
                $objContentCoverMedia->setContentId($this->intId); // Save the ID of the current page
                $objContentCoverMedia->setMenuContentId($this->intGroup); // Save the ID of the current menu tree
                $objContentCoverMedia->setMediaTypeId(3);
                $objContentCoverMedia->setVideoEmbed($this->strVideo->Text ?? $_SESSION['video_data']);
                $objContentCoverMedia->setTitle($this->txtTitle->Text ?? null);
                $objContentCoverMedia->setAuthor($this->txtAuthor->Text ?? null);
                $objContentCoverMedia->setDescription($this->txtDescription->Text ?? null);
                $objContentCoverMedia->setStatus(1);
                $objContentCoverMedia->setPostDate(QDateTime::now());
                $objContentCoverMedia->save();
            }

            $params = [
                "id" => $objContentCoverMedia->Id ?? !empty($this->objVideo->getId()),
                "embed" => $this->strVideo->Text ?? $_SESSION['video_data']
            ];

            $data = json_encode(
                $params,
                JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
                | JSON_HEX_TAG
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
                | JSON_HEX_AMP
            );

            if (!empty($_SESSION['video_data'])) unset($_SESSION['video_data']);

            $this->resetInputs();

            // Simulate the user action of selecting a file to be returned to VideoEmbed.
            Application::executeJavaScript(
                "window.parent.opener.getVideoParams(" . json_encode($data) . "); window.close();"
            );
        }

        /**
         * Handles the cancel button click event.
         *
         * This method is triggered when the cancel button is clicked. It performs the following actions:
         * - Sets the video embed text if a video object is available.
         * - Clears any session data related to video information.
         * - Executes a JavaScript command to close the current browser window.
         *
         * @param ActionParams $params The parameters associated with the button click event.
         *
         * @return void
         */
        public function btnCancel_Click(ActionParams $params): void
        {
            If (!empty($this->objVideo)) $this->strVideo->Text = $this->objVideo->getVideoEmbed();

            if (!empty($_SESSION['video_data'])) unset($_SESSION['video_data']);

            $this->resetInputs();

            Application::executeJavaScript("window.close();");
        }

        /**
         * Resets all input fields, buttons, and associated states to their initial values.
         *
         * This method clears text inputs, resets fields related to video and group properties,
         * disables specific buttons, and updates the UI by toggling visibility of certain elements
         * through JavaScript. It also nullifies associated variables for proper state management.
         *
         * @return void
         */
        public function resetInputs(): void
        {
            $this->txtTitle->Text = '';
            $this->txtEmbedCode->Text = '';
            $this->strVideo->Text = '';
            $this->txtDescription->Text = '';
            $this->txtAuthor->Text = '';

            $this->btnReplace->Enabled = false;
            $this->btnSave->Enabled = false;

            Application::executeJavaScript("
                $('.js-video').addClass('hidden');
                $('.js-embed-code').removeClass('hidden');
            ");

            $this->intId = null;
            $this->intGroup = null;
            $this->objVideo = null;
        }

        ///////////////////////////////////////////////////////////////////////////////////////////

        /**
         * Cleans the input embed code by removing unnecessary tags and attributes, ensuring a properly formatted iframe.
         *
         * @param string $code The embed code to be cleaned.
         *
         * @return string The cleaned and formatted embed code.
         */
        private function cleanEmbedCode(string $code): string
        {
            // Removes div tags and other wrappers
            $code = preg_replace('/<div[^>]*>|<\/div>/', '', $code);

            // Removes the width and height attributes from the iframe
            $code = preg_replace('/\s*(width|height)=["\'].*?["\']/', '', $code);

            // Ensures the iframe starts correctly
            $code = preg_replace('/^.*?(<iframe\b.*?>).*?(<\/iframe>).*$/s', '$1$2', $code);

            // Returns the cleaned embed code
            return trim($code);
        }
    }
    SampleForm::run('SampleForm');

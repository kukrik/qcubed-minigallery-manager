<?php
    require_once('qcubed.inc.php');

    require_once('../src/VauuTableCheckboxColumn.php');

    error_reporting(E_ALL); // Error engine - always ON!
    ini_set('display_errors', TRUE); // Error display - OFF in production env or real server
    ini_set('log_errors', TRUE); // Error logging

    use QCubed as Q;
    use QCubed\Control\TextBoxBase;
    use QCubed\Event\CheckboxColumnClick;
    use QCubed\Event\DialogButton;
    use QCubed\Folder;
    use QCubed\Plugin\Control\VauuTable;
    use QCubed\Project\Control\FormBase as Form;
    use QCubed\Bootstrap as Bs;
    use QCubed\Exception\Caller;
    use QCubed\Exception\InvalidCast;
    use QCubed\Query\QQ;
    use QCubed\Plugin\VauuTableCheckboxColumn;
    use QCubed\Event\Click;
    use QCubed\Action\Ajax;
    use QCubed\QDateTime;
    use QCubed\Action\ActionParams;
    use QCubed\Project\Application;

    /**
     * Class VauuCheckColumn
     *
     * Extends the functionality of the `VauuTableCheckboxColumn` to provide tailored handling
     * of checkbox columns within a table component. This includes functionality for selecting
     * and managing rows based on primary keys retrieved from a specific data source.
     *
     * Key features and functionalities:
     * - Provides a method to retrieve all primary keys of the associated data source.
     * - Designed for use within table components that require selection capabilities.
     * - Implements efficient querying of primary keys using the MiniGallery data model.
     *
     * This class extends the `VauuTableCheckboxColumn` to enhance table interaction with
     * a focus on streamlined and customizable row selection processes.
     */
    class VauuCheckColumn extends VauuTableCheckboxColumn {
        /**
         * Retrieves all the primary key IDs from the MiniGallery.
         *
         * @return array|null An array of IDs if present, or null if no IDs are found.
         * @throws Caller
         */
        protected function getAllIds(): ?array
        {
            return MiniGallery::queryPrimaryKeys();
        }
    }

    /**
     * Class MiniGalleryPageForm
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
     * - Arrays and properties to manage file and directory states, allowed file formats, and temporary storage during
     * interactions.
     *
     * This class extends the `Form` class and implements additional functionalities specific to file operations.
     */
    class MiniGalleryPageForm extends Form
    {
        protected string $strRootPath = APP_UPLOADS_DIR;
        protected string $strRootUrl = APP_UPLOADS_URL;
        protected string $strTempPath = APP_UPLOADS_TEMP_DIR;
        protected string $strTempUrl = APP_UPLOADS_TEMP_URL;
        public array $tempFolders = ['thumbnail', 'medium', 'large'];

        protected Q\Plugin\Toastr $dlgToastr1;
        protected Q\Plugin\Toastr $dlgToastr2;
        protected Q\Plugin\Toastr $dlgToastr3;
        protected Q\Plugin\Toastr $dlgToastr4;
        protected Q\Plugin\Toastr $dlgToastr5;
        protected Q\Plugin\Toastr $dlgToastr6;

        protected Bs\Modal $dlgModal1;
        protected Bs\Modal $dlgModal2;
        protected Bs\Modal $dlgModal3;
        protected Bs\Modal $dlgModal4;
        protected Bs\Modal $dlgModal5;
        protected Bs\Modal $dlgModal6;
        protected Bs\Modal $dlgModal7;

        protected Q\Plugin\Control\Alert $lblNoImages;
        protected Q\Plugin\Control\Alert $lblRegisterError;
        protected Bs\Button $btnGoUpload;

        protected Q\Plugin\Control\Label $lblPhotoDescription;
        protected Bs\TextBox $txtPhotoDescription;
        protected Q\Plugin\Control\Label $lblPhotoAuthor;
        protected Bs\TextBox $txtPhotoAuthor;
        protected Bs\Button $btnSaveMiniGallery;
        protected Bs\Button $btnCancelMiniGallery;

        ////////////////////////////////

        protected Q\Plugin\BsFileControl $btnAddFiles;
        protected Bs\Button $btnAllStart;
        protected Bs\Button $btnAllCancel;
        protected Bs\Button $btnBack;
        protected Bs\Button $btnDone;
        protected Q\Plugin\GalleryUploadHandler $objUpload;
        protected ?VauuTable $tblMiniGalleryList = null;
        protected VauuTableCheckboxColumn $colSelect;
        protected Bs\TextBox $txtFileName;
        protected Bs\TextBox $txtFileAuthor;
        protected Bs\TextBox $txtFileDescription;
        protected Q\Plugin\Control\RadioList $lstIsEnabled;
        protected Bs\Button $btnPhotoSave;
        protected Bs\Button $btnPhotoCancel;

        ////////////////////////////////

        protected Bs\Button $btnSave;
        protected Bs\Button $btnCancel;

        protected ?int $intId = null;
        protected ?int $intGroup = null;

        protected array $objRegisters = [];
        protected ?int $objMiniGalleryRegister = null;
        protected ?object $objContentCoverMedia = null;
        protected ?array $objMiniGallery = null;
        protected ?int $intChangeFilesId = null;
        protected bool $blnCoverSelected = false;
        protected ?int $intCoverGalleryId = null;

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

            $this->objContentCoverMedia = ContentCoverMedia::loadByIdFromPopupId($this->intId);

            if ($this->objContentCoverMedia) {
                $this->objMiniGallery = MiniGallery::loadArrayByContentCoverMediaId($this->objContentCoverMedia->getId());
            }

            if (!empty($_SESSION['logged_user_id'])) {
                $this->intLoggedUserId = $_SESSION['logged_user_id'];
                $this->objUser = User::load($this->intLoggedUserId);
            }

            $this->createTable();
            $this->createInputs();
            $this->createButtons();
            $this->createToastr();
            $this->createModals();
            $this->inputsStateControl();
            $this->createObjects();


            ///////////////////////////////////////////////////////////////////////////////////////////
            // IMPORTANT! This check below must not be removed. It checks whether the "mini-gallery" directory
            // has already been created. The resulting value is passed on to the necessary actions.

            $this->objRegisters = MiniGalleryRegister::loadAll();

            if (count($this->objRegisters) === 0) {
                $this->lblRegisterError->Display = true;
                $this->btnGoUpload->Enabled = false;
                $this->txtPhotoDescription->Enabled = false;
                $this->txtPhotoAuthor->Enabled = false;
                Application::executeJavaScript("$('.js-gallery-table').addClass('hidden');");
                return;
            }

            foreach ($this->objRegisters  as $objId) {
                if ($objId->getId()) {
                    $this->objMiniGalleryRegister = $objId->getFolderId();
                    break;
                }
            }

            ///////////////////////////////////////////////////////////////////////////////////////////

            if ($this->objContentCoverMedia === null) {
                $registerPath = Folders::loadById($this->objMiniGalleryRegister);
                $fullPath = $this->strRootPath . $registerPath->getPath();

                $maxNumber = 0;

                if (is_dir($fullPath)) {
                    foreach (scandir($fullPath) as $item) {
                        if ($item === '.' || $item === '..') {
                            continue;
                        }

                        $itemPath = $fullPath . '/' . $item;

                        if (is_dir($itemPath) && ctype_digit($item)) {
                            $number = (int)$item;
                            if ($number > $maxNumber) {
                                $maxNumber = $number;
                            }
                        }
                    }
                }

                // Next free number
                $newFolderNumber = $maxNumber + 1;
                $newFolderName   = (string)$newFolderNumber;
                $newPath         = $fullPath . '/' . $newFolderName;

                $relativePath = $registerPath->getPath() . '/' . $newFolderName;

                if (!is_dir($newPath)) {
                    Folder::makeDirectory($newPath, 0777);
                }

                foreach ($this->tempFolders as $tempFolder) {
                    $tempPath = $this->strTempPath . '/_files/' . $tempFolder . $relativePath;
                    Folder::makeDirectory($tempPath, 0777);
                }

                $objAddFolder = new Folders();
                $objAddFolder->setParentId($this->objMiniGalleryRegister);
                $objAddFolder->setPath($relativePath);
                $objAddFolder->setName($newFolderName);
                $objAddFolder->setType('dir');
                $objAddFolder->setMtime(filemtime($newPath));
                $objAddFolder->setLockedFile(0);
                $objAddFolder->setActivitiesLocked(1);
                $objAddFolder->save();

                $_SESSION['folderId']   = $objAddFolder->getId();
                $_SESSION['folderPath'] = $relativePath;

                $objLockedFolder = Folders::loadById($this->objMiniGalleryRegister);

                if ($objLockedFolder->getLockedFile() == 0) {
                    $objLockedFolder->setLockedFile(1);
                    $objLockedFolder->setMtime(filemtime($this->strRootPath . '/' . $objLockedFolder->getPath()));
                    $objLockedFolder->save();
                }

                 $objAddMedia = new ContentCoverMedia();
                 $objAddMedia->setContentId($this->intId); // Save the ID of the current page
                 $objAddMedia->setMenuContentId($this->intGroup); // Save the ID of the current menu tree
                 $objAddMedia->setMediaTypeId(2); // Save the ID of the media type;
                 $objAddMedia->setFolderId($objAddFolder->getId());
                 $objAddMedia->setFolderPath($relativePath);
                 $objAddMedia->setStatus(1);
                 $objAddMedia->setPostDate(QDateTime::now());
                 $objAddMedia->save();

                $_SESSION['coverMediaId'] = $objAddMedia->getId();
            }

            if (!$this->objMiniGallery) {
                $this->lblNoImages->Display = true;
                $this->txtPhotoDescription->Enabled = false;
                $this->txtPhotoAuthor->Enabled = false;
                Application::executeJavaScript("$('.js-gallery-table').addClass('hidden');");
            }
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

        /**
         * Retrieves the active cover media ID.
         *
         * @return int|null The ID of the active cover media if available, either from the objContentCoverMedia object
         *     or from the session variable 'coverMediaId'. Returns null if no active cover media ID is found.
         */
        protected function getActiveCoverMediaId(): ?int
        {
            if ($this->objContentCoverMedia && $this->objContentCoverMedia->getId()) {
                return (int)$this->objContentCoverMedia->getId();
            }

            return isset($_SESSION['coverMediaId'])
                ? (int)$_SESSION['coverMediaId']
                : null;
        }

        /**
         * Controls the input elements based on the state of the MiniGallery object.
         *
         * If the MiniGallery object is null or unavailable, disables various form inputs,
         * displays a "no images" message, and updates the UI to hide the gallery table.
         *
         * @return void This method does not return a value.
         * @throws Caller
         */
        private function inputsStateControl(): void
        {
            if (!$this->objContentCoverMedia) {
                return;
            }

            $previewId = $this->objContentCoverMedia->getPreviewFileId();

            if ($previewId) {
                $this->btnSave->Enabled = true;
                Application::executeJavaScript("syncMiniGalleryCoverState($previewId);");
            } else {
                $this->btnSave->Enabled = false;
                Application::executeJavaScript("syncMiniGalleryCoverState(null);");
            }
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
            $this->lblNoImages = new Q\Plugin\Control\Alert($this);
            $this->lblNoImages->Dismissable = true;
            $this->lblNoImages->addCssClass('alert alert-info alert-dismissible');
            $this->lblNoImages->Text = t('There are no images in the mini gallery. Click the "Go to upload area" button to upload images.');
            $this->lblNoImages->Display = false;

            $this->lblRegisterError = new Q\Plugin\Control\Alert($this);
            $this->lblRegisterError->Dismissable = true;
            $this->lblRegisterError->addCssClass('alert alert-danger alert-dismissible');
            $this->lblRegisterError->Text = t('<p><strong>Important!</strong> The "mini-gallery" directory has not been created automatically.</p>
                                               <p>Please open the file manager once and then return to this page.</p>');
            $this->lblRegisterError->Display = false;

            $this->lblPhotoDescription = new Q\Plugin\Control\Label($this);
            $this->lblPhotoDescription->Text = t('Brief description');
            $this->lblPhotoDescription->addCssClass('col-md-3');
            $this->lblPhotoDescription->setCssStyle('font-weight', 'normal');

            $this->txtPhotoDescription = new Bs\TextBox($this);
            $this->txtPhotoDescription->Text = $this->objContentCoverMedia->Description ?? '';
            $this->txtPhotoDescription->TextMode = TextBoxBase::MULTI_LINE;
            $this->txtPhotoDescription->CrossScripting = TextBoxBase::XSS_HTML_PURIFIER;

            $this->lblPhotoAuthor = new Q\Plugin\Control\Label($this);
            $this->lblPhotoAuthor->Text = t('Photo author/source');
            $this->lblPhotoAuthor->addCssClass('col-md-3');
            $this->lblPhotoAuthor->setCssStyle('font-weight', 'normal');

            $this->txtPhotoAuthor = new Bs\TextBox($this);
            $this->txtPhotoAuthor->Text = $this->objContentCoverMedia->Author ?? '';
            $this->txtPhotoAuthor->CrossScripting = TextBoxBase::XSS_HTML_PURIFIER;

            ///////////////////////////////////////////////////////////////////////////////////////////

            $this->txtFileName = new Bs\TextBox($this->tblMiniGalleryList);
            $this->txtFileName->setHtmlAttribute('required', 'required');
            $this->txtFileName->CrossScripting = TextBoxBase::XSS_HTML_PURIFIER;

            $this->txtFileDescription = new Bs\TextBox($this->tblMiniGalleryList);
            $this->txtFileDescription->TextMode = TextBoxBase::MULTI_LINE;
            $this->txtFileDescription->Rows = 2;
            $this->txtFileDescription->CrossScripting = TextBoxBase::XSS_HTML_PURIFIER;

            $this->txtFileAuthor = new Bs\TextBox($this->tblMiniGalleryList);
            $this->txtFileAuthor->CrossScripting = TextBoxBase::XSS_HTML_PURIFIER;

            $this->lstIsEnabled = new Q\Plugin\Control\RadioList($this->tblMiniGalleryList);
            $this->lstIsEnabled->addItems([1 => t('Published'), 2 => t('Hidden')]);
            $this->lstIsEnabled->ButtonGroupClass = 'radio radio-orange'; //  radio-inline
            //$this->lstIsEnabled->AddAction(new Change(), new Ajax('lstIsEnabled_Change'));

        ///////////////////////////////////////////////////////////////////////////////////////////

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
            $this->btnAddFiles = new Q\Plugin\BsFileControl($this, 'files');
            $this->btnAddFiles->Text = t(' Add files');
            $this->btnAddFiles->Glyph = 'fa fa-upload';
            $this->btnAddFiles->Multiple = true;
            $this->btnAddFiles->CssClass = 'btn btn-orange fileinput-button';
            $this->btnAddFiles->setCssStyle('float', 'left');
            $this->btnAddFiles->setCssStyle('margin-right', '10px');
            $this->btnAddFiles->UseWrapper = false;
            $this->btnAddFiles->addAction(new Click(), new Ajax('uploadStart_Click'));

            $this->btnAllStart = new Bs\Button($this);
            $this->btnAllStart->Text = t('Start upload');
            $this->btnAllStart->CssClass = 'btn btn-darkblue all-start';
            $this->btnAllStart->setCssStyle('float', 'left');
            $this->btnAllStart->setCssStyle('margin-right', '10px');
            $this->btnAllStart->PrimaryButton = true;
            $this->btnAllStart->UseWrapper = false;

            $this->btnAllCancel = new Bs\Button($this);
            $this->btnAllCancel->Text = t('Cancel all uploads');
            $this->btnAllCancel->CssClass = 'btn btn-warning all-cancel';
            $this->btnAllCancel->setCssStyle('float', 'left');
            $this->btnAllCancel->setCssStyle('margin-right', '10px');
            $this->btnAllCancel->UseWrapper = false;

            $this->btnBack = new Bs\Button($this);
            $this->btnBack->Text = t('Back to the editing view');
            $this->btnBack->CssClass = 'btn btn-default back';
            $this->btnBack->setCssStyle('float', 'left');
            $this->btnBack->UseWrapper = false;
            $this->btnBack->addAction(new Click(), new Ajax('btnBack_Click'));

            $this->btnDone = new Bs\Button($this);
            $this->btnDone->Text = t('Done');
            $this->btnDone->CssClass = 'btn btn-success pull-right done';
            $this->btnDone->UseWrapper = false;
            $this->btnDone->addAction(new Click(), new Ajax('btnDone_Click'));

            ///////////////////////////////////////////////////////////////////////////////////////////

            $this->btnGoUpload = new Bs\Button($this);
            $this->btnGoUpload->Text = t('Go to the upload area');
            $this->btnGoUpload->CssClass = 'btn btn-orange';
            $this->btnGoUpload->setCssStyle('float', 'left');
            $this->btnGoUpload->CausesValidation = false;
            $this->btnGoUpload->UseWrapper = false;
            $this->btnGoUpload->addAction(new Click(), new Ajax('btnGoUpload_Click'));

            ///////////////////////////////////////////////////////////////////////////////////////////

            $this->btnPhotoSave = new Bs\Button($this->tblMiniGalleryList);
            $this->btnPhotoSave->Text = t('Save');
            $this->btnPhotoSave->CssClass = 'btn btn-orange';
            $this->btnPhotoSave->addAction(new Click(), new Ajax('btnPhotoSave_Click'));

            $this->btnPhotoCancel = new Bs\Button($this->tblMiniGalleryList);
            $this->btnPhotoCancel->Text = t('Cancel');
            $this->btnPhotoCancel->addAction(new Click(), new Ajax('btnPhotoCancel_Click'));
            $this->btnPhotoCancel->CausesValidation = false;

            ///////////////////////////////////////////////////////////////////////////////////////////

            $this->btnSave = new Bs\Button($this);
            $this->btnSave->Text = t('Save');
            $this->btnSave->CssClass = 'btn btn-orange';
            $this->btnSave->addAction(new Click(), new Ajax('btnSave_Click'));
            $this->btnSave->Enabled = false;

            $this->btnCancel = new Bs\Button($this);
            $this->btnCancel->Text = t('Cancel');
            $this->btnCancel->CssClass = 'btn btn-default';
            $this->btnCancel->CausesValidation = false;
            $this->btnCancel->addAction(new Click(), new Ajax('btnCancel_Click'));
        }

        /**
         * Initializes and configures multiple Toastr notifications for various application events.
         * Sets up messages, alert types, positions, and progress bar visibility for each notification.
         *
         * @return void This method does not return a value.
         * @throws Caller
         */
        protected function createToastr(): void
        {
            $this->dlgToastr1 = new Q\Plugin\Toastr($this);
            $this->dlgToastr1->AlertType = Q\Plugin\ToastrBase::TYPE_SUCCESS;
            $this->dlgToastr1->PositionClass = Q\Plugin\ToastrBase::POSITION_TOP_CENTER;
            $this->dlgToastr1->Message = t('<strong>Well done!</strong> File changed successfully.');
            $this->dlgToastr1->ProgressBar = true;

            $this->dlgToastr2 = new Q\Plugin\Toastr($this);
            $this->dlgToastr2->AlertType = Q\Plugin\ToastrBase::TYPE_ERROR;
            $this->dlgToastr2->PositionClass = Q\Plugin\ToastrBase::POSITION_TOP_CENTER;
            $this->dlgToastr2->Message = t('<strong>Sorry!</strong> Failed to change file.');
            $this->dlgToastr2->ProgressBar = true;

            $this->dlgToastr3 = new Q\Plugin\Toastr($this);
            $this->dlgToastr3->AlertType = Q\Plugin\ToastrBase::TYPE_SUCCESS;
            $this->dlgToastr3->PositionClass = Q\Plugin\ToastrBase::POSITION_TOP_CENTER;
            $this->dlgToastr3->Message = t('<strong>Well done!</strong> File deleted successfully.');
            $this->dlgToastr3->ProgressBar = true;

            $this->dlgToastr4 = new Q\Plugin\Toastr($this);
            $this->dlgToastr4->AlertType = Q\Plugin\ToastrBase::TYPE_ERROR;
            $this->dlgToastr4->PositionClass = Q\Plugin\ToastrBase::POSITION_TOP_CENTER;
            $this->dlgToastr4->Message = t('<strong>Sorry!</strong> Failed to delete file.');
            $this->dlgToastr4->ProgressBar = true;

            $this->dlgToastr5 = new Q\Plugin\Toastr($this);
            $this->dlgToastr5->AlertType = Q\Plugin\ToastrBase::TYPE_SUCCESS;
            $this->dlgToastr5->PositionClass = Q\Plugin\ToastrBase::POSITION_TOP_CENTER;
            $this->dlgToastr5->Message = t('<strong>Well done!</strong> Mini gallery description/author has been successfully saved or edited!');
            $this->dlgToastr5->ProgressBar = true;

            $this->dlgToastr6 = new Q\Plugin\Toastr($this);
            $this->dlgToastr6->AlertType = Q\Plugin\ToastrBase::TYPE_INFO;
            $this->dlgToastr6->PositionClass = Q\Plugin\ToastrBase::POSITION_TOP_CENTER;
            $this->dlgToastr6->Message = t('<strong>Well done!</strong> The mini gallery description/author has been restored!');
            $this->dlgToastr6->ProgressBar = true;
        }
        
        /**
         * Creates and configures multiple modal dialogs to be used within the application.
         *
         * This method initializes four modal dialogs with different titles, text content, header styles, and buttons. Each modal
         * is configured for a specific purpose, such as providing tips, warnings, or confirming user actions.
         *
         * @return void
         * @throws Caller
         */
        protected function createModals(): void
        {
            $this->dlgModal1 = new Bs\Modal($this);
            $this->dlgModal1->Title = t('Tip');
            $this->dlgModal1->Text = t('<p style="margin-top: 15px;">Please select a cover image!</p>');
            $this->dlgModal1->HeaderClasses = 'btn-darkblue';
            $this->dlgModal1->addCloseButton(t("I understand"));

            $this->dlgModal2 = new Bs\Modal($this);
            $this->dlgModal2->Title = t('Tip');
            $this->dlgModal2->Text = t('<p style="margin-top: 15px;">A mini gallery requires two or more images to work correctly.</p>
                                        <p style="margin-top: 25px; margin-bottom: 2px;">The cover image indicates to visitors that additional images are available.</p>
                                        <p style="margin-top: 25px; margin-bottom: -3px;">If you only want to display a single image, please close this window and select the "Image" media type.</p>');
            $this->dlgModal2->HeaderClasses = 'btn-darkblue';
            $this->dlgModal2->addButton(t("I understand"), null, false, false, null,
                ['data-dismiss'=>'modal', 'class' => 'btn btn-orange']);

            $this->dlgModal3 = new Bs\Modal($this);
            $this->dlgModal3->Title = t('Tip');
            $this->dlgModal3->Text = t('<p style="margin-top: 15px; margin-bottom: 2px;">This image cannot be deleted because it is currently used as the cover image.</p>
                                        <p style="margin-top: 15px; margin-bottom: -3px;">To delete this image, please select another image as the cover image first.</p>');
            $this->dlgModal3->HeaderClasses = 'btn-darkblue';
            $this->dlgModal3->addButton(t("OK"), null, false, false, null,
                ['data-dismiss'=>'modal', 'class' => 'btn btn-orange']);

            $this->dlgModal4 = new Bs\Modal($this);
            $this->dlgModal4->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">Are you sure you want to permanently delete this file?</p>
                                <p style="line-height: 25px; margin-bottom: -3px;">This action cannot be undone.</p>');
            $this->dlgModal4->Title = 'Warning';
            $this->dlgModal4->HeaderClasses = 'btn-danger';
            $this->dlgModal4->addButton("I accept", 'This file has been permanently deleted', false, false, null,
                ['class' => 'btn btn-orange']);
            $this->dlgModal4->addCloseButton(t("I'll cancel"));
            $this->dlgModal4->addAction(new DialogButton(), new Ajax('photoDeleteItem_Click'));

            $this->dlgModal5 = new Bs\Modal($this);
            $this->dlgModal5->Text = t('<p style="line-height: 15px; margin-bottom: 2px;">File cannot be updated without name!</p>');
            $this->dlgModal5->Title = t("Tip");
            $this->dlgModal5->HeaderClasses = 'btn-darkblue';
            $this->dlgModal5->addCloseButton(t("I close the window"));

            $this->dlgModal6 = new Bs\Modal($this);
            $this->dlgModal6->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">Cannot create a file with the same name!</p>');
            $this->dlgModal6->Title = t("Warning");
            $this->dlgModal6->HeaderClasses = 'btn-danger';
            $this->dlgModal6->addCloseButton(t("I understand"));

            $this->dlgModal7 = new Bs\Modal($this);
            $this->dlgModal7->Title = t('Tip');
            $this->dlgModal7->Text = t('<p style="margin-top: 15px;">If the brief  description is already filled in and 
                                    the author\'s name/source is not provided, the image description will not be displayed 
                                    under the image in the gallery!</p>
                                    <p style="margin-top: 25px; margin-bottom: 15px;">Please write only the author\'s 
                                    name/source text or fill in both fields!</p>');
            $this->dlgModal7->HeaderClasses = 'btn-darkblue';
            $this->dlgModal7->addCloseButton(t("I close the window"));
        }

        /**
         * Initializes and configures the necessary upload handler object for handling file uploads.
         *
         * This method sets up the file upload handler with specific configurations such as
         * accepted file types, language, and URL endpoint. Default values for various properties
         * are overridden as needed.
         *
         * @return void
         * @throws Caller
         */
        public function createObjects(): void
        {
            $this->objUpload = new Q\Plugin\GalleryUploadHandler($this);
            $this->objUpload->Language = $this->objUser->PreferredLanguageObject->Code ?? 'en';
            //$this->objUpload->ShowIcons = true; // Default false
            $this->objUpload->AcceptFileTypes = ['jpg', 'jpeg', 'bmp', 'png', 'webp', 'gif']; // Default null
            //$this->objUpload->MaxNumberOfFiles = 5; // Default null
            //$this->objUpload->MaxFileSize = 1024 * 1024 * 2; // 2 MB // Default null
            //$this->objUpload->MinFileSize = 500000; // 500 kb // Default null
            //$this->objUpload->ChunkUpload = false; // Default true
            //$this->objUpload->MaxChunkSize = 1024 * 1024 * 2; //* 10; // 10 MB // Default 5 MB
            //$this->objUpload->LimitConcurrentUploads = 10; // Default 2
            $this->objUpload->Url = 'php/mini_gallery_upload.php'; // Default null
            //$this->objUpload->PreviewMaxWidth = 120; // Default 80
            //$this->objUpload->PreviewMaxHeight = 120; // Default 80
            //$this->objUpload->WithCredentials = true; // Default false
        }

        ///////////////////////////////////////////////////////////////////////////////////////////

        /**
         * Handles the click event for the "Go Upload" button.
         *
         * This method is responsible for toggling visibility between gallery edit and upload forms
         * by adding or removing specific CSS classes through JavaScript execution. It ensures the
         * user interface reflects the appropriate state for uploading images to the gallery.
         *
         * @param ActionParams $params An object that contains parameters related to the triggering action.
         *
         * @return void
         * @throws Caller
         */
        public function btnGoUpload_Click(ActionParams $params): void
        {
            Application::executeJavaScript("
                $('.js-gallery-edit-form').addClass('hidden');
                $('.js-gallery-upload-form').removeClass('hidden');
            ");

            $this->userOptions();
        }

        /**
         * Initiates the upload process by setting session variables related to the content cover media.
         *
         * @param ActionParams $params The parameters associated with the action triggering the upload.
         *
         * @return void This method does not return a value but sets session variables for the content cover media context.
         */
        protected function uploadStart_Click(ActionParams $params): void
        {
            if ($this->objContentCoverMedia) {
                $_SESSION['coverMediaId'] = $this->objContentCoverMedia->getId();
                $_SESSION['folderId']   = $this->objContentCoverMedia->getFolderId();
                $_SESSION['folderPath'] = $this->objContentCoverMedia->getFolderPath();
            }

            $this->userOptions();
        }

        /**
         * Handles the click event for the "Done" button, updating the post-update date of the content cover media if it exists.
         *
         * @param ActionParams $params The parameters associated with the button click action.
         *
         * @return void This method does not return a value.
         * @throws Caller
         * @throws InvalidCast
         */
        protected function btnDone_Click(ActionParams $params): void
        {
            $coverMediaId = $this->getActiveCoverMediaId();

            if (!$coverMediaId) return;

            $objContentCoverMedia = ContentCoverMedia::load($coverMediaId);
            $objContentCoverMedia->setPostUpdateDate(QDateTime::now());
            $objContentCoverMedia->save();

            $this->txtPhotoDescription->Enabled = true;
            $this->txtPhotoAuthor->Enabled = true;

            $this->userOptions();
        }

        /**
         * Handles the "Back" button click event.
         *
         * This method manages the visibility of specific elements when the "Back" button is clicked.
         * It uses JavaScript to toggle the visibility of gallery-related forms, making the edit form visible
         * while hiding the upload form.
         *
         * @param ActionParams $params The parameters associated with the triggered action event.
         *
         * @return void
         * @throws Caller
         */
        protected function btnBack_Click(ActionParams $params): void
        {
            Application::executeJavaScript("
                $('.js-gallery-edit-form').removeClass('hidden');
                $('.js-gallery-table').removeClass('hidden');
                $('.js-gallery-upload-form').addClass('hidden');
                $('.alert-info, .alert-warning').remove();
                window.location.href = window.location.href;
            ");
        }

        ///////////////////////////////////////////////////////////////////////////////////////////

        /**
         * Handles the save button click action for updating the mini gallery.
         * Validates the presence of both the photo author and description, and updates the corresponding content cover media record if changes are detected.
         *
         * @param ActionParams $params The parameters related to the button click action, including the action parameter.
         *
         * @return void This method does not return a value.
         * @throws Caller
         * @throws InvalidCast
         */
        public function btnSaveMiniGallery_Click(ActionParams $params): void
        {
            $coverMediaId = $this->getActiveCoverMediaId();

            if (!$coverMediaId) return;

            if ((!$this->txtPhotoAuthor->Text && $this->txtPhotoDescription->Text) || ((!$this->txtPhotoDescription->Text) && $this->txtPhotoAuthor->Text)) {
                $this->dlgModal7->showDialogBox();
                return;
            }

            $objContentCoverMedia = ContentCoverMedia::load($coverMediaId);

            if ($this->txtPhotoDescription->Text !== $objContentCoverMedia->getDescription() ||
                $this->txtPhotoAuthor->Text !== $objContentCoverMedia->getAuthor()) {

                $objContentCoverMedia->setDescription($this->txtPhotoDescription->Text);
                $objContentCoverMedia->setAuthor($this->txtPhotoAuthor->Text);
            }

            $objContentCoverMedia->setPostUpdateDate(QDateTime::now());
            $objContentCoverMedia->save();

            $this->userOptions();

            $this->dlgToastr5->notify();
        }

        /**
         * Handles the cancel button click action for the mini gallery.
         * Resets the photo description field to match the currently active cover media's description.
         *
         * @param ActionParams $params The parameters associated with the button click action.
         *
         * @return void This method does not return a value.
         * @throws Caller
         * @throws InvalidCast
         */
        public function btnCancelMiniGallery_Click(ActionParams $params): void
        {
            $coverMediaId = $this->getActiveCoverMediaId();

            if (!$coverMediaId) return;

            $objContentCoverMedia = ContentCoverMedia::load($coverMediaId);

            $this->txtPhotoDescription->Text = $objContentCoverMedia->getDescription();
            $this->txtPhotoAuthor->Text = $objContentCoverMedia->getAuthor();

            $this->userOptions();

            $this->dlgToastr6->notify();
        }

        ///////////////////////////////////////////////////////////////////////////////////////////

        /**
         * Creates and configures a table for displaying mini gallery data.
         *
         * This method initializes a new VauuTable instance and sets its properties,
         * including CSS class and data binding functionality. It defines several
         * callable columns for the table, specifying their display names, rendering
         * logic, and styling attributes.
         *
         * @return void
         * @throws Caller
         * @throws InvalidCast
         */
        protected function createTable(): void
        {
            $this->tblMiniGalleryList = new VauuTable($this);
            $this->tblMiniGalleryList->CssClass = "table vauu-table table-hover";

            $col = $this->tblMiniGalleryList->createCallableColumn(t('View'), [$this, 'View_render']);
            $col->HtmlEntities = false;
            $col->CellStyler->Width = '8%';

            $col = $this->tblMiniGalleryList->createCallableColumn(t('Name'), [$this, 'Name_render']);
            $col->HtmlEntities = false;
            $col->CellStyler->Width = '17%';

            $col = $this->tblMiniGalleryList->createCallableColumn(t('Author'), [$this, 'PhotoAuthor_render']);
            $col->HtmlEntities = false;
            $col->CellStyler->Width = '18%';

            $col = $this->tblMiniGalleryList->createCallableColumn(t('Brief description'), [$this, 'PhotoDescription_render']);
            $col->HtmlEntities = false;
            $col->CellStyler->Width = '25%';

            $col = $this->tblMiniGalleryList->createCallableColumn(t('Status'), [$this, 'IsEnabled_render']);
            $col->HtmlEntities = false;
            $col->CellStyler->Width = '10%';

            $col = $this->tblMiniGalleryList->createCallableColumn(t('Actions'), [$this, 'Change_render']);
            $col->HtmlEntities = false;
            $col->CellStyler->Width = '20%';

            $this->colSelect = new VauuCheckColumn('');
            $this->colSelect->CheckboxClass = 'checkbox checkbox-orange';
            $this->colSelect->CellStyler->Width = '2%';

            $this->tblMiniGalleryList->addColumnAt(0, $this->colSelect);
            $this->tblMiniGalleryList->addAction(new CheckboxColumnClick(), new Ajax ('chkSelected_Click'));

            $this->tblMiniGalleryList->UseAjax = true;
            $this->tblMiniGalleryList->setDataBinder('tblMiniGalleryList_Bind');
        }

        /**
         * Binds data to the `tblMiniGalleryList` table by setting its data source based on the current
         * mini-gallery or session content cover media ID. The data source is fetched using the associated
         * cover media ID and ordered by album name.
         *
         * @return void
         * @throws Caller
         * @throws InvalidCast
         */

        public function tblMiniGalleryList_Bind(): void
        {
            $coverMediaId = $this->getActiveCoverMediaId();

            if (!$coverMediaId) {
                $this->tblMiniGalleryList->DataSource = [];
                return;
            }

            $this->tblMiniGalleryList->DataSource =
                MiniGallery::loadArrayByContentCoverMediaId(
                    $coverMediaId,
                    QQ::Clause(
                        QQ::orderBy(QQN::MiniGallery()->Name)
                    )
                );
        }

        /**
         * Renders the HTML for a preview of a mini gallery.
         *
         * @param MiniGallery $objMiniGallery An instance of MiniGallery containing the data required to generate the preview.
         *
         * @return string The rendered HTML string for the mini gallery preview.
         */
        public function View_render(MiniGallery $objMiniGallery): string
        {
            $strHtm = '<span class="preview">';
            $strHtm .= '<img src="' . $this->strTempUrl . '/_files/thumbnail' . $objMiniGallery->Path . '">';
            $strHtm .= '</span>';
            return $strHtm;
        }

        /**
         * Renders the name of the provided MiniGallery object.
         *
         * @param MiniGallery $objMiniGallery The MiniGallery object whose name will be rendered.
         *
         * @return string The rendered name, either from the txtFileName if conditions are met or the formatted name
         *     otherwise.
         * @throws Caller
         */
        public function Name_render(MiniGallery $objMiniGallery): string
        {
            if ($objMiniGallery->Id == $this->intChangeFilesId) {
                return $this->txtFileName->render(false);
            } else {
                // return QCubed::truncate($objAlbum->Name, 25);
                return wordwrap($objMiniGallery->Name, 25, "\n", true);
            }
        }

        /**
         * Renders the description of the provided MiniGallery object.
         *
         * @param MiniGallery $objMiniGallery The MiniGallery object whose description will be rendered.
         *
         * @return string|null The rendered description, either from txtFileDescription if conditions are met or the object's
         *     description otherwise. May return null if no description is available.
         * @throws Caller
         */
        public function PhotoDescription_render(MiniGallery $objMiniGallery): ?string
        {
            if ($objMiniGallery->Id == $this->intChangeFilesId) {
                return $this->txtFileDescription->render(false);
            } else {
                return $objMiniGallery->Description;
            }
        }

        /**
         * Renders the author of the provided MiniGallery object.
         *
         * @param MiniGallery $objMiniGallery The MiniGallery object whose author will be rendered.
         *
         * @return string|null The rendered author, either from the txtFileAuthor if conditions are met or the original
         *     author otherwise.
         * @throws Caller
         */
        public function PhotoAuthor_render(MiniGallery $objMiniGallery): ?string
        {
            if ($objMiniGallery->Id == $this->intChangeFilesId) {
                return $this->txtFileAuthor->render(false);
            } else {
                return $objMiniGallery->Author;
            }
        }

        /**
         * Renders the enabled status of the provided MiniGallery object.
         *
         * @param MiniGallery $objMiniGallery The MiniGallery object whose enabled status will be rendered.
         *
         * @return string The rendered status, either from the lstIsEnabled if conditions are met or the object's status otherwise.
         * @throws Caller
         */
        public function IsEnabled_render(MiniGallery $objMiniGallery): string
        {
            if ($objMiniGallery->Id == $this->intChangeFilesId) {
                return $this->lstIsEnabled->render(false);
            } else {
                return $objMiniGallery->StatusObject;
            }
        }

        /**
         * Renders the appropriate controls or buttons for the provided MiniGallery object.
         *
         * @param MiniGallery $objMiniGallery The MiniGallery object for which the controls will be rendered.
         *
         * @return string The rendered controls, which include the photo save and cancel buttons if conditions are met,
         *     or dynamically created "Change" and "Delete" buttons otherwise.
         * @throws Caller
         */
        public function Change_render(MiniGallery $objMiniGallery): string
        {
            if ($objMiniGallery->Id == $this->intChangeFilesId) {
                return $this->btnPhotoSave->render(false) . ' ' . $this->btnPhotoCancel->render(false);
            } else {
                $btnChangeId = 'btnChange' . $objMiniGallery->Id;
                $btnChange = $this->getControl($btnChangeId);
                if (!$btnChange) {
                    $btnChange = new Bs\Button($this->tblMiniGalleryList, $btnChangeId);
                    $btnChange->Text = t('Change');
                    $btnChange->ActionParameter = $objMiniGallery->Id;
                    $btnChange->CssClass = 'btn btn-orange';
                    $btnChange->CausesValidation = false;
                    $btnChange->addAction(new Click(), new Ajax('btnChange_Click'));
                }
                $btnDeleteId = 'btnDelete' . $objMiniGallery->Id;
                $btnPhotoDelete = $this->getControl($btnDeleteId);

                if (!$btnPhotoDelete) {
                    $btnPhotoDelete = new Bs\Button($this->tblMiniGalleryList, $btnDeleteId);
                    $btnPhotoDelete->Text = 'Delete';
                    $btnPhotoDelete->ActionParameter = $objMiniGallery->Id;
                    $btnPhotoDelete->CausesValidation = false;
                    $btnPhotoDelete->addAction(new Click(), new Ajax('btnPhotoDelete_Click'));
                }

                $this->inputsStateControl();

                return $btnChange->render(false) . ' ' . $btnPhotoDelete->render(false);
            }
        }

        /**
         * Handles the click event for a checkbox to determine the selected state of a MiniGallery item.
         * Updates the cover media settings and session data based on the checkbox state.
         *
         * @param string $strFormId The form ID triggering the event.
         * @param string $strControlId The control ID triggering the event.
         * @param array $params Event parameters, including 'checked' state and the control's 'id'.
         *
         * @return void
         * @throws Caller
         * @throws InvalidCast
         */
        protected function chkSelected_Click(string $strFormId, string $strControlId, array $params): void
        {
            $coverMediaId = $this->getActiveCoverMediaId();

            $blnChecked = (bool)$params['checked'];
            $idParts = explode('_', $params['id']);
            $intGalleryId = (int)end($idParts);

            if ($blnChecked) {
                $countMiniGallery = MiniGallery::countByContentCoverMediaId($coverMediaId);

                if ($countMiniGallery === 1) {
                    $this->dlgModal2->showDialogBox();
                    Application::executeJavaScript("syncMiniGalleryCoverState(null);");
                    return;
                }

                $objMiniGallery = MiniGallery::load($intGalleryId);

                $this->objContentCoverMedia->setPreviewFileId($intGalleryId);
                $this->objContentCoverMedia->setPreviewFilePath($objMiniGallery->getPath());
                $this->objContentCoverMedia->setPostUpdateDate(QDateTime::now());
                $this->objContentCoverMedia->save();

                $_SESSION['coverMedia'] = [
                    'id'   => $this->objContentCoverMedia->getId(),
                    'path' => $objMiniGallery->getPath(),
                    "description" => $this->objContentCoverMedia->getDescription(),
                    "author" => $this->objContentCoverMedia->getAuthor()
                ];

                $this->btnSave->Enabled = true;

                Application::executeJavaScript("syncMiniGalleryCoverState($intGalleryId);");
            } else {
                $this->objContentCoverMedia->setPreviewFileId(null);
                $this->objContentCoverMedia->setPreviewFilePath(null);
                $this->objContentCoverMedia->setPostUpdateDate(QDateTime::now());
                $this->objContentCoverMedia->save();

                unset($_SESSION['coverMedia']);

                $this->btnSave->Enabled = false;

                Application::executeJavaScript("syncMiniGalleryCoverState(null);");

                $this->userOptions();
            }
        }

        /**
         * Handles the click action for the change button, updating the UI and loading data for the selected file.
         *
         * @param ActionParams $params The parameters associated with the button click action, including the selected file's ID.
         *
         * @return void
         * @throws Caller
         * @throws InvalidCast
         */
        protected function btnChange_Click(ActionParams $params): void
        {
            $this->intChangeFilesId = intval($params->ActionParameter);
            $objMiniGallery = MiniGallery::load($this->intChangeFilesId);

            $this->txtFileName->Text = pathinfo(APP_UPLOADS_DIR . $objMiniGallery->getPath(), PATHINFO_FILENAME);
            $this->lstIsEnabled->SelectedValue = $objMiniGallery->getStatus();
            $this->txtFileDescription->Text = $objMiniGallery->getDescription() ?? '';
            $this->txtFileAuthor->Text = $objMiniGallery->getAuthor() ?? '';
            Application::executeControlCommand($this->txtFileName->ControlId, 'focus');

            $this->userOptions();

            $this->tblMiniGalleryList->refresh();
        }

        /**
         * Handles the photo save button click action.
         * Performs validation and updates the file details in the mini gallery. Displays appropriate dialogs based on
         * the validation results or updates the file if conditions are met.
         *
         * @param ActionParams $params The parameters related to the button click action, including the action
         *     parameter.
         *
         * @return void This method does not return a value.
         * @throws Caller
         * @throws InvalidCast
         * @throws \Exception
         */
        protected function btnPhotoSave_Click(ActionParams $params): void
        {
            $objMiniGallery = MiniGallery::load($this->intChangeFilesId);

            $parts = pathinfo($this->strRootPath . $objMiniGallery->getPath());
            $files = glob($parts['dirname'] . '/*', GLOB_NOSORT);

            if (!$this->txtFileName->Text) {
                $this->dlgModal5->showDialogBox();
                $this->txtFileName->Text = $this->getFileName($objMiniGallery->getName());
            } else if (!$this->txtFileAuthor->Text && $this->txtFileDescription->Text) {
                $this->dlgModal7->showDialogBox();
                $this->txtFileDescription->Text = '';
                $this->txtFileAuthor->focus();
            } else if ($this->txtFileName->Text == $this->getFileName($objMiniGallery->getName()) &&
                ($this->txtFileAuthor->Text !== $objMiniGallery->getAuthor() ||
                    $this->txtFileDescription->Text !== $objMiniGallery->getDescription() ||
                    $this->lstIsEnabled->SelectedValue !== $objMiniGallery->getStatus())) {
                $this->updateFile($objMiniGallery);
            } else if (in_array($parts['dirname'] . '/' . trim($this->txtFileName->Text) . '.' . strtolower($parts['extension']), $files)) {
                $this->dlgModal6->showDialogBox();
                $this->txtFileName->Text = $this->getFileName($objMiniGallery->getName());
            } else {
                $this->updateFile($objMiniGallery);
            }
        }

        /**
         * Updates the file of a MiniGallery and its associated records, renaming the file, updating metadata, and refreshing the associated list.
         *
         * @param MiniGallery $intMiniGallery The MiniGallery instance representing the file to be updated, including its metadata and path.
         *
         * @return void This method does not return a value.
         * @throws Exception If any file operation or database update fails.
         */
        protected function updateFile(MiniGallery $intMiniGallery): void
        {
            $parts = pathinfo($this->strRootPath . $intMiniGallery->getPath());
            $files = glob($parts['dirname'] . '/*', GLOB_NOSORT);
            $newPath = $parts['dirname'] . '/' . trim($this->txtFileName->Text) . '.' . strtolower($parts['extension']);

            if (is_file($this->strRootPath . $intMiniGallery->getPath())) {
                if (!in_array($parts['dirname'] . '/' . trim($this->txtFileName->Text) . '.' . strtolower($parts['extension']), $files)) {
                    $this->rename($this->strRootPath . $intMiniGallery->getPath(), $newPath);

                    foreach ($this->tempFolders as $tempFolder) {
                        if (is_file($this->strTempPath . '/_files/' . $tempFolder . $intMiniGallery->getPath())) {
                            $this->rename($this->strTempPath . '/_files/' . $tempFolder . $intMiniGallery->getPath(), $this->strTempPath . '/_files/' . $tempFolder . $this->getRelativePath($newPath));
                        }
                    }
                }

                $objMiniGallery = MiniGallery::load($intMiniGallery->getId());
                $objMiniGallery->setName(basename($newPath));
                $objMiniGallery->setPath($this->getRelativePath($newPath));
                $objMiniGallery->setDescription($this->txtFileDescription->Text);
                $objMiniGallery->setAuthor($this->txtFileAuthor->Text);
                $objMiniGallery->setStatus($this->lstIsEnabled->SelectedValue);
                $objMiniGallery->setPostUpdateDate(QDateTime::now());
                $objMiniGallery->save();

                $objFile = Files::loadById($intMiniGallery->getFileId());
                $objFile->setName(basename($newPath));
                $objFile->setPath($this->getRelativePath($newPath));
                $objFile->setMtime(time());
                $objFile->save();
            }
            
            $this->objContentCoverMedia->setPostUpdateDate(QDateTime::now());
            $this->objContentCoverMedia->save();

            if (is_file($newPath)) {
                $this->dlgToastr1->notify();
            } else {
                $this->dlgToastr2->notify();
            }

            $this->intChangeFilesId = null;
            $this->tblMiniGalleryList->refresh();

            $this->userOptions();
        }

        /**
         * Handles the event triggered when the photo cancel button is clicked.
         * Resets the change file identifier and refreshes the mini gallery list.
         *
         * @param ActionParams $params The parameters associated with the action triggering this method.
         *
         * @return void Does not return a value.
         */
        protected function btnPhotoCancel_Click(ActionParams $params): void
        {
            $this->intChangeFilesId = null;
            $this->tblMiniGalleryList->refresh();

            $this->userOptions();
        }

        /**
         * Handles the photo delete button click action.
         * Validates the selected file against the active cover media and performs logic to show appropriate dialogs.
         *
         * @param ActionParams $params The parameters related to the button click action, including the action parameter.
         *
         * @return void This method does not return a value.
         * @throws Caller
         * @throws InvalidCast
         */
        protected function btnPhotoDelete_Click(ActionParams $params): void
        {
            $coverMediaId = $this->getActiveCoverMediaId();

            if (!$coverMediaId) return;

            $this->intChangeFilesId = intval($params->ActionParameter);
            $lockedImage = ContentCoverMedia::load($coverMediaId)->getPreviewFileId();

            if ($this->intChangeFilesId === $lockedImage) {
                $this->dlgModal3->showDialogBox();
                return;
            }

            $objContentCoverMedia = ContentCoverMedia::load($coverMediaId);
            $objContentCoverMedia->setPostUpdateDate(QDateTime::now());
            $objContentCoverMedia->save();

            $this->userOptions();

            $this->dlgModal4->showDialogBox();
        }

        /**
         * Handles the photo deletion process triggered by an item delete action.
         * Removes the designated file and associated temporary files, updates gallery settings, and displays notifications accordingly.
         *
         * @param ActionParams $params The parameters related to the delete action, including the action parameter used to identify the file to delete.
         *
         * @return void This method does not return a value.
         * @throws Caller
         * @throws InvalidCast
         */
        protected function photoDeleteItem_Click(ActionParams $params): void
        {
            $this->dlgModal4->hideDialogBox();

            $objMiniGallery = MiniGallery::load($this->intChangeFilesId);

            if (is_file($this->strRootPath . $objMiniGallery->getPath())) {
                unlink($this->strRootPath . $objMiniGallery->getPath());

                foreach ($this->tempFolders as $tempFolder) {
                    if (is_file($this->strTempPath . '/_files/' . $tempFolder . $objMiniGallery->getPath())) {
                        unlink($this->strTempPath . '/_files/' . $tempFolder . $objMiniGallery-> getPath());
                    }
                }

                $objFile = Files::loadById($objMiniGallery->getFileId());
                $objFile->delete();
                $objMiniGallery->delete();

                $this->userOptions();
            }

            if (is_file($this->strRootPath . $objMiniGallery->getPath())) {
                $this->dlgToastr4->notify();
            } else {
                $this->dlgToastr3->notify();
            }

            if ($this->objContentCoverMedia) {
                $this->objContentCoverMedia->setPostUpdateDate(QDateTime::now());
                $this->objContentCoverMedia->save();
            }

            $this->tblMiniGalleryList->refresh();
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
         * @throws Caller
         * @throws InvalidCast
         */
        public function btnSave_Click(ActionParams $params): void
        {
            $coverMediaId = $this->getActiveCoverMediaId();

            if (!$coverMediaId) return;

            if (empty($coverMediaId)) {
                $this->dlgModal1->showDialogBox();
                return;
            }

            if ((!$this->txtPhotoAuthor->Text && $this->txtPhotoDescription->Text) || ((!$this->txtPhotoDescription->Text) && $this->txtPhotoAuthor->Text)) {
                $this->dlgModal7->showDialogBox();
                return;
            }

            $objContentCoverMedia = ContentCoverMedia::load($coverMediaId);

            if ($this->txtPhotoDescription->Text !== $objContentCoverMedia->getDescription() ||
                $this->txtPhotoAuthor->Text !== $objContentCoverMedia->getAuthor()) {

                $objContentCoverMedia->setDescription($this->txtPhotoDescription->Text);
                $objContentCoverMedia->setAuthor($this->txtPhotoAuthor->Text);
            }

            $objContentCoverMedia->setPostUpdateDate(QDateTime::now());
            $objContentCoverMedia->save();

            $this->dlgToastr5->notify();

            $countMiniGallery = MiniGallery::countByContentCoverMediaId($coverMediaId);

            if ($countMiniGallery === 1) {
                $this->dlgModal2->showDialogBox();
                return;
            }

            if (!empty($_SESSION['coverMedia'])) {
                $params = $_SESSION['coverMedia'];
            } else {
                $params = [
                    "id" => $this->objContentCoverMedia->getId(),
                    "path" => $this->objContentCoverMedia->getPreviewFilePath(),
                    "description" => $this->txtPhotoDescription->Text ?? '',
                    "author" => $this->txtPhotoAuthor->Text ?? ''
                ];
            }

            $data = json_encode(
                $params,
                JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
                | JSON_HEX_TAG
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
                | JSON_HEX_AMP
            );

            if (!empty($_SESSION['coverMedia'])) {
                unset($_SESSION['coverMedia'] );
            } else if (!empty($_SESSION['coverMediaId'])) {
                unset($_SESSION['coverMediaId'] );
            } else if (!empty($_SESSION['folderId']) && !empty($_SESSION['folderPath'])) {
                unset($_SESSION['folderId'] );
                unset($_SESSION['folderPath']);
            }

            // Simulate the user action of selecting a file to be returned to MiniGallery.
            Application::executeJavaScript(
                "window.parent.opener.getImagesParams(" . json_encode($data) . "); window.close();"
            );

            $this->userOptions();
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
         * @throws Caller
         */
        public function btnCancel_Click(ActionParams $params): void
        {
            $coverMediaId = $this->getActiveCoverMediaId();
            $objContentCoverMedia = ContentCoverMedia::load($coverMediaId);

            $data = [
                'hasCover' => (bool)$objContentCoverMedia?->getPreviewFileId()
            ];

            if (!empty($_SESSION['coverMedia'])) unset($_SESSION['coverMedia']);
            if (!empty($_SESSION['coverMediaId'])) unset($_SESSION['coverMediaId']);
            if (!empty($_SESSION['coverMediaId'])) unset($_SESSION['coverMediaId']);
            if (!empty($_SESSION['folderPath'])) unset($_SESSION['folderPath']);

            $this->userOptions();

            Application::executeJavaScript(
                "window.parent.opener.updateMiniGalleryState(" . json_encode($data) . "); window.close();"
            );
        }

        ///////////////////////////////////////////////////////////////////////////////////////////

        /**
         * Renames a file or directory from the old name to the new name, ensuring the new name does not already exist and the old name exists.
         *
         * @param string $old The current name of the file or directory to be renamed.
         * @param string $new The desired new name for the file or directory.
         *
         * @return bool|null Returns true on success, false on failure, or null if the new name exists or the old name does not exist.
         */
        public function rename(string $old, string $new): ?bool
        {
            return (!file_exists($new) && file_exists($old)) ? rename($old, $new) : null;
        }

        /**
         * Extracts the name of a file by removing the file extension from the provided filename.
         *
         * @param string $filename The full filename from which the extension will be removed.
         *
         * @return string The name of the file without its extension.
         */
        protected function getFileName(string $filename): string
        {
            return substr($filename, 0, strrpos($filename, "."));
        }

        /**
         * Retrieves the relative path by removing the root path from the given absolute path.
         *
         * @param string $path The absolute path from which the relative path will be derived.
         *
         * @return string The relative path computed by subtracting the root path from the given absolute path.
         */
        protected function getRelativePath(string $path): string
        {
            return substr($path, strlen($this->strRootPath));
        }

    }
    MiniGalleryPageForm::run('MiniGalleryPageForm');

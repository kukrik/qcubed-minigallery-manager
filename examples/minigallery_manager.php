<?php
    require('qcubed.inc.php');

    use QCubed as Q;
    use QCubed\Bootstrap as Bs;
    use QCubed\Event\DialogButton;
    use QCubed\Project\Control\FormBase as Form;
    use QCubed\Exception\Caller;
    use QCubed\Exception\InvalidCast;
    use QCubed\Project\Control\Button;
    use QCubed\Control\Panel;
    use QCubed\Event\Click;
    use QCubed\Action\Ajax;
    use QCubed\Action\ActionParams;
    use QCubed\Plugin\Event\DeleteClick;
    use QCubed\QDateTime;

    /**
     * Class MiniGalleryForm
     *
     * This example demonstrates how to call and use the MinigalleryManager plugin.
     */

    class MiniGalleryForm extends Form
    {
        protected string $strRootPath = APP_UPLOADS_DIR;
        protected string $strTempPath = APP_UPLOADS_TEMP_DIR;
        protected string $strTempUrl = APP_UPLOADS_TEMP_URL;
        public array $tempFolders = ['thumbnail', 'medium', 'large'];

        protected Bs\Modal $dlgModal1;
        protected Bs\Modal $dlgModal2;
        protected Bs\Modal $dlgModal3;

        protected Q\Plugin\CKEditor $txtEditor;
        protected Q\Plugin\MiniGallery $objMiniGallery;
        protected ?int $objMiniGalleryId = null;


        protected Button $btnSubmit;
        protected Panel $pnlResult;
        protected Panel $pnlData;
        protected Panel $pnlIntroData;

        protected ?object $objArticle = null;
        protected ?int $intGroup = null;
        protected ?object $objContentCoverMedia = null;

        /**
         * Initializes and configures UI components such as text editor, media finder, buttons, and panels.
         * Includes the setup for handling user interactions with the components.
         *
         * @return void
         * @throws Caller
         * @throws InvalidCast
         */
        protected function formCreate(): void
        {
            // For development purposes

            $this->objArticle = Article::load(105);
            $this->intGroup = $this->objArticle->getMenuContentId(); // This is the group key: 664
            $this->objContentCoverMedia = ContentCoverMedia::load($this->objArticle->getContentCoverMediaId());

            /////////////////////////////////////////////////

            $objExample = Example::load(1);

            $this->txtEditor = new Q\Plugin\CKEditor($this);
            $this->txtEditor->Text = $objExample->getContent() ? $objExample->getContent() : null;
            $this->txtEditor->Configuration = 'ckConfig';
            $this->txtEditor->Rows = 15;

            ////////////////////////////////////////////////////////

            $this->dlgModal1 = new Bs\Modal($this);
            $this->dlgModal1->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">Are you sure you want to permanently delete this item?</p>
                                <p style="line-height: 25px; margin-bottom: -3px;">This action cannot be undone.</p>');
            $this->dlgModal1->Title = 'Warning';
            $this->dlgModal1->HeaderClasses = 'btn-danger';
            $this->dlgModal1->addButton("I accept", null, false, false, null, ['class' => 'btn btn-orange']);
            $this->dlgModal1->addCloseButton(t("I'll cancel"));
            $this->dlgModal1->addAction(new DialogButton(), new Ajax('deleteItem_Click'));

            $this->dlgModal2 = new Bs\Modal($this);
            $this->dlgModal2->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">Mini gallery has been successfully deleted!</p>');
            $this->dlgModal2->Title = t("Success");
            $this->dlgModal2->HeaderClasses = 'btn-success';
            $this->dlgModal2->addButton(t("OK"), null, false, false, null,
                ['data-dismiss'=>'modal', 'class' => 'btn btn-orange']);

            $this->dlgModal3 = new Bs\Modal($this);
            $this->dlgModal3->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">Error while deleting items!</p>');
            $this->dlgModal3->Title = t("Warning");
            $this->dlgModal3->HeaderClasses = 'btn-danger';
            $this->dlgModal3->addCloseButton(t("I understand"));

            ////////////////////////////////////////////////////////

            $this->objMiniGallery = new Q\Plugin\MiniGallery($this);
            $this->objMiniGallery->PopupUrl = dirname(QCUBED_QCUBED_MINIGALLERY_MANAGER_ASSETS_URL) . "/examples/mini_gallery_page.php?id=" . $this->objArticle->getId() . "&group=" . $this->intGroup;
            $this->objMiniGallery->EmptyImagesAlt = t("Choose a mini gallery");

            if (!empty($this->objArticle->getMediaTypeId())) {
                $this->objMiniGallery->SelectedImagesId = $this->objContentCoverMedia->getId() ?? null;
                $this->objMiniGallery->CoverImagePath = $this->objMiniGallery->TempUrl . $this->objContentCoverMedia->getPreviewFilePath();
            }

            $this->objMiniGallery->addAction(new Q\Plugin\Event\GallerySave(), new Ajax('imageSave_Push'));
            $this->objMiniGallery->addAction(new DeleteClick(0, null, '.delete-wrapper'), new Ajax('onMiniGalleryDelete'));

            $this->btnSubmit = new Button($this);
            $this->btnSubmit->Text = "Submit";
            $this->btnSubmit->PrimaryButton = true;
            $this->btnSubmit->AddAction(new Click(), new Ajax('submit_Click'));

            $this->pnlResult = new Panel($this);
            $this->pnlResult->HtmlEntities = true;

            $this->pnlData = new Panel($this);
            $this->pnlIntroData = new Panel($this);
            $this->pnlIntroData->HtmlEntities = true;
        }

        /**
         * Handles the process of saving and associating a selected image with an article.
         * It updates the media type, cover media, and post-update date for the article and
         * refreshes the mini-gallery with the selected image details.
         *
         * @param ActionParams $params Contains the parameters triggered during the operation.
         *
         * @return void
         * @throws Caller
         * @throws InvalidCast
         */
        protected function imageSave_Push(ActionParams $params): void
        {
            $saveId = $this->objMiniGallery->Items;

            $this->objArticle->setmediaTypeId(2);
            $this->objArticle->setContentCoverMediaId($saveId['id']);
            $this->objArticle->setPostUpdateDate(QDateTime::now());
            //$this->objArticle->setAssignedEditorsNameById($this->intLoggedUserId);
            $this->objArticle->save();

            if (!empty($this->objArticle->getMediaTypeId())) {
                $this->objMiniGallery->SelectedImagesId = $saveId['id'];
                $this->objMiniGallery->CoverImagePath = $this->objMiniGallery->TempUrl . $saveId['path'];
            }

            $this->objMiniGallery->refresh();
        }

        /**
         * Handles the deletion process for a mini gallery. This method assigns the mini gallery ID
         * based on the provided action parameters and displays a modal dialog box for confirmation.
         *
         * @param ActionParams $params Contains the parameters passed during the delete action, including the mini gallery ID.
         *
         * @return void
         */
        protected function onMiniGalleryDelete(ActionParams $params): void
        {
            $this->objMiniGalleryId = $params->ActionParameter;

            //Q\Project\Application::displayAlert('POST: ' . print_r($_POST, true));
            //Q\Project\Application::displayAlert('DELETED: ' . $deleteId['id']);

            $this->dlgModal1->showDialogBox();
        }

        /**
         * Handles the deletion of a content cover media item along with its associated files, folders, and mini galleries.
         * Updates the article's content cover media and media type references, as well as triggers dialog boxes based on
         * the state of remaining files.
         *
         * @param ActionParams $params The parameters passed during the delete item click event.
         *
         * @return void
         * @throws Caller
         * @throws InvalidCast
         * @throws Exception If file or directory operations fail.
         */
        protected function deleteItem_Click(ActionParams $params): void
        {
            $this->dlgModal1->hideDialogBox();

            $objContentCoverMedia = ContentCoverMedia::loadById($this->objMiniGalleryId);

            $objFolder = Folders::loadById($objContentCoverMedia->getFolderId());
            $objFileArray = Files::loadArrayByFolderId($objFolder->getId());
            $objMiniGalleryArray = MiniGallery::loadArrayByContentCoverMediaId($this->objMiniGalleryId);

            foreach ($objFileArray as $objFile) {
                $objFile = Files::loadById($objFile->getId());

                if (is_file($this->strRootPath . $objFile->getPath())) {
                    unlink($this->strRootPath . $objFile->getPath());
                }

                foreach ($this->tempFolders as $tempFolder) {
                    if (is_file($this->strTempPath . '/_files/' . $tempFolder . $objFile->getPath())) {
                        unlink($this->strTempPath . '/_files/' . $tempFolder . $objFile->getPath());
                    }
                }

                $objFile->delete();
            }

            foreach ($objMiniGalleryArray as $objMiniGallery) {
                $objMiniGallery = MiniGallery::loadById($objMiniGallery->getId());
                $objMiniGallery->delete();
            }

            if (is_dir($this->strRootPath . $objFolder->getPath())) {
                rmdir($this->strRootPath . $objFolder->getPath());

                foreach ($this->tempFolders as $tempFolder) {
                    if (is_dir($this->strTempPath . '/_files/' . $tempFolder . $objFolder->getPath())) {
                        rmdir($this->strTempPath . '/_files/' . $tempFolder . $objFolder->getPath());
                    }
                }
            }

            $objFolder->delete();

            $this->objArticle->setContentCoverMediaId(null);
            $this->objArticle->setMediaTypeId(null);
            $this->objArticle->setPostUpdateDate(QDateTime::now());
            //$this->objArticle->setAssignedEditorsNameById($this->intLoggedUserId);
            $this->objArticle->save();

            //$this->userOptions();

            $objContentCoverMedia->delete();

            $this->objMiniGallery->SelectedImagesId = null;
            $this->objMiniGallery->CoverImagePath = null;

            $this->objMiniGallery->refresh();

            if (!Files::countByFolderId($objContentCoverMedia->getFolderId())) {
                $this->dlgModal2->showDialogBox();
            } else {
                $this->dlgModal3->showDialogBox();
            }
        }

        /**
         * Handles the click event for the submit action. This method updates the content
         * of an example object, saves the changes, and updates various panels with information
         * or placeholders based on available data, including an associated image.
         *
         * @param ActionParams $params Contains the parameters passed during the click event.
         *
         * @return void
         * @throws Caller
         * @throws InvalidCast
         */
        protected function submit_Click(ActionParams $params): void
        {
//            $objExample = Example::loadById(1);
//            $objExample->setContent($this->txtEditor->Text);
//            $objExample->save();
//
//            $this->pnlResult->Text = $objExample->getContent();
//
//            if ($objExample->getPictureId()) {
//                $this->pnlData->Text = $objExample->getPictureId();
//            } else {
//                $this->pnlData->Text = "NULL";
//            }
//
//            $video = $objExample->getVideoEmbed();
//
//            if ($video) {
//                $this->pnlIntroData->Text = $objExample->getVideoEmbed();
//            } else {
//                $this->pnlIntroData->Text = "NULL";
//            }
        }

        // Special attention must be given here when you wish to delete the selected example. It is necessary
        // to inform FileHandler to first decrease the count of locked files ("locked_file").
        // Finally, delete this example.

        // The approximate example below:

        /*protected function delete_Click(ActionParams $params)
        {
            $objExample = Example::loadById(1);

            $lockedFile = Files::loadById($objExample->getPictureId());
            $lockedFile->setLockedFile($lockedFile->getLockedFile() - 1);
            $lockedFile->save();

            $objExample->delete();
        }*/

    }
    MiniGalleryForm::run('MiniGalleryForm');

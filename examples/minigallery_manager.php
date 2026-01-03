<?php
    require('qcubed.inc.php');

    use QCubed as Q;
    use QCubed\Project\Control\FormBase as Form;
    use QCubed\Exception\Caller;
    use QCubed\Exception\InvalidCast;
    use QCubed\Project\Control\Button;
    use QCubed\Control\Panel;
    use QCubed\Event\Click;
    use QCubed\Action\Ajax;
    use QCubed\Action\ActionParams;
    use QCubed\Plugin\Event\DeleteClick;

    /**
     * Class SampleForm5
     *
     * This example demonstrates how to call and use VideoEmbed.
     */

    class SampleForm6 extends Form
    {
        protected Q\Plugin\CKEditor $txtEditor;
        protected Q\Plugin\MediaFinder $objMediaFinder;
        protected Q\Plugin\VideoEmbed $objVideoEmbed;
        protected Button $btnSubmit;
        protected Panel $pnlResult;
        protected Panel $pnlData;
        protected Panel $pnlIntroData;

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
            $objExample = Example::load(1);

            $this->txtEditor = new Q\Plugin\CKEditor($this);
            $this->txtEditor->Text = $objExample->getContent() ? $objExample->getContent() : null;
            $this->txtEditor->Configuration = 'ckConfig';
            $this->txtEditor->Rows = 15;

            $this->objVideoEmbed = new Q\Plugin\VideoEmbed($this);
            $this->objVideoEmbed->PopupUrl = dirname(QCUBED_VIDEOMANAGER_ASSETS_URL) . "/examples/video_page.php" . "?id=1";
            $this->objVideoEmbed->EmptyVideoAlt = t("Choose a video");
            $this->objVideoEmbed->SelectedVideoAlt = t("Selected video");

            if ($objExample->getVideoEmbed()) {
                $this->objVideoEmbed->SelectedVideoId = 1;
                $this->objVideoEmbed->SelectedVideoEmbed = $objExample->getVideoEmbed() ?? null;
            }

            if ($objExample->getVideoEmbed()) {
                $this->objVideoEmbed->SelectedVideoId = 1;
                $this->objVideoEmbed->SelectedVideoEmbed = $objExample->getVideoEmbed() ?? null;
            }

            $this->objVideoEmbed->addAction(new Q\Plugin\Event\VideoSave(), new Ajax('videoSave_Push'));
            $this->objVideoEmbed->addAction(new DeleteClick(0, null, '.delete-wrapper'), new Ajax('onVideoDelete'));

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
         * Handles the saving of a selected image by updating the relevant database entry
         * with the associated image ID. Updates the UI components to reflect the selected image's information.
         *
         * @param ActionParams $params Parameters from the action event, used for processing the image save request.
         *
         * @return void
         * @throws Caller
         * @throws InvalidCast
         */
        protected function videoSave_Push(ActionParams $params): void
        {
            $embed = $this->objVideoEmbed->Items;

            //Q\Project\Application::displayAlert('POST: ' . print_r($_POST, true));
            //Q\Project\Application::displayAlert('VIDEOSAVE: ' . $embed['id'] . ' => ' . $embed['embed']);

            $objExample = Example::load($embed['id']);
            $objExample->setPictureId($embed['id']);
            $objExample->setVideoEmbed($embed['embed']);
            $objExample->save();

            $this->objVideoEmbed->SelectedVideoId = $objExample->getPictureId();
            $this->objVideoEmbed->SelectedVideoEmbed = $objExample->getVideoEmbed() ?? null;

            $this->objVideoEmbed->refresh();
        }

        /**
         * Handles the deletion of an image and updates the relevant database records.
         * Ensures the associated file is marked as free and updates the corresponding
         * entry in the "example" table to indicate no image is associated. Additionally,
         * updates UI components to reflect the changes.
         *
         * @param ActionParams $params Provides parameters related to the user-triggered action.
         *
         * @return void
         * @throws Caller
         * @throws InvalidCast
         */
        protected function onVideoDelete(ActionParams $params): void
        {
            $videoId = $params->ActionParameter;

            //Q\Project\Application::displayAlert('POST: ' . print_r($_POST, true));
            //Q\Project\Application::displayAlert('DELETEID: ' . $videoId);

            $objExample = Example::load($videoId);

            $objExample->setVideoEmbed(null);
            $objExample->setPictureId(null); // This is a temporary column to store the video ID. For testing purposes.
            $objExample->save();

            $this->objVideoEmbed->SelectedVideoId = null;
            $this->objVideoEmbed->SelectedVideoEmbed = null;

            $this->objVideoEmbed->refresh();
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
            $objExample = Example::loadById(1);
            $objExample->setContent($this->txtEditor->Text);
            $objExample->save();

            $this->pnlResult->Text = $objExample->getContent();

            if ($objExample->getPictureId()) {
                $this->pnlData->Text = $objExample->getPictureId();
            } else {
                $this->pnlData->Text = "NULL";
            }

            $video = $objExample->getVideoEmbed();

            if ($video) {
                $this->pnlIntroData->Text = $objExample->getVideoEmbed();
            } else {
                $this->pnlIntroData->Text = "NULL";
            }
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
    SampleForm6::run('SampleForm6');

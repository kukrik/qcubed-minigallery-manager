<?php $strPageTitle = 'Mini gallery management' ; ?>
<?php require('header.inc.php'); ?>
<?php $this->RenderBegin(); ?>
    <style>
        body, html {margin: 0; padding: 0; background-color: #ebe7e2 !important; font-family: 'Open Sans', sans-serif; font-size: 14px !important; color: #000;}
        .page-container {margin: 20px; padding: 15px; min-height: 94vh; background-color: #ffffff;border-radius: 4px;}
        .vauu-title-3 {margin: 0 0 20px; padding: 10px 0 20px; display: block; font-size: 18px; color: #000; font-weight: 600 !important; letter-spacing: -1px; border-bottom: 1px solid #ccc;}
        .form-actions-wrapper  {display: block; background-color: #f5f5f5; border-radius: 4px; margin: 10px 0; padding: 15px; }
        .preview img {height: 70px; width: 70px; object-fit: cover; object-position: 100% 0;}
        .table-body-alert {margin: 0; padding: 15px 0 0 0;}
        .table-wrapper {margin-top: 30px;}
        .upload-button-wrapper {margin-bottom: 10px;}
        .fileupload-buttonbar {margin: 0; padding: 15px; background-color: #dedede;}
        .gallery-upload-wrapper {margin-top: 25px; height: auto;}
    </style>

    <div class="page-container">
        <div class="form-horizontal">
            <div class="js-gallery-edit-form">
                <div class="row">
                    <div class="col-md-12">
                        <div class="title-heading">
                            <h3 class="vauu-title-3"><?php _t('Mini gallery edit') ?></h3>
                        </div>
                        <?= _r($this->lblRegisterError); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 upload-button-wrapper">
                        <?= _r($this->btnGoUpload); ?>
                    </div>
                </div>
                <div class="table-body-alert">
                    <?= _r($this->lblNoImages); ?>
                </div>
                <div class="form-group">
                    <?= _r($this->lblPhotoDescription); ?>
                    <div class="col-md-5">
                        <?= _r($this->txtPhotoDescription); ?>
                    </div>
                </div>
                <div class="form-group">
                    <?= _r($this->lblPhotoAuthor); ?>
                    <div class="col-md-5">
                        <?= _r($this->txtPhotoAuthor); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8" style="text-align: right;">
                        <?= _r($this->btnSaveMiniGallery); ?>
                        <?= _r($this->btnCancelMiniGallery); ?>
                    </div>
                </div>
                <div class="table-wrapper js-gallery-table">
                    <?= _r($this->tblMiniGalleryList); ?>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-actions-wrapper" style="text-align: right;">
                            <?= _r($this->btnSave); ?>
                            <?= _r($this->btnCancel); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="js-gallery-upload-form hidden">
                <div class="row">
                    <div class="col-md-12">
                        <div class="title-heading">
                            <h3 class="vauu-title-3"><?php _t('Mini gallery upload') ?></h3>
                        </div>
                    </div>
                </div>
                <div class="fileupload-buttonbar">
                    <div class="row">
                        <div class="col-md-12">
                            <?= _r($this->btnAddFiles); ?>
                            <?= _r($this->btnAllStart); ?>
                            <?= _r($this->btnAllCancel); ?>
                            <?= _r($this->btnBack); ?>
                        </div>
                    </div>
                </div>
                <div class="gallery-upload-wrapper">
                    <div id="alert-wrapper"></div>
                    <div class="alert-multi-wrapper"></div>
                    <?= _r($this->objUpload); ?>
                    <div class="fileupload-donebar hidden">
                        <?= _r($this->btnDone); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $this->RenderEnd(); ?>
<?php require('footer.inc.php');
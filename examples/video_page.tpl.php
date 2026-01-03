<?php $strPageTitle = 'Video management' ; ?>
<?php require('header.inc.php'); ?>
<?php $this->RenderBegin(); ?>
    <style>
        body, html {background-color: #ebe7e2 !important; font-family: 'Open Sans', sans-serif; font-size: 14px !important; color: #000;}
        .page-container {margin: 20px; padding: 15px; height: 94vh; background-color: #ffffff;border-radius: 4px;}
        .vauu-title-3 {margin: 0 0 30px; padding: 10px 0 20px; display: block; font-size: 18px; color: #000; font-weight: 600 !important; letter-spacing: -1px; border-bottom: 1px solid #ccc;}
        .form-actions-wrapper  {display: block; background-color: #f5f5f5; border-radius: 4px; margin: 0 -15px; padding: 15px; text-align: left;}
    </style>
    <div class="page-container">
        <div class="form-horizontal">
            <div class="row">
                <div class="col-md-12">
                    <div class="title-heading">
                        <h3 class="vauu-title-3"><?php _t('Video edit') ?></h3>
                    </div>
                    <div class="form-group">
                        <?= _r($this->lblTitle); ?>
                        <div class="col-md-7">
                            <?= _r($this->txtTitle); ?>
                        </div>
                    </div>
                    <div class="form-group js-embed-code">
                        <?= _r($this->lblEmbedCode); ?>
                        <div class="col-md-7">
                            <?= _r($this->txtEmbedCode); ?>
                            <?= _r($this->btnEmbed); ?>
                        </div>
                    </div>
                    <div class="form-group hidden js-video">
                        <?= _r($this->lblVideo); ?>
                        <div class="col-md-7">
                            <div class="embed-responsive embed-responsive-16by9">
                                <?= _r($this->strVideo); ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <?= _r($this->lblDescription); ?>
                        <div class="col-md-7">
                            <?= _r($this->txtDescription); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?= _r($this->lblAuthor); ?>
                        <div class="col-md-7">
                            <?= _r($this->txtAuthor); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-actions-wrapper" style="text-align: right;">
                    <?= _r($this->btnSave); ?>
                    <?= _r($this->btnReplace); ?>
                    <?= _r($this->btnCancel); ?>
                </div>
            </div>
        </div>
    </div>

<?php $this->RenderEnd(); ?>
<?php require('footer.inc.php');
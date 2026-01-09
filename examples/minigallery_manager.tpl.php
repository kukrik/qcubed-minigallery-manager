<?php require(QCUBED_CONFIG_DIR . '/header.inc.php'); ?>

<?php
// https://ckeditor.com/docs/ckeditor4/latest/guide/dev_file_browse_upload.html
?>
<style>
    code {
        display: inline-block;
        width: 11%;
    }
</style>
<script>
    ckConfig = {
        skin: 'moono',
    };
</script>
<?php $this->RenderBegin(); ?>

<div class="instructions" style="margin-bottom: 40px;">
    <h3>MiniGallery for QCubed-4: Implementing the MiniGallery Plugin</h3>

</div>


<div class="container" style="margin-top: 20px; margin-bottom: 20px;">
    <div class="row">
        <div class="col-md-9"><?= _r($this->txtEditor); ?></div>
        <div class="col-md-3"><?= _r($this->objMiniGallery); ?></div>
    </div>
<!--    <div class="row">-->
<!--        <div class="col-md-12" style="margin-top: 15px;">--><?php //= _r($this->btnSubmit); ?><!--</div>-->
<!--    </div>-->
<!--    <div class="row" style="margin-top: 20px;">-->
<!--        <div class="col-md-4">-->
<!--            <h5><b>The HTML you typed:</b></h5>-->
<!--            --><?php //= _r($this->pnlResult); ?>
<!--        </div>-->
<!--        <div class="col-md-4">-->
<!--            <h5><b>DATA to store in a separate column of the database table:</b></h5>-->
<!--            --><?php //= _r($this->pnlData); ?>
<!--        </div>-->
<!--        <div class="col-md-4">-->
<!--            <h5><b>The introduction videoembed is placed, for example, on top of the content on the frontend:</b></h5>-->
<!--            --><?php //= _r($this->pnlIntroData); ?>
<!--        </div>-->
<!--    </div>-->
</div>

<?php $this->RenderEnd(); ?>
<?php require(QCUBED_CONFIG_DIR . '/footer.inc.php'); ?>

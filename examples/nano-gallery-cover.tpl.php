<?php require(QCUBED_CONFIG_DIR . '/header.inc.php'); ?>

<?php $this->RenderBegin(); ?>

<div class="instructions" style="margin-bottom: 40px;">
    <h3>NanoGalleryCover for QCubed-4: Implementing the NanoGalleryCover Plugin</h3>

</div>


<div class="container" style="margin-top: 20px; margin-bottom: 20px;">
    <div class="row">
        <div class="col-md-6"><?= _r($this->objCover); ?></div>
    </div>
</div>

<?php $this->RenderEnd(); ?>
<?php require(QCUBED_CONFIG_DIR . '/footer.inc.php'); ?>

<div class="PDFLogo">
    <?php
    /**
     * Before company logo in quote PDF.
     *
     * @param  array  $pdfSetting  Quoteup settings.
     */
    do_action('quoteup_before_pdf_logo', $pdfSetting);
    if (isset($pdfSetting[ 'company_logo' ]) && $pdfSetting[ 'company_logo' ] != '') {
        ?>
        <img class="quote-logo"  src='<?php echo $pdfSetting[ 'company_logo' ];
        ?>' height="150px" width="150px">
        <?php
    }
    /**
     * After company logo in quote PDF.
     *
     * @param  array  $pdfSetting  Quoteup settings.
     */
    do_action('quoteup_after_pdf_logo', $pdfSetting);
    ?>
</div>

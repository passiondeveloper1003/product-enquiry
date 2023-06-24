<?php

/**
 * This file deals with deleting PDF created before more than 1 hour using cron job.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

add_action('quoteupDeletePdfs', 'quoteupDeletePdfsCallback');

/**
* Function that performs actual deleting process.
* The function finds out the files which are older than an hour in the uploads directory.
* It then unlinks the files from the uploads.
* Also it changes the enquiry_details_new table and the pdf_deleted to 1.
* @global $wpdb
*/
function quoteupDeletePdfsCallback()
{
    global $wpdb;
    //find out pdfs older than an hour and delete them
    $uploadDir = wp_upload_dir();
    $pdfDir = $uploadDir[ 'basedir' ].'/QuoteUp_PDF/';

    if (!file_exists($pdfDir)) {
        return;
    }

    $all_files = glob($pdfDir.'*');
    if ($all_files) {
        /* * * cycle through all files in the directory * * */
        foreach ($all_files as $file) {
            /* * * if file is older than an hour, delete it * * */
            if (filemtime($file) < time() - 3600 && 'mpdf' != basename($file)) {
                //Update Enquiry table for deleted file enquiry ID
                $enquiry_id = basename($file, '.pdf');
                $table_name = getEnquiryDetailsTable();
                $wpdb->update(
                    $table_name,
                    array(
                        'pdf_deleted' => 1,
                    ),
                    array(
                        'enquiry_id' => $enquiry_id,
                    )
                );
                //END
                unlink($file);
            }
        }
    }
}

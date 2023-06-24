<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function quoteupShowSideBarFeedback($form_data)
{
    ?>
    <div id='quoteup_sidebar_feedback_wrapper'>
    <?php
        require_once 'feedback/feedback.php';
        do_action('quoteup_feedback', $form_data);
    ?>
    </div>
    <?php
}

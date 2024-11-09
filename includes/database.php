<?php

// Hook into quiz completion
add_action('learndash_quiz_completed', 'save_quiz_result_to_database', 10, 2);

function save_quiz_result_to_database(){
    // Ensure MongoDB PHP extension is available
    if (!class_exists('MongoDB\Client')) {
        error_log('MongoDB PHP library is not available.');
        return;
    }
}
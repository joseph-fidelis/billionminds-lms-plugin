<?php
add_action('learndash_quiz_submitted', 'generate_result_journal', 12, 2);
add_action('learndash_init', 'billion_minds_learndash',99);
add_action('learndash_quiz_completed', 'generate_result_journal', 12, 2);

function generate_result_journal($quiz_data, $user_id) {
    // // Extracting Quiz Information
    // $quiz_id = $quiz_data['quiz']->ID;
    // $quiz_title = get_the_title($quiz_id);
    // $quiz_score = $quiz_data['score'];
    // $quiz_percentage = $quiz_data['percentage'];
    // $quiz_passed = $quiz_data['pass'] ? 'Passed' : 'Failed';

    // // Extracting User Information
    // $user_info = get_userdata($user_id);
    // $user_name = $user_info->display_name;
    // $user_email = $user_info->user_email;

    // // Extracting Lesson Information
    // $lesson_id = learndash_get_lesson_id($quiz_id);
    // $lesson_title = get_the_title($lesson_id);

    // // Log basic quiz and user data
    // $log_message = sprintf(
    //     'User %s (Email: %s) completed quiz "%s" (ID: %d) under lesson "%s" (ID: %d). Score: %d, Percentage: %d%%, Result: %s.',
    //     $user_name,
    //     $user_email,
    //     $quiz_title,
    //     $quiz_id,
    //     $lesson_title,
    //     $lesson_id,
    //     $quiz_score,
    //     $quiz_percentage,
    //     $quiz_passed
    // );
    
    // Log to Wordpress error log
    do_action( 'qm/debug', "quiz taken" );
    
}

function billion_minds_learndash(){
    do_action( 'qm/debug', "init hook" );
    error_log("Learndash init hook ");
}
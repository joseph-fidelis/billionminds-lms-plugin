<?php
add_action('gform_after_submission', 'send_form_data_to_api', 10, 2);

function send_form_data_to_api($entry, $form) {
    do_action( 'qm/debug', "Send Form Data Entry" );
    
    // ? List of acceptable form IDs.
    $form_ids = 
    [
        81, 82, 83,84,85,86,94,95,96,97,103,104,105,109,110,111

    ];

    // Check if the form exist in list of acceptable forms 
    if (!in_array($form['id'], $form_ids)) {
        return;
    }

    // ? Get the current user (subscriber) ID
    $user_id = get_current_user_id();
    $course_id = learndash_get_course_id(); 
    $entry_id = $entry['id'];
    $course_name = $course_id ? get_the_title($course_id) : '';

    if (is_singular('sfwd-lessons')) {
        $lesson_id   = get_the_ID();
        $lesson_name = get_the_title();
    } else {
        $lesson_id   = '';
        $lesson_name = '';
    }
    
    $result = [];

 foreach ($form['fields'] as $field) {
    $field_id = $field->id; 
    $field_label = $field->label; 
    $field_value = rgar($entry, $field_id);

    if (!empty($field_value)) {
        $result[] = [
            "question" => $field_label,
            "answer" => $field_value
        ];
    }
}

// Data to send to the webhook
$webhook_data = [
        "user_id" => $user_id,
        "course_id"   => $course_id,
        "course_name" => $course_name,
        "lesson_id"   => $lesson_id,
        "lesson_name" => $lesson_name,
        "form_id"     => $form['id'],
        "entry_id"    => $entry_id,
        "exercises"     => $result,
];

// Webhook URL
$webhook_url = "https://hook.us1.make.com/vefr1uybqp9bkhlhdk0qpqa5up34mxa1";

// Send the data to the webhook
$response = wp_remote_post($webhook_url, [
    'method'    => 'POST',
    'headers'   => [
        'Content-Type' => 'application/json',
    ],
    'body'      => json_encode($webhook_data),
]);

// Debug the webhook response
if (is_wp_error($response)) {
    do_action('qm/debug', "Webhook Error -> " . $response->get_error_message());
} else {
    do_action('qm/debug', "Webhook Response -> " . wp_remote_retrieve_body($response));
}

}
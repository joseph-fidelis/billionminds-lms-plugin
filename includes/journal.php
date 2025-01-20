<?php
add_action('gform_after_submission', 'send_form_data_to_api', 10, 2);

function send_form_data_to_api($entry, $form) {
    do_action( 'qm/debug', "Send Form Data Entry" );
    // List of form IDs
    $form_ids = [2, 1];

    // Check if the form exist in list of acceptable forms 
    if (!in_array($form['id'], $form_ids)) {
        return;
    }

    // Get the current user (subscriber) ID
    $user_id = get_current_user_id();

    // Get the course ID (assuming it's embedded in a LearnDash course)
    $course_id = learndash_get_course_id(); // Ensure you are inside a LearnDash course page
    $entry_id = $entry['id'];
    $labels = $entry["_labels"];

    $result = [];

 // Loop through fields to map labels to their corresponding answers
 foreach ($form['fields'] as $field) {
    $field_id = $field->id; // Field ID
    $field_label = $field->label; // Field label (question)
    $field_value = rgar($entry, $field_id); // Get the field's value from the entry

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
    "course_id" => $course_id,
    "form_id" => $form['id'],
    "entry_id" => $entry_id,
    "q_and_a" => $result,
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
    
    do_action( 'qm/debug', "User ID -> " . $user_id );
    do_action( 'qm/debug', "Course ID -> " . $course_id );
    do_action( 'qm/debug', "Form ID -> " . $form['id'] );
    do_action( 'qm/debug', "Entry ID -> " . $entry_id );
    do_action( 'qm/debug', "Q&A -> " . json_encode($result) );

}
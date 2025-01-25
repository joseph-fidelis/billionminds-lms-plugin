<?php
/**
 * Accreditation: Send completed lessons to webhook
 * This hook sends the completed lesson data to a webhook URL.
 * For Accreditation purposes.
 */


// Define the ActiveCampaign API URL and API key
define('ACTIVECAMPAIGN_API_URL', 'https://billionminds.api-us1.com/api/3/');
define('ACTIVECAMPAIGN_API_KEY', '6001a4b640e44ba003c278599777904ee8e790434b41f3a0867f97a941abcfc0bf0b5c0a');
define('ACCREDIBLE_API_URL', 'https://api.accredible.com/v1/credentials');
define('ACCREDIBLE_API_KEY', 'Token token=28b4ec39731789e990b49db41640398c');

/////////////////////// REST API /////////////////////////


/**
 * REST API to check if user completed a lesson in a course.
 * 
 */

 add_action('rest_api_init', function () {
    register_rest_route('billionminds/v1', '/lesson-status', array(
        'methods'  => 'GET',
        'callback' => 'check_lesson_not_complete',
        'permission_callback' => '__return_true', // For testing; secure as needed
    ));
});

/**
 * REST API to return all the users that completes a lesson in a course.
 * 
 */

 add_action('rest_api_init', function () {
    register_rest_route('billionminds/v1', '/bulk-lesson-status', array(
        'methods'  => 'GET',
        'callback' => 'check_bulk_users_lesson_complete',
        'permission_callback' => '__return_true', // For testing; secure as needed
    ));
});

/**
 * REST API to return all users who completed a lesson in a course but has no accredible Credential.
 * 
 */

 add_action('rest_api_init', function () {
    register_rest_route('billionminds/v1', '/bulk-accredible-lesson-status', array(
        'methods'  => 'GET',
        'callback' => 'check_accredible_bulk_users_lesson_complete',
        'permission_callback' => '__return_true', // For testing; secure as needed
    ));
});

/**
 * REST API to check if user completed quiz.
 * 
 */

 add_action('rest_api_init', function () {
    register_rest_route('billionminds/v1', '/quiz-status', array(
        'methods'  => 'GET',
        'callback' => 'check_quiz_not_complete',
        'permission_callback' => '__return_true', // For testing; secure as needed
    ));
});

/**
 * REST API to return all the users that completed a quiz.
 * 
 */

 add_action('rest_api_init', function () {
    register_rest_route('billionminds/v1', '/bulk-quiz-status', array(
        'methods'  => 'GET',
        'callback' => 'check_bulk_users_quiz_complete',
        'permission_callback' => '__return_true', // For testing; secure as needed
    ));
});

/**
 * REST API to return all users who completed a quiz in a course but has no accredible Credential.
 * 
 */

 add_action('rest_api_init', function () {
    register_rest_route('billionminds/v1', '/bulk-accredible-quiz-status', array(
        'methods'  => 'GET',
        'callback' => 'check_bulk_accredible_users_quiz_complete',
        'permission_callback' => '__return_true', // For testing; secure as needed
    ));
});


/**
 * REST API to return all WP user.
 * 
 */
add_action('rest_api_init', function () {
    register_rest_route('billionminds/v1', '/users', array(
        'methods'  => 'GET',
        'callback' => 'get_all_users',
        'permission_callback' => '__return_true', // For testing; secure as needed
    ));
});


/**
 * REST API to return all WP user.
 * Paginated
 */
add_action('rest_api_init', function () {
    register_rest_route('billionminds/v1', '/users-course', array(
        'methods'  => 'GET',
        'callback' => 'get_all_course_users',
        'permission_callback' => '__return_true', // For testing; secure as needed
    ));
});




 /**
 * Sends a webhook when a lesson is completed.
 * @param array $data The lesson completion data.
 *
 */

 add_action(
    'learndash_lesson_completed',
    function( $data ) {
        do_action( 'qm/debug', "Accreditation Fired -> ");
    // Define the webhook URL
    $webhook_url = 'https://hook.us1.make.com/gy2jnhx5m1wte8khe3dmi2ypk1sie8v3'; 

    $user_id = get_current_user_id();

    // Prepare the data payload
    $payload = [
        'user_id'     => $user_id,
        'lesson_id'   => $data['lesson']->ID,
        'lesson_title'=> get_the_title( $data['lesson']->ID ),
        'course_id'   => $data['course']->ID,
        'course_title'=> get_the_title( $data['course']->ID ),
        'completion_time' => current_time( 'mysql' ),
    ];

    // Send the data to the webhook
    wp_remote_post( $webhook_url, [
        'method'      => 'POST',
        'body'        => json_encode( $payload ),
        'headers'     => array(
            'Content-Type' => 'application/json',
        ),
    ]);

    do_action( 'qm/debug', "Accreditation sent -> " . json_encode( $payload ) );
 
    }
);




//////////////// Functions //////////////////////




/**
 * Check if a lesson is not complete.
 * @param WP_REST_Request $request The REST request.
 * @return array The response data.
 * @returns user who have completed the lesson 
 */

function check_lesson_not_complete($data) {
    $user_id = $data['user_id'];
    $lesson_id = $data['lesson_id'];
    $course_id = $data['course_id'];

    if (!$user_id || !$lesson_id) {
        return new WP_Error('missing_parameters', 'User ID and Lesson ID are required.', array('status' => 400));
    }

    $is_lesson_complete = learndash_is_lesson_complete($user_id, $lesson_id, $course_id);

    return array(
        'user_id'    => $user_id,
        'lesson_id'  => $lesson_id,
        'is_lesson_completed' => $is_lesson_complete,
    );
}



/**
 * Check if a lesson is not complete.
 * @param WP_REST_Request $request The REST request.
 * @return array The response data .
 * returns list of users who have completed the lesson 
 */

 function check_bulk_users_lesson_complete($data) {
    $lesson_id = $data['lesson_id'];
    $course_id = $data['course_id'];

    if (!$course_id || !$lesson_id) {
        return new WP_Error('missing_parameters', 'Course ID and Lesson ID are required.', array('status' => 400));
    }

    $users = get_all_course_users($course_id);
    $completed_users = array();

    foreach ($users as $user) {
        $user_id = $user->ID;
        $is_lesson_complete = learndash_is_lesson_complete($user_id, $lesson_id, $course_id);

        if ($is_lesson_complete) {
            $completed_users[] = array(
                'user_id'    => $user_id,
                'email'      => $user->user_email,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'lesson_id'  => $lesson_id,
                'is_lesson_completed' => $is_lesson_complete,
            );
        }
    }

    return $completed_users;

}


/**
 * Check for users who completed a lesson but was not assigned a credential.
 * @param WP_REST_Request $request The REST request.
 * @return array The response data .
 * returns list of users who completed the lesson but has no credential
 */

 function check_accredible_bulk_users_lesson_complete($data) {
    $lesson_id = $data['lesson_id'];
    $course_id = $data['course_id'];
    $group_id = $data['group_id'];
    $tag_id = $data['tag_id'];

    if (!$course_id || !$lesson_id) {
        return new WP_Error('missing_parameters', 'Course ID and Lesson ID are required.', array('status' => 400));
    }
    if (!$group_id) {
        return new WP_Error('missing_parameters', 'Group ID required.', array('status' => 400));
    }
    if (!$tag_id) {
        return new WP_Error('missing_parameters', 'AC Tag ID required.', array('status' => 400));
    }

    $users = get_all_course_users($course_id);
    $completed_users = array();
    $users_with_empty_credentials = array();

    // STEP 1: Get all the users that has completed the lesson.
    foreach ($users as $user) {
        $user_id = $user->ID;
        $is_lesson_complete = learndash_is_lesson_complete($user_id, $lesson_id, $course_id);

        if ($is_lesson_complete) {
            $completed_users[] = array(
                'user_id'    => $user_id,
                'email'      => $user->user_email,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'lesson_id'  => $lesson_id,
                'is_lesson_completed' => $is_lesson_complete,
            );
        }
    }

    // STEP 2: Check all the users that has completed the lesson but have no certificate.
    foreach ($completed_users as $user) {
        $email = $user['email'];
        $credentials = is_accredible_credentials_exist($email, $group_id);

        if(empty($credentials)) {
            $users_with_empty_credentials[] = array(
                'user_id'    => $user['user_id'],
                'email'      => $user['email'],
                'first_name' => $user['first_name'],
                'last_name'  => $user['last_name'],
                'lesson_id'  => $user['lesson_id'],
                'course_id'  => $course_id,
                'group_id'   => $group_id,
            );
        }
    }

    // STEP 3: Assign Certificate. 
    if(empty($users_with_empty_credentials)){
        return array(
            "code" => 200, 
            "message" => "no users to assign credentials." 
        );
    }
    
    foreach($users_with_empty_credentials as $user){
        $email = $user['email'];
        $group_id = $user['group_id'];
        $name = $user['first_name'] . ' ' . $user['last_name'];
        $response = create_accredible_credentials($email, $group_id, $name);
    }

    // STEP 4: Add tag to AC
    foreach($users_with_empty_credentials as $user){
        $email = $user['email'];
        $tag_id = $tag_id;
        $response = add_activecampaign_tag($email, $tag_id);
    }

    return $users_with_empty_credentials;
}



/**
 * Check if a quiz is not complete.
 * @param WP_REST_Request $request The REST request.
 * @return array The response data.
 */

function check_quiz_not_complete($data) {
    $user_id = $data['user_id'];
    $quiz_id = $data['quiz_id'];
    $course_id = $data['course_id'];

    if (!$user_id || !$quiz_id) {
        return new WP_Error('missing_parameters', 'User ID and Quiz ID are required.', array('status' => 400));
    }

    $is_quiz_complete = learndash_is_quiz_complete($user_id, $quiz_id, $course_id);

    return array(
        'user_id'    => $user_id,
        'quiz_id'    => $quiz_id,
        'is_quiz_completed' => $is_quiz_complete,
    );
}


/**
 * Check if a quiz is complete.
 * @param WP_REST_Request $request The REST request.
 * @return array The response data.
 * Returns list of users who have completed the quiz in a course.
 */

 function check_bulk_users_quiz_complete($data) {
    $quiz_id = $data['quiz_id'];
    $course_id = $data['course_id'];

    if (!$course_id || !$quiz_id) {
        return new WP_Error('missing_parameters', 'Course ID and Quiz ID are required.', array('status' => 400));
    }

    $users = get_all_course_users($course_id);
    $completed_users = array();

    foreach ($users as $user) {
        $user_id = $user->ID;
        $is_quiz_complete = learndash_is_quiz_complete($user_id, $quiz_id, $course_id);

        if ($is_quiz_complete) {
            $completed_users[] = array(
                'user_id'    => $user_id,
                'email'      => $user->user_email,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'quiz_id'    => $quiz_id,
                'is_quiz_completed' => $is_quiz_complete,
            );
        }
    }

    return $completed_users;
}

/**
 * Check for users who completed a quize but was not assigned a credential.
 * @param WP_REST_Request $request The REST request.
 * @return array The response data.
 * Returns list of users who have completed the quiz in a course but has no credential.
 */

 function check_bulk_accredible_users_quiz_complete($data) {
    $quiz_id = $data['quiz_id'];
    $course_id = $data['course_id'];
    $group_id = $data['group_id'];
    $tag_id = $data['tag_id'];


    if (!$course_id || !$quiz_id) {
        return new WP_Error('missing_parameters', 'Course ID and Quiz ID are required.', array('status' => 400));
    }

    if (!$group_id) {
        return new WP_Error('missing_parameters', 'Group ID required.', array('status' => 400));
    }

    if (!$tag_id) {
        return new WP_Error('missing_parameters', 'AC Tag ID required.', array('status' => 400));
    }

    $users = get_all_course_users($course_id);
    $completed_users = array();
    $users_with_empty_credentials = array();

    // STEP 1: Get all the users that has completed the quiz.
    foreach ($users as $user) {
        $user_id = $user->ID;
        $is_quiz_complete = learndash_is_quiz_complete($user_id, $quiz_id, $course_id);

        if ($is_quiz_complete) {
            $completed_users[] = array(
                'user_id'    => $user_id,
                'email'      => $user->user_email,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'quiz_id'    => $quiz_id,
                'is_quiz_completed' => $is_quiz_complete,
            );
        }
    }

    // STEP 2: Check all the users that has completed the quiz but have no certificate.
    foreach ($completed_users as $user) {
        $email = $user['email'];
        $credentials = is_accredible_credentials_exist($email, $group_id);

        if(empty($credentials)) {
            $users_with_empty_credentials[] = array(
                'user_id'    => $user['user_id'],
                'email'      => $user['email'],
                'first_name' => $user['first_name'],
                'last_name'  => $user['last_name'],
                'quiz_id'    => $user['quiz_id'],
                'course_id'  => $course_id,
                'group_id'   => $group_id,
            );
        }
    }

    // STEP 3: Assign Certificate.
    if(empty($users_with_empty_credentials)){
        return array(
            "code" => 200, 
            "message" => "no users to assign credentials." 
        );
    }

    foreach($users_with_empty_credentials as $user){
        $email = $user['email'];
        $group_id = $user['group_id'];
        $name = $user['first_name'] . ' ' . $user['last_name'];
        $response = create_accredible_credentials($email, $group_id, $name);
    }

    // STEP 4: Add tag to AC
    foreach($users_with_empty_credentials as $user){
        $email = $user['email'];
        $tag_id = $tag_id;
        $response = add_activecampaign_tag($email, $tag_id);
    }

    return $users_with_empty_credentials;
}



/**
 * Get all users' emails and IDs.
 * @return array The response data.
 */
function get_all_users() {
    $users = get_users();
    $user_data = array();

    foreach ($users as $user) {
        $user_data[] = array(
            'user_id' => $user->ID,
            'email'   => $user->user_email,
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
        );
    }

    return $user_data;
}


/**
 * Get all users with the role 'subscriber' and paginate the results.
 * @param WP_REST_Request $request The REST request.
 * @return array The paginated user data.
 */
function get_all_course_users($request) {
    $course_id = $request['course_id'];
    $user_data = array();
    
    if (!$course_id) {
        return new WP_Error('missing_parameters', 'Course ID is required.', array('status' => 400));
    }

    $all_users = learndash_get_course_users_access_from_meta($course_id);



    foreach ($all_users as $user_id) {
        $user = get_userdata($user_id);
    
        $user_data[] = array(
            'user_id' => $user->ID,
            'email'   => $user->user_email,
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
        );
    }
    return $user_data;
}

/**
 * Check if Accredible credentials exist.
 * @param array $response The API response.
 * @return array The credentials data.
 */
function is_accredible_credentials_exist($email, $group_id) {
    $url = 'https://api.accredible.com/v1/credentials';
    $args = array(
        'headers' => array(
            'Authorization' => 'Token token=28b4ec39731789e990b49db41640398c', 
        ),
    );

    $response = wp_remote_get("$url?email=" . urlencode($email) . "&group_id=" . urlencode($group_id), $args);

    if (is_wp_error($response)) {
        return $response;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['credentials']) && !empty($data['credentials'])) {
        return $data['credentials'];
    }
    return array(); // Return an empty array if 'credentials' key is not set
}

/**
 * Create credentials in Accredible API with email and group_id.
 * @param string $email The user's email.
 * @param int $group_id The Badge/Certificate group ID.
 * @param string $name The user's name.
 * @return array|WP_Error The response data or WP_Error on failure.
 */
function create_accredible_credentials($email, $group_id, $name) {
    $url = 'https://api.accredible.com/v1/credentials';
    $args = array(
        'headers' => array(
            'Authorization' => 'Token token=28b4ec39731789e990b49db41640398c', 
            'Content-Type'  => 'application/json',
        ),
        'body' => json_encode(array(
            'credential' => array(
                'recipient' => array(
                    'name'  => $name,
                    'email' => $email,
                ),
                'group_id' => $group_id,
            ),
        )),
        'method' => 'POST',
    );

    $response = wp_remote_post($url, $args);

    if (is_wp_error($response)) {
        return $response;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    return $data;
}

/**
 * Add a tag to a contact in ActiveCampaign.
 * @param string $email The user's email.
 * @param int $tag_id The tag ID.
 * @return array|WP_Error The response data or WP_Error on failure.
 */
function add_activecampaign_tag($email, $tag_id) {
    // Get the contact ID by email
    $contact_id = get_activecampaign_contact_id($email);
    if (is_wp_error($contact_id)) {
        return $contact_id;
    }

    // Add the tag to the contact
    $url = ACTIVECAMPAIGN_API_URL . 'contactTags';
    $args = array(
        'headers' => array(
            'Api-Token' => ACTIVECAMPAIGN_API_KEY,
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode(array(
            'contactTag' => array(
                'contact' => $contact_id,
                'tag' => $tag_id,
            ),
        )),
        'method' => 'POST',
    );

    $response = wp_remote_post($url, $args);

    if (is_wp_error($response)) {
        return $response;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    return $data;
}

/**
 * Get the ActiveCampaign contact ID by email.
 * @param string $email The user's email.
 * @return int|WP_Error The contact ID or WP_Error on failure.
 */
function get_activecampaign_contact_id($email) {
    $url = ACTIVECAMPAIGN_API_URL . 'contacts?email=' . urlencode($email);
    $args = array(
        'headers' => array(
            'Api-Token' => ACTIVECAMPAIGN_API_KEY,
        ),
    );

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        return $response;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (empty($data['contacts'])) {
        return new WP_Error('contact_not_found', 'Contact not found.', array('status' => 404));
    }

    return $data['contacts'][0]['id'];
}

////////  Helper Endpoints /////////

/**
 * REST API to return Active Campaigne Tag ID.
 * 
 */

 add_action('rest_api_init', function () {
    register_rest_route('billionminds/v1', '/ac-tag', array(
        'methods'  => 'GET',
        'callback' => 'active_campaign_tag_id',
        'permission_callback' => '__return_true', // For testing; secure as needed
    ));
});

/**
 * REST API to return Accredible Group ID.
 * 
 */

 add_action('rest_api_init', function () {
    register_rest_route('billionminds/v1', '/accredible-group-id', array(
        'methods'  => 'GET',
        'callback' => 'accredible_group_id',
        'permission_callback' => '__return_true', // For testing; secure as needed
    ));
});


/**
 * REST API to return Lesson ID.
 * 
 */

 add_action('rest_api_init', function () {
    register_rest_route('billionminds/v1', '/accredible-credentials', array(
        'methods'  => 'GET',
        'callback' => 'accridible_credentials',
        'permission_callback' => '__return_true', // For testing; secure as needed
    ));
});



/**
 * Get Active Campaign Tag ID.
 * @return array The response data.
 */

function active_campaign_tag_id($data) {
    $tag_name = $data['tag_name'];

    if (!$tag_name) {
        return new WP_Error('missing_parameters', 'AC Tag Name required.', array('status' => 400));
    }

    $url = ACTIVECAMPAIGN_API_URL . 'tags' . '?search=' . urlencode($tag_name);
    $args = array(
        'headers' => array(
            'Api-Token' => ACTIVECAMPAIGN_API_KEY,
        ),
    );

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        return $response;
    }

    $body = wp_remote_retrieve_body($response);
    $response = json_decode($body, true);

    if (empty($response['tags'])) {
        return new WP_Error('tag_not_found', 'Tag not found.', array('status' => 404));
    }

    return array(
        "tag_id" => $response['tags'][0]['id'],
        "tag_name" => $response['tags'][0]['tag'],
    );
}

/**
 * Get Accredible Group ID.
 * @return array The response data.
 */

function accredible_group_id($data) {
    $group_name = $data['group_name'];

    if (!$group_name) {
        return new WP_Error('missing_parameters', 'Group Name required.', array('status' => 400));
    }

    $url = 'https://api.accredible.com/v1/issuer/all_groups' . '?name=' . urlencode($group_name);
    $args = array(
        'headers' => array(
            'Authorization' => ACCREDIBLE_API_KEY, 
        ),
    );

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        return $response;
    }

    $body = wp_remote_retrieve_body($response);
    $response = json_decode($body, true);

    if (empty($response['groups'])) {
        return new WP_Error('group_not_found', 'Group not found.', array('status' => 404));
    }

    return array(
        "group_id" => $response['groups'][0]['id'],
        "group_name" => $response['groups'][0]['name'],
        "course_name" => $response['groups'][0]['course_name'],
    );
}
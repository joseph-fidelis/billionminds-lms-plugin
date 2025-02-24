<?php
/**
 * Active campaign: Custom Helper endpoints for Active Campaign API.
 */



// Define the ActiveCampaign API URL and API key
define('ACTIVECAMPAIGN_API_URL', 'https://billionminds.api-us1.com/api/3/');
define('ACTIVECAMPAIGN_API_KEY', '6001a4b640e44ba003c278599777904ee8e790434b41f3a0867f97a941abcfc0bf0b5c0a');


/**
 * Endpoint to retrieve the tags for a user by email.
 */

add_action('rest_api_init', function () {
    register_rest_route('billionminds/v1', '/user-tags', array(
        'methods'  => 'GET',
        'callback' => 'get_user_tags',
        'permission_callback' => '__return_true', // For testing; secure as needed
        'args' => array(
            'email' => array(
                'required' => true,
                'validate_callback' => function($param, $request, $key) {
                    return is_email($param);
                }
            ),
        ),
    ));
});


/**
 * Get all ActiveCampaign tags for a user by email.
 * @param string $email The user's email.
 * @return array|WP_Error The tags data or WP_Error on failure.
 */
function get_activecampaign_tags_by_email($email) {
    // Get the contact ID by email
    $contact_id = get_activecampaign_contact_id($email);
    if (is_wp_error($contact_id)) {
        return $contact_id;
    }

    // Get the tags for the contact
    $url = ACTIVECAMPAIGN_API_URL . 'contacts/' . $contact_id . '/contactTags';
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

    // Extract the tag values
    $tags = array();
    foreach ($data['contactTags'] as $contactTag) {
        $tags[] = $contactTag['tag'];
    }

    return array('tags' => $tags);
}

/**
 * Get all ActiveCampaign tags for a user by email.
 * @param WP_REST_Request $request The REST request.
 * @return array|WP_Error The tags data or WP_Error on failure.
 */
function get_user_tags($request) {
    $email = $request->get_param('email');
    return get_activecampaign_tags_by_email($email);
}


/**
 * Endpoint to common and diff tags.
 */

 add_action('rest_api_init', function () {
    register_rest_route('billionminds/v1', '/compare-tags', array(
        'methods'  => 'POST',
        'callback' => 'compare_activecampaign_tags',
        'permission_callback' => '__return_true', // For testing; secure as needed
        'args' => array(
            'email' => array(
                'required' => true,
                'validate_callback' => function($param, $request, $key) {
                    return is_email($param);
                }
            ),
            'tags' => array(
                'required' => true,
                'validate_callback' => function($param, $request, $key) {
                    return is_array($param);
                }
            ),
        ),
    ));
});


/**
 * Compare Active Campaign Tags.
 *
 * This function compares tags from Active Campaign based on the provided REST request.
 *
 * @param WP_REST_Request $request The REST request containing the necessary parameters for comparison.
 * @return array The comparison result, including details of matched and unmatched tags.
 */
function compare_activecampaign_tags($request) {
    $params = json_decode($request->get_body(), true);
    $email = $params['email'];
    $compare_tags = $params['tags'];

    // Get the contact ID by email
    $contact_id = get_activecampaign_contact_id($email);
    if (is_wp_error($contact_id)) {
        return $contact_id;
    }

    // Get the tags for the contact
    $url = ACTIVECAMPAIGN_API_URL . 'contacts/' . $contact_id . '/contactTags';
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

    // Extract the tag values
    $tags = array();
    foreach ($data['contactTags'] as $contactTag) {
        $tags[] = $contactTag['tag'];
    }

    // Compare the arrays
    $active_tags = array_intersect($tags, $compare_tags);
    $missing_tags = array_diff($compare_tags, $tags);

    return array(
        'active_tags' => $active_tags,
        'missing_tags' => $missing_tags,
    );
}
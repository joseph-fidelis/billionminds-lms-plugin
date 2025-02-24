<?php

/**
 * LearnDash: Custom Helper endpoints for Learndash API.
 */

 add_action('rest_api_init', function () {
    register_rest_route('billionminds/v1', '/enroll-user', array(
        'methods'  => 'POST',
        'callback' => 'enroll_user_in_course',
        'permission_callback' => '__return_true', // For testing; secure as needed
        'args' => array(
            'user_id' => array(
                'required' => true,
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric($param);
                }
            ),
            'course_id' => array(
                'required' => true,
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric($param);
                }
            ),
        ),
    ));
});


/**
 * Enroll a user in a LearnDash course.
 *
 * @param WP_REST_Request $request The REST request.
 * @return array|WP_Error The response data or WP_Error on failure.
 */
function enroll_user_in_course($request) {
    $user_id = $request->get_param('user_id');
    $course_id = $request->get_param('course_id');

    if (!get_userdata($user_id)) {
        return new WP_Error('invalid_user', 'Invalid user ID.', array('status' => 400));
    }

    if (!get_post($course_id) || get_post_type($course_id) !== 'sfwd-courses') {
        return new WP_Error('invalid_course', 'Invalid course ID.', array('status' => 400));
    }

    $result = ld_update_course_access($user_id, $course_id, false);

    return array(
        'status' => $result,
        'message' => 'User enrolled in course successfully.',
        'user_id' => $user_id,
        'course_id' => $course_id,
    );
}
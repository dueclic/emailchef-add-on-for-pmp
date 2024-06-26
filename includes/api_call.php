<?php
/**
 * Load and return an list for the Emailchef API
 */
function pmproecaddon_load_list_ec(){
    $variables = include("variables.php");
    $list_data = array();
    update_option('pmproecaddon_list_data', $list_data);
    $parameters = array(
        'hidden' => '0',
        'integrations' => '1',
        'limit' => '10',
        'offset' => '0',
        'orderby' => 'cd',
        'ordertype' => 'd',
    );

    $user_ec = get_option('pmproecaddon_plugin_user_ec', '');
    $pass_ec = get_option('pmproecaddon_plugin_pass_ec', '');

    $headers = array(
        'username' => $user_ec,
        'password' => $pass_ec,
    );

    $options = array(
        'headers' => $headers,
        'body' => $parameters,
    );

    $response = wp_remote_get($variables["api_url"], $options);

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        echo 'Error obtaining data: ' . esc_html($error_message);
        return;
    }

    $list_data = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($list_data)) {
        return;
    }

    update_option('pmproecaddon_list_data', $list_data);
}

/**
 * Add a user to the queue to subscribe to an audience
 *
 * @param int  $id_list - The id of the list on Emailchef.
 * @param string $user_email - The user email.
 * @param string $user_login - The user firstname.
 */
function pmproecaddon_suscribe_contact_ec($id_list,$user_email,$user_login){
    $variables = include("variables.php");
    $data = array(
        "instance_in" => array(
            "list_id" => $id_list,
            "status" => "ACTIVE",
            "email" => $user_email,
            "firstname" => $user_login,
            "mode" => "ADMIN"
        )
    );

    $data_json = json_encode($data);

    $user_ec = get_option('pmproecaddon_plugin_user_ec', '');
    $pass_ec = get_option('pmproecaddon_plugin_pass_ec', '');

    //update_option('pmproecaddon_plugin_message', "user_ec" . $user_ec . ' pass_ec' . $pass_ec . " api_contact_url: " . $variables["api_contact_url"]);

    $headers = array(
        'username' => $user_ec,
        'password' => $pass_ec,
        'Content-Type' => 'application/json'
    );

    $options = array(
        'headers' => $headers,
        'body' => $data_json,
    );

    $response = wp_remote_post($variables["api_contact_url"], $options);

    if (is_wp_error($response)) {
        return;
    }
}

/**
 * Unsubscribe a user from a specific list
 *
 * @param int  $id_list - The id of the list on Emailchef.
 * @param int $contact_id - The id of the audience to remove the user from.
 * @param string $user_email - The user email.
 * @param string $user_login - The user firstname.
 */
function pmproecaddon_delete_contact_ec($id_list,$contact_id,$user_email,$user_login){
    $variables = include("variables.php");
    $user_ec = get_option('pmproecaddon_plugin_user_ec', '');
    $pass_ec = get_option('pmproecaddon_plugin_pass_ec', '');

    //update_option('pmproecaddon_plugin_message', "user_ec" . $user_ec . ' pass_ec' . $pass_ec . " api_contact_url: " . $variables["api_contact_url"]);

    $headers = array(
        'username' => $user_ec,
        'password' => $pass_ec,
        'Content-Type' => 'application/json'
    );

    $options = array(
        'headers' => $headers
    );

    $response = wp_remote_post($variables["api_unsuscribe_url"] . "?contact_id=" . $contact_id . "&list_id=" . $id_list, $options);

    if (is_wp_error($response)) {
        return;
    }
}

/**
 * Queue an update to an audience
 *
 * @param int  $id_list - The id of the list on Emailchef.
 * @param string $user_email - The user email.
 * @param string $user_login - The user firstname.
 * @param string $firstname - The user firstname.
 * @param string $lastname - The user lastname.
 */
function pmproecaddon_update_contact($id_list,$user_email,$user_login,$firstname,$lastname){
    $variables = include("variables.php");

    $contact_id = pmproecaddon_get_contact($user_email,$id_list);
    if(isset($contact_id)){
        $data = array(
            "instance_in" => array(
                "list_id" => $id_list,
                "status" => "ACTIVE",
                "email" => $user_email,
                "firstname" => $firstname,
                "lastname" => $lastname,
                "mode" => "ADMIN"
            )
        );

        $data_json = json_encode($data);

        $user_ec = get_option('pmproecaddon_plugin_user_ec', '');
        $pass_ec = get_option('pmproecaddon_plugin_pass_ec', '');

        //update_option('pmproecaddon_plugin_message', "user_ec" . $user_ec . ' pass_ec' . $pass_ec . " api_contact_url: " . $variables["api_contact_url"]);

        $headers = array(
            'username' => $user_ec,
            'password' => $pass_ec,
            'Content-Type' => 'application/json'
        );

        $options = array(
            'method' => 'PUT',
            'timeout' => 45,
            'headers' => $headers,
            'body' => $data_json,
        );

        $response = wp_remote_request($variables["api_contact_url"] . "/" . $contact_id, $options);

        if (is_wp_error($response)) {
            return;
        }
    }
}

/**
 * Get contact by email
 *
 * @param string $user_email - The user email.
 * @param int  $id_list - The id of the list on Emailchef.
 */
function pmproecaddon_get_contact($user_email,$list_id){
    $variables = include("variables.php");

    $url_contact = $variables['api_contact_url'] . "?limit=10&list_id=". $list_id."&offset=0&orderby=e&ordertype=a&query_string=" . $user_email;
    $user_ec = get_option('pmproecaddon_plugin_user_ec', '');
    $pass_ec = get_option('pmproecaddon_plugin_pass_ec', '');

    //update_option('pmproecaddon_plugin_message', "user_ec" . $user_ec . ' pass_ec' . $pass_ec . " api_contact_url: " . $variables["api_contact_url"]);

    $headers = array(
        'username' => $user_ec,
        'password' => $pass_ec,
        'Content-Type' => 'application/json'
    );

    $options = array(
        'headers' => $headers,
        'body' => '',
    );

    $response = wp_remote_get($url_contact, $options);

    if (is_wp_error($response)) {
        return;
    }

    $id = 0;
    if(isset($response)){
        $contacts = json_decode($response['body'], true);
        if(count($contacts) > 0){
            $contact = $contacts[0];
            $id = $contact['id'];
        }
    }

    return $id;
}
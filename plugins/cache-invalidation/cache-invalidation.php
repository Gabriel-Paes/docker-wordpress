<?php
/* 
    Plugin Name: Cache Invalidation
    Description: Plugin to invalidate the cache by sending POST request when publishing or editing an article.
    Version: 1.0.0
    Author: 0xGameStudio
*/

function cache_invalidation_plugin_menu() {
    add_menu_page('Cache Invalidation Settings', 'Cache Invalidation', 'manage_options', 'cache-invalidation-settings', 'cache_invalidation_settings_page', 'dashicons-database');
}

add_action('admin_menu', 'cache_invalidation_plugin_menu');

function cache_invalidation_settings_page() {
    ?>
    <div class="wrap" >
        <h1>Cache Invalidation Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('cache-invalidation-settings-group');
            do_settings_sections('cache-invalidation-settings');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">URL:</th>
                    <td>
                        <input type="text" name="cache_invalidation_url" value="<?php echo esc_attr(get_option('cache_invalidation_url')); ?>" style="width: 353px;" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Prefixos:</th>
                    <td>
                        <label for="prefixInput">Insira os prefixos separados por ; (ponto e vírgula)</label><br>
                        <textarea id="prefixInput" name="cache_invalidation_prefixes" rows="4" cols="50"><?php echo esc_textarea(get_option('cache_invalidation_prefixes')); ?></textarea>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Token:</th>
                    <td>
                        <input type="text" name="cache_invalidation_token" value="<?php echo esc_attr(get_option('cache_invalidation_token')); ?>" style="width: 353px;" />
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <div>
            <h2>Logs</h2>
            <textarea id="cache_invalidation_logs" rows="10" cols="84" style="resize: none;" readonly><?php echo esc_textarea(get_option('cache_invalidation_logs')); ?></textarea>
        </div>
        
        <script>
            var logs = document.getElementById('cache_invalidation_logs');
            
            logs.scrollTop = logs.scrollHeight;
        </script>
    </div>
    <?php
}

add_action('admin_init', 'cache_invalidation_register_settings');

function cache_invalidation_register_settings() {
    register_setting('cache-invalidation-settings-group', 'cache_invalidation_url');
    register_setting('cache-invalidation-settings-group', 'cache_invalidation_prefixes');
    register_setting('cache-invalidation-settings-group', 'cache_invalidation_token');
}

function cache_invalidation_send_post_request($post_ID) {
    $url = get_option('cache_invalidation_url');
    $prefixes_string = get_option('cache_invalidation_prefixes');
    $token = get_option('cache_invalidation_token');

    if (empty($url) || empty($prefixes_string) || empty($token)) {
        cache_invalidation_log('Error incomplete plugin settings');
        return;
    }

    $prefixes = array_filter(explode(';', $prefixes_string));
    $data = array(
        'prefixes' => $prefixes
    );

    $args = array(
        'body' => json_encode($data),
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ),
    );

    $response = wp_remote_post($url, $args);

    if (is_wp_error($response)) {
        cache_invalidation_log('Error sending POST request, message: '. $response->get_error_message());
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code == 200) {
            cache_invalidation_log('Successful sending POST request to: '. $url);
        } else {
            cache_invalidation_log('Error sending POST request, status code: '. $response_code);
        }
    }
}

function cache_invalidation_post($post_ID) {
    if (wp_is_post_revision($post_ID) || wp_is_post_autosave($post_ID)) {
        return;
    }

    $post_status = get_post_status($post_ID);

    cache_invalidation_log('Post ID: '. $post_ID .' - Published');
    cache_invalidation_log('Status: '. $post_status);

    cache_invalidation_send_post_request($post_ID);
}

function cache_invalidation_edit_post($post_ID) {
    if (wp_is_post_revision($post_ID) || wp_is_post_autosave($post_ID)) {
        return;
    }

    cache_invalidation_log('Post ID: '. $post_ID .' - Edited');

    cache_invalidation_send_post_request($post_ID);
}

add_action('publish_post', 'cache_invalidation_post');
//add_action('edit_post', 'cache_invalidation_edit_post');

function cache_invalidation_log($message) {
    $current_datetime = current_time('mysql');

    $log_entry = "[$current_datetime] - $message\n";

    $existing_logs = get_option('cache_invalidation_logs', '');
    $updated_logs = $existing_logs . $log_entry;
    update_option('cache_invalidation_logs', $updated_logs);
}

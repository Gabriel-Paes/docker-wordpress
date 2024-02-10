<?php
/**
 * Plugin Name: Plugin
 */

function my_content_footer( $content ) {
    if ( is_single( )) {
        return $content . '<p>Hello, World!</p>';
    }
}
add_filter('the_content', 'my_content_footer');
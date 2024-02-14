<?php
/*
Plugin Name: Recent Posts List
Description: Displays a list of recent posts with titles and dates.
Version: 1.0
Author: Gabriel Paes
*/

function recent_posts_list_shortcode() {
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => 5,
    );

    $recent_posts = new WP_Query( $args );

    if ( $recent_posts->have_posts() ) {
        $output = '<ul>';
        while ( $recent_posts->have_posts() ) {
            $recent_posts->the_post();
            $output .= '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a> - ' . get_the_date() . '</li>';
        }
        $output .= '</ul>';
    } else {
        $output = 'Nenhum post encontrado.';
    }

    wp_reset_postdata();

    return $output;
}
add_shortcode( 'recent_posts_list', 'recent_posts_list_shortcode' );

<?php

if ( !function_exists('get_the_content_block')) {
    /**
     * Fetches the specified block
     *
     * @param int $block
     * @param int $post_id
     *
     * @return string
     */
    function get_the_content_block( $block = 1, $post_id = 0 ) {

        global $post, $page, $more, $preview, $pages, $multipage;

        // Fetch the specified post if an id is specified
        if ( !empty($post_id) ) {
            $post = get_post($post_id);
        }

        $output = '';

        // If the requested block exists, return it
        if ( isset( $post->post_blocks ) && isset( $post->post_blocks[$block] ) ) {
            $output = $post->post_blocks[$block];
        }

        return $output;
    }}

if ( !function_exists('the_content_block')) {
    /**
     * Outputs the specified block to the page.
     *
     * @param int $block
     * @param int $post_id
     */
    function the_content_block( $block = 1, $post_id = 0 ) {
        $content = get_the_content_block($block,$post_id);
        $content = apply_filters( 'the_content', $content );
        $content = str_replace( ']]>', ']]&gt;', $content );
        echo $content;
    }}
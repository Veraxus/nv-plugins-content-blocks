<?php
/*
Plugin Name: Content Blocks (Nouveau)
Description: Allows the main content area to be broken into "blocks" that can be more easily used in themes.
Author: Matt Van Andel
Version: β1
Author URI: http://nouveauframework.com/
Plugin URI: http://nouveauframework.com/contentblocks
License: GPLv2 or later
*/

//Initialize the plugin
NV_ContentBlocks::init();
require_once plugin_dir_path(__FILE__).'inc/functions.php';


/**
 * This controls the Selective Autop
 */
class NV_ContentBlocks {

    /**
     * Hook everything...
     */
    public static function init() {

        // Add new buttons to TinyMCE
        add_action('init', array('NV_ContentBlocks','mceButtonHooks') );

        // Enable help text
        add_action('admin_head', array('NV_ContentBlocks','help') );

        // Modify the post object
        add_action('the_post', array('NV_ContentBlocks','createBlocks') );
    }


    /**
     * Allows modification of the post object before it it used. We pre-parse the content into a new post_blocks
     * property of the post object. This property is an array of block content.
     *
     * @param $post Ref.
     */
    public static function createBlocks($post) {
        $post->post_blocks = array();

        if ( preg_match( '/<!--block(.*?)?-->/', $post->post_content, $matches ) ) {
            $post->post_blocks = explode( $matches[0], $post->post_content );
        }
    }


    /**
     * Add hooks for the buttons
     */
    public static function mceButtonHooks() {

        // Register the Javascript plugin with TinyMCE
        add_filter("mce_external_plugins", array('NV_ContentBlocks','mceRegisterJS') );

        // Register the new button with TinyMCE
        add_filter('mce_buttons', array('NV_ContentBlocks','mceRegisterButtons') );

        add_editor_style( plugins_url('/css/editor.css',__file__) );

    }


    /**
     * Register new buttons with the Rich Text editor
     *
     * @param $buttons
     *
     * @return mixed
     */
    public static function mceRegisterButtons($buttons) {
//        array_push( $buttons, 'nv_block', 'dropcap', 'showrecent' ); // dropcap', 'recentposts
        array_push( $buttons, 'nv_block', 'dropcap' ); // dropcap', 'recentposts
        return $buttons;
    }


    /**
     * Load the JS for the TinyMCE plugin
     *
     * @param $plugin_array
     *
     * @return mixed
     */
    public static function mceRegisterJS($plugin_array) {
        $plugin_array['NvContentBlocks'] = plugins_url('/js/tinymce-blocks.js',__file__);
        return $plugin_array;
    }


    /**
     * Customizes help text for the admin.
     *
     * Used by hook: admin_head
     *
     * @see add_action('admin_head',$func)
     * @global WP_Screen $current_screen Information about the current admin screen
     * @since Nouveau 1.0
     */
    public static function help() {
        global $wp_meta_boxes;
        $current_screen = get_current_screen();

        //Add new help text
        switch ( $current_screen->base ) {

            case 'post':
            case 'edit':
            case 'add':
                get_current_screen()->add_help_tab( array(
                    'id'      => 'nvcontentblocks',
                    'title'   => __( 'Content Blocks', 'nouveau' ),
                    'content' => '<p>'.__( "Content blocks separate the main content area into separate blocks. Similar to the &lt;!--more--&gt; tag, <code>the_content()</code> will only display content up to the first &lt;!--block--&gt; tag. You may use <code>the_block()</code> or <code>get_the_block()</code> to load further content blocks at any point within the loop of your theme. <code>the_block()</code> can take one argument: the number of the block you want to load. Default is 1 (first block), while <code>0</code> will load <code>the_content()</code> instead.", 'nouveau' ).'</p>',
                ) );
                break;

            default:
                break;
        }

    }

}
<?php
/*
Plugin Name: NOUVEAU Content Blocks
Description: Allows the main content area to be broken into "blocks" that can be more easily used in themes.
Author: Veraxus
Version: 0.2
Author URI: http://nouveauframework.com/
Plugin URI: http://nouveauframework.com/contentblocks
License: GPLv2 or later
*/

//Initialize the plugin
NV_ContentBlocks::init();

// Load the global theme functions
require_once plugin_dir_path(__FILE__).'inc/theme-functions.php';


/**
 * This class contains all the bootstrapping needed to get Content Blocks working reliably.
 */
class NV_ContentBlocks {

    /**
     * Hook everything...
     */
    public static function init() {

        // Add new buttons to TinyMCE
        add_action('init', array(__CLASS__,'mceButtonHooks') );

        // Enable help text
        add_action('admin_head', array(__CLASS__,'help') );

        // Modify the post object
        add_action('the_post', array(__CLASS__,'createBlocks') );
    }


    /**
     * Allows modification of the post object before it it used. We pre-parse the content into a new post_blocks
     * property of the post object. This property is an array of block content.
     *
     * @param $post
     */
    public static function createBlocks($post) {
        $post->post_blocks = array();

        // Standardize the block delimiters
        $temp_content = preg_replace('/<!--block(.*)?-->/','<!--block-->',$post->post_content);

        // Use delimiters to split the content into blocks
        if ( preg_match( '/<!--block-->/', $temp_content, $matches ) ) {
            $post->post_blocks = explode( $matches[0], $temp_content );
        }
    }


    /**
     * Add hooks for the buttons
     */
    public static function mceButtonHooks() {

        // Register the Javascript plugin with TinyMCE
        add_filter("mce_external_plugins", array(__CLASS__,'mceRegisterJS') );

        // Register the new button with TinyMCE
        add_filter('mce_buttons', array(__CLASS__,'mceRegisterButtons') );

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
        // Add a reference to our JS button "buttonContentBlock" so that TinyMCE will load it
        array_push( $buttons, 'buttonContentBlock' );
        return $buttons;
    }


    /**
     * Enqueue the plugin JS with TinyMCE using WordPress mce_external_plugins hook
     *
     * @param array $plugin_array
     *
     * @return mixed
     */
    public static function mceRegisterJS($plugin_array) {
        // Array key is the plugins JS class, the value is the js path.
        $plugin_array['NVContentBlocks'] = plugins_url('/js/tinymce-blocks.js',__file__);
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
                    'content' => '<p>'.__( "Content blocks split the main content area into multiple chunks, similar to the <code>&lt;!--more--&gt;</code> tag. Authors can even give a block a title to more easily remember block topics when writing content.", 'nvLangScope' ).'</p>'.
                        '<p>'.__("<b>For Theme Developers</b> – The theme function <code>the_content()</code> will continue to load ALL of a pages content (including all blocks). To display only specific blocks in your theme templates, use either <code>the_block()</code> or <code>get_the_block()</code> and specify the number of the block you want to load. Note: <code>0</code> will load only the content above the first block.",'nvLangScope').'</p>',
                ) );
                break;

            default:
                break;
        }

    }

}
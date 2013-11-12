(function($) {

    console.log('Loading TinyMCE plugin...');

    tinymce.create('tinymce.plugins.NVContentBlocks', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed, url) {

            console.log('Content Blocks TinyMCE Plugin initialized!');

            // Set img url (which is NOT not under default js dir)
            var img = url.replace(/\/js/g,"/images");

            // Set delimiter for visual mode
            var blockImg = '<div class="nv-content-block">Content Block</div>';

            // Register the command that will insert the block in visual mode
            ed.addCommand('insertContentBlock', function() {

                // What node is the caret currently inside?
                var node = ed.selection.getNode();

                if ( ed.dom.hasClass(node,'nv-content-block') ) {
                    // We are inside a divider, delete it
                    ed.dom.remove(node);
                }
                else {
                    // NOT inside divider, use TinyMCE mceInsertContent command to insert our divider
                    ed.execCommand('mceInsertContent', false, blockImg);
                }
            });

            // Add a button to TinyMCE that triggers our insertContentBlock command
            ed.addButton('buttonContentBlock', {
                title : 'Add a Content Block Separator',
                cmd : 'insertContentBlock',
                image : img + '/page.gif'
            });

            // Pass along our custom variables to our custom handler...
            this._handleBlockBreak(ed, url, img, blockImg);

        },

        /**
         * Swaps the delimiter with a tag between rich text and html mode
         *
         * @param editor
         * @param url
         * @private
         */
        _handleBlockBreak : function(editor, url, img, blockImg) {

            /**
             * Text => Visual Switch
             *
             * This is triggered when switching from TEXT mode to VISUAL MODE.
             *
             * This is also automatically triggered within onPostRender whenever the Visual Editor is loaded.
             */
            editor.onBeforeSetContent.add(function(ed, o) {
                console.log('onBeforeSetContent()');
                if ( o.content ) {

                    o.content = o.content.replace(
                        /<!--block\s*\(?(.*)?\)?-->/g,
                        function(obj,grp) {

                            if ( grp==='undefined' | grp.trim()==='' ) {
                                // If no message is specified, default to...
                                grp = 'Content Block';
                            }
                            else {
                                // strip out opening and closing parentheses
                                grp = grp.replace(/^\s*\(?/, '');
                                grp = grp.replace(/\)?$/, '');
                            }
                            obj = '<div class="nv-content-block">'+grp+'</div>';
                            return obj;
                        }
                    );

                }
            });


            /**
             * Visual => Text Switch
             *
             * This converts the visual content into plain, raw database content. This is triggered when switching
             * from VISUAL to TEXT MODE, when saving, and also periodically (every couple seconds).
             *
             * NOTE: The stripping of <br> and <p> tags happens AFTER this is executed and newlines are ignored. Use
             * <br> or <p> wraps in this function to add blank newlines in the plain text editor.
             */
            editor.onPostProcess.add(function(ed, o) {
                console.log('onPostProcess()');
                if (o.get) {

                    console.log(o.content);

                    o.content = o.content.replace( /<div\sclass="nv-content-block">([^<]*)<\/div>/g, function(obj,grp) {
                        if (grp.trim()==='' || grp.trim()==='undefined') {
                            grp = 'Content Block';
                        }
                        else {
                            grp = grp.replace( /\n|\r/g, '' );
                            grp = grp.replace( /<br\s?\/?>/g, '' );
                        }
                        obj = '<p><!--block ('+grp+')--></p>';
                        return obj;
                    });

                    console.log(o.content);

                }
            });


            /**
             * This is triggered last when the visual mode is loaded.
             */
//            editor.onNodeChange.add(function(ed, cm, n) {
//                console.log('onNodeChange()');
//                cm.setActive('wp_block', n.nodeName === 'IMG' && ed.dom.hasClass(n, 'mce-nv-block'));
//            });
        },


        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function() {
            return {
                longname : 'NOUVEAU Content Blocks',
                author : 'Matt Van Andel',
                authorurl : 'http://nouveauframework.org/',
                infourl : 'http://nouveauframework.org/content-blocks',
                version : "0.1"
            };
        }
    });

    // Register plugin with TinyMCE
    tinymce.PluginManager.add( 'NVContentBlocks', tinymce.plugins.NVContentBlocks );
})(jQuery);
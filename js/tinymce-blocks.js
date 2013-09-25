(function() {
    tinymce.create('tinymce.plugins.NvContentBlocks', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(ed, url) {

            // Set img url (not under js dir)
            var img = url.replace(/\/js/g,"/images");
            var blockImg = '<img src="' + img + '/trans.gif" class="mce-nv-block mceItemNoResize" title="Block Delimiter" />';

            // Register the nvblock command for the nv_block button
            ed.addCommand('nvblock', function() {
                ed.execCommand('mceInsertContent', false, blockImg);
            });

            // Register the dropcap button
            ed.addButton('nv_block', {
                title : 'Add a Content Block Separator',
                cmd : 'nvblock',
                image : img + '/page.gif'
            });

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

            // Display morebreak instead if img in element path
            editor.onPostRender.add(function() {
                if (editor.theme.onResolveName) {
                    editor.theme.onResolveName.add(function(th, o) {
                        if (o.node.nodeName == 'IMG') {
                            if ( editor.dom.hasClass(o.node, 'mce-nv-block') ) {
                                o.name = 'nvblock';
                            }
                        }

                    });
                }
            });

            // Replace <!--block--> with images for HTML editor
            editor.onBeforeSetContent.add(function(ed, o) {
                if ( o.content ) {
                    o.content = o.content.replace(/<!--block(.*?)-->/g, blockImg);
                }
            });

            // Replace images with <!--block--> for the visual editor
            editor.onPostProcess.add(function(ed, o) {
                if (o.get) {
                    o.content = o.content.replace(/<img[^>]+>/g, function(im) {

                        if (im.indexOf('class="mce-nv-block') !== -1) {
                            im = '<!--block-->';
                        }
                        return im;

                    });
                }
            });

            // Set active buttons if user selected block
            editor.onNodeChange.add(function(ed, cm, n) {
                cm.setActive('wp_block', n.nodeName === 'IMG' && ed.dom.hasClass(n, 'mce-nv-block'));
            });
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
                author : 'Veraxus',
                authorurl : 'http://nouveauframework.org/',
                infourl : 'http://nouveauframework.org/content-blocks',
                version : "0.1"
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add( 'NvContentBlocks', tinymce.plugins.NvContentBlocks );
})();
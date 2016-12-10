/*!
 * UF Config Manager - Config Manager Widget
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2016 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

(function( $ ){

    'use strict';

    var options = {};

    var methods = {
        init : function(optionsArg) {

            // Setup options
            options = $.extend( options, $.fn.ConfigManager.defaultOptions, optionsArg );

            // To use this inside sub-functions
            var elements = this;

            // Get the currently selected panel from the url anchor and switch to it
            var hash = window.location.hash.substr(1);
            if (hash != undefined && hash !== "") {
                $(elements).hide();
                $("#"+hash).show();

                // Change the menu
                $(options.menu).find("li").removeClass('active');
                $(options.menu).find('a[href="#'+hash+'"]').parent().addClass("active");
            }

            // Set the menu
            $(options.menu).find("li > a").click(function () {

                // Change the menu first
                $(options.menu).find("li").removeClass('active');
                $(this).parent().addClass("active");

                // Change the displayed forms next
                $(elements).hide();
                $("#"+$(this).data('target')).show();
            });

            // For each element the plugin is called on
            this.each(function() {

                // To use this inside sub-functions
                var formPanel = this;

                // ufForm instance. Don't need FormGeneator now
                $(formPanel).find("form").ufForm({
                    validators: options.validators[ $(formPanel).attr('id') ],
                    msgTarget: $(formPanel).find("form .form-alerts")
                }).on("submitSuccess.ufForm", function() {
                    // Forward to settings page on success
                    window.location.reload(true);
                }).on("submitError.ufForm", function() {
                    $(formPanel).find("form .form-alerts").show();
                });

            });
            return;
        }
    };

    /*
     * Main plugin function
     */
    $.fn.ConfigManager = function(methodOrOptions) {
        if ( methods[methodOrOptions] ) {
            return methods[ methodOrOptions ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof methodOrOptions === 'object' || ! methodOrOptions ) {
            // Default to "init"
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  methodOrOptions + ' does not exist on jQuery.ConfigManager' );
        }
    };

    /*
     * Default plugin options
     */
    $.fn.ConfigManager.defaultOptions = {
        menu : $(".configMenu"),
        validators: {}
    };


})( jQuery );
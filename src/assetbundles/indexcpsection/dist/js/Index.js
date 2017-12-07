/**
 * Pic Puller plugin for Craft CMS
 *
 *  Field JS
 *
 * @author    John F Morton
 * @copyright Copyright (c) 2017 John F Morton
 * @link      https://picpuller.com
 * @package   PicPuller
 * @since     3.0.0
 */

 ;(function ( $, window, document, undefined ) {

    var pluginName = "PicPullerFieldType",
        defaults = {
        };

    // Plugin constructor
    function Plugin( element, options ) {
        this.element = element;

        this.options = $.extend( {}, defaults, options) ;

        this._defaults = defaults;
        this._name = pluginName;

        this.init();
    }

    Plugin.prototype = {

        init: function(id) {
            var _this = this;

            $(function () {

/* -- _this.options gives us access to the $jsonVars that our FieldType passed down to us */

            });
        }
    };

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName,
                new Plugin( this, options ));
            }
        });
    };

})( jQuery, window, document );

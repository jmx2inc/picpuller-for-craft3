/**
 * Pic Puller plugin for Craft CMS
 *
 *  Field JS
 *
 * @author    John F Morton
 * @copyright Copyright (c) 2017 John F Morton
 * @link      https://picpuller.com
 * @package   PicPuller
 * @since     3.0.0PicPuller
 */

;(function($, window, document, undefined) {

    var pluginName = "PicPuller",
        defaults = {};

    // Plugin constructor
    function Plugin(element, options) {
        this.$element = $(element);

        this.options = $.extend({}, defaults, options);

        this._defaults = defaults;
        this._name = pluginName;

        this.init();
    }

    Plugin.prototype = {
        getElement: function(element) {
            return this.$element.find('#' + this.options.namespace + '-' + element);
        },
        init: function() {
            var _this = this;
            var $deleteButton = this.getElement('delete');

            this.$inputTextField = this.getElement('input');
            this.$previewField = this.getElement('preview');
            this.$adminPath = this.options.adminPath;
            this.$lookup = this.getElement('lookup');

            // only used in init
            var $browseInstagramButton = this.getElement('btn');
            var $ppModalWindow = null;


            function doLookup() {
                // console.log($inputTextField);
                // console.log('_this.options',_this.options);
                if (_this.$inputTextField.val() !== '') {
                    // console.log('There is text present in the PP field.');
                    var inputTextFieldVal = _this.$inputTextField.val();
                    if (inputTextFieldVal.indexOf('instagr') !== -1) {
                        _this.translateURLtoMediaID(_this._trim11(inputTextFieldVal));
                    } else {
                        _this.loadMediaById(inputTextFieldVal);
                    }
                } else {
                    // console.log('No text found in input');
                }
            }

            doLookup();

            $browseInstagramButton.on('click', function() {
                var divPart1 = '<div class="pp-thumbs modal elementselectormodal"><div class="body"><div class="content" ><div class="main"><div class="toolbar"><h3>Instagram Images</h3>';

                var divPart3 = '</div><div class="elements"></div></div></div></div><div class="footer"><div class="buttons rightalign"><div class="btn cancel" tabindex=0>Cancel</div></div></div></div>';

                var $div = $(divPart1 + divPart3);

                if (!$ppModalWindow) {
                    // the Ganish modal window doesn't exist yet,
                    // so build it and then show it
                    $ppModalWindow = new Garnish.Modal($div, {
                        resizeable: true,
                        draggable: false,
                    });
                    _this.loadImages();
                    // Add listeners to modal so user can do things with it
                    addModelButtonActions($ppModalWindow);
                } else {
                    // the Ganish modal window has already been instantiated so just show it again
                    $ppModalWindow.show();
                }
            });

            _this.$lookup.on('click', doLookup);

            $deleteButton.on('click', function() {
                _this.$inputTextField.val('');
                closePreview();
            });

            _this.$inputTextField.keyup(function() {
                _this._checkForValueinPPfield();
                closePreview();
            });

            function closePreview() {
                _this.$previewField.slideUp().html('');
            }

            function addModelButtonActions(ppmodalwindow) {
                var thisModal = $(ppmodalwindow.$container[0]);
                // console.log('thisModal ', thisModal);
                var thisSearchBt = thisModal.find('.pp-search .btn');
                thisModal.on('click', '.btn', function(e) {
                    if ($(e.target).hasClass('cancel')) {
                        ppmodalwindow.hide();
                    }
                    if ($(e.target).hasClass('igpic_next')) {
                        if ($(e.target).hasClass('type-media')) {
                            $(e.target).removeClass('btn').removeClass('igpic_next').addClass('next_loading');
                            _this.loadImages($(e.target).data('nextmaxid'));
                        }
                    }
                });
                thisModal.on('click', '.selectable', function(e) {
                    var selectedMediaId = $(e.currentTarget).data('mediaid');
                    _this.$inputTextField.val(selectedMediaId);
                    _this.loadMediaById(selectedMediaId);
                    ppmodalwindow.hide();
                });

            }

            // $(function () {
            //     window.debuggingPP = _this;
            //     /*
            //     * _this.options gives us access to the $jsonVars that our FieldType passed down to us
            //     *
            //     * */

            //
            //
            // });
        },

        loadImages: function(nextMaxId) {
            var _this = this;
            var localNextMaxId = (nextMaxId === undefined) ? '' : nextMaxId;
            var theURL = "/" +  _this.$adminPath + "/pic-puller/mediarecent/" + localNextMaxId;
            $.ajax({
                url: theURL,
                dataType: 'json',
                success: function(data) {
                    $('.igpic_next').remove();
                    $('.type-media').remove();
                    $('.next_loading').remove();
                    var thumbField = $('.pp-thumbs .main .elements');
                    var loadMore = '<div class="igpic igpic_end">' + '' + '</div>';
                    if (data.meta.nextMaxId !== '') {
                        loadMore = '<div class=" igpic igpic_next type-media btn" data-nextmaxid="' + data.meta.nextMaxId + '"></div>';
                    }
                    for (var i = 0; i < data.ppimages.length; i++) {
                        var mediatype = data.ppimages[i].video ? 'video' : 'photo';
                        var pic = '<div class="igpic selectable" data-mediaid="' + data.ppimages[i].media_id + '" style="background-image: url(' + data.ppimages[i].url + '); background-size:cover;background-position: center;"><div class="' + mediatype + '"></div></div>';
                        thumbField.append(pic);
                    }
                    thumbField.append(loadMore);
                }
            });

        },

        loadMediaById: function(mediaId) {
            var _this = this;
            var theURL = "/" + _this.$adminPath + "/pic-puller/mediabyid/" + mediaId;
            // console.log(theURL);
            $.ajax({
                url: theURL,
                dataType: 'json',
                success: function(data) {
                    var thePreview;
                    // console.log('success', data);
                    if ( data.ppimages ) {
                        thePreview = "<div><div class='igpic_image' style='background-image: url(" + data.ppimages[0].url + "); background-size:cover;background-position: center;'></div><div class='igpic_info'><p class='titlefield'>Preview from Instagram</p><p class='caption'><a href='" + data.ppimages[0].link + "' target='_blank' title='Open image link in new window'>" + data.ppimages[0].caption + "</a></p><p class='igpic_author'>By <strong>" + data.ppimages[0].full_name + "</strong> @" + data.ppimages[0].username + "</p></div></div>";
                        _this.$previewField.html(thePreview);
                        _this.$previewField.slideDown();
                        _this.$lookup.addClass('hidden');
                    }
                    if ( data.error ) {
                        thePreview = "<div><div class='igpic_info'><p class='titlefield'>Error was returned:</p><p>Code: "+data.error[0].code+"</p><p class='caption'>"+ data.error[0].error_type +": " + data.error[0].message+"</div></div>";
                        _this.$previewField.html(thePreview);
                        _this.$previewField.slideDown();
                        _this.$lookup.addClass('hidden');
                    }
                },
                error: function(data) {
                    var errorData = $.parseJSON(data.responseText);
                    console.log('error', errorData);
                }
            });
        },

        translateURLtoMediaID: function(url) {
            var _this = this;
            var media_id = null;
            $.ajax({
                url: "https://api.instagram.com/oembed/?url=" + url,
                contentType: 'text/plain',
                xh: {
                    withCredentials: false
                },
                dataType: 'jsonp',
                success: function(data) {
                    // console.log('Data received from Instagram oembed.');
                    // console.log(data);
                    // console.log(data.media_id);
                    if (data.media_id) {
                        media_id = data.media_id;
                        _this.$inputTextField.val(media_id);
                        _this.loadMediaById(media_id, _this.$adminPath);
                    } else {
                        return null;
                    }
                }
            });
        },

        _trim11: function(str) {
            str = str.replace(/^\s+/, '');
            for (var i = str.length - 1; i >= 0; i--) {
                if (/\S/.test(str.charAt(i))) {
                    str = str.substring(0, i + 1);
                    break;
                }
            }
            return str;
        },
        //,

        _checkForValueinPPfield: function() {

            var myValue = this.$inputTextField.val();
            if (myValue !== '') {
                this.$lookup.removeClass('hidden');
                return true;
            } else {
                this.$lookup.addClass('hidden');
                return false;
            }
        }
    };

    // A really lightweight plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[pluginName] = function(options) {
        return this.each(function() {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName,
                    new Plugin(this, options));
            }
        });
    };

})(jQuery, window, document);

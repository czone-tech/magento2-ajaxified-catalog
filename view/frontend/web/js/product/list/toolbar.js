
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'jquery/ui'
], function ($, alert) {
    /**
     * ProductListToolbarForm Widget - this widget is setting cookie and submitting form according to toolbar controls
     */
    $.widget('czone.productListToolbarForm', {

        options:
        {
            modeControl: '[data-role="mode-switcher"]',
            directionControl: '[data-role="direction-switcher"]',
            orderControl: '[data-role="sorter"]',
            limitControl: '[data-role="limiter"]',
            pagerControl: '.pages .page',
            mode: 'product_list_mode',
            direction: 'product_list_dir',
            order: 'product_list_order',
            limit: 'product_list_limit',
            pager: 'page',
            modeDefault: 'grid',
            directionDefault: 'asc',
            orderDefault: 'position',
            limitDefault: '9',
            pagerDefault: '1',
            contentContainer: '.column.main',
            url: ''
        },


        _create: function () {
            this._bind($(this.element).find(this.options.modeControl), this.options.mode, this.options.modeDefault);
            this._bind($(this.element).find(this.options.directionControl), this.options.direction, this.options.directionDefault);
            this._bind($(this.element).find(this.options.orderControl), this.options.order, this.options.orderDefault);
            this._bind($(this.element).find(this.options.limitControl), this.options.limit, this.options.limitDefault);
            this._bind($(this.element).find(this.options.pagerControl), this.options.pager, this.options.pagerDefault);
        },


        _bind: function (element, paramName, defaultValue) {
            if (element.is('select')) {
                element.on('change', { paramName: paramName, default: defaultValue }, $.proxy(this._processSelect, this));
            } else {
                element.on('click', { paramName: paramName, default: defaultValue }, $.proxy(this._processLink, this));
            }
        },


        _processLink: function (event) {
            event.preventDefault();
            this.ajaxSubmit(
                event.data.paramName,
                $(event.currentTarget).data('value'),
                event.data.default
            );
        },


        _processSelect: function (event) {
            event.preventDefault();
            this.ajaxSubmit(
                event.data.paramName,
                event.currentTarget.options[event.currentTarget.selectedIndex].value,
                event.data.default
            );
        },

        _replaceBrowserUrl: function (url, paramData) {
            if (!url) {
                return;
            }
            if (paramData.length > 0) {
                url += '?' + paramData;
            }

            if (typeof history.replaceState === 'function') {
                history.replaceState(null, null, url);
            }
        },

        _prepareParams: function (urlParams, paramName, paramValue, defaultValue) {
            var paramData = {},
                parameters;

            for (var i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                if (parameters[1] !== undefined) {
                    paramData[parameters[0]] = parameters[1];
                } else {
                    paramData[parameters[0]] = '';
                }
            }

            paramData[paramName] = paramValue;
            if (paramValue == defaultValue) {
                delete paramData[paramName];
            }

            return window.decodeURIComponent($.param(paramData).replace(/\+/g, '%20'));
        },


        _updateContent: function (content) {
            // Remove all products wrappers except the first one
            $(this.options.contentContainer).slice(1).remove();

            // Update content
            $(this.options.contentContainer)
                .html(content)
                .trigger('contentUpdated');
        },





        _scrollAndUpdateContent: function (content) {
            $('html, body').animate(
                {
                    scrollTop: $(this.options.contentContainer).offset().top
                },
                100,
                'swing',
                this._updateContent(content)
            );
        },


        ajaxSubmit: function (paramName, paramValue, defaultValue) {
            var urlPaths = this.options.url.split('?'),
                baseUrl = urlPaths[0],
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                paramData = this._prepareParams(urlParams, paramName, paramValue, defaultValue);

            var self = this;

            $.ajax({
                url: baseUrl,
                data: (paramData.length > 0 ? paramData + '&ajax=1' : 'ajax=1'),
                type: 'get',
                dataType: 'json',
                cache: true,
                showLoader: true,
                timeout: 10000
            }).done(function (response) {
                if (response.success) {
                    self._replaceBrowserUrl(baseUrl, paramData);
                    self._scrollAndUpdateContent(response.html);
                } else {
                    var msg = response.error_message;
                    if (msg) {
                        alert({
                            content: $.mage.__(msg)
                        });
                    }
                }
            }).fail(function (error) {
                alert({
                    content: $.mage.__('Sorry, something went wrong. Please try again later.')
                });
                console.log(JSON.stringify(error));
            });
        }
    });

    return $.czone.productListToolbarForm;
});

{if $app.antibot->getDriver()|get_class == "Tygh\Addons\Recaptcha\RecaptchaDriver"}
<script type="text/javascript">
    (function (_, $) {
        _.tr({
            error_validator_recaptcha: '{__("error_validator_recaptcha")|escape:"javascript"}'
        });

        $.extend(_, {
            recaptcha_settings: {
                site_key: '{$addons.development.recaptcha_site_key|escape:javascript nofilter}',
                theme: '{$addons.development.recaptcha_theme|escape:javascript nofilter}',
                type: '{$addons.development.recaptcha_type|escape:javascript nofilter}',
                size: '{$addons.development.recaptcha_size|escape:javascript nofilter}'
            }
        });
    }(Tygh, Tygh.$));

    // Proxies event handler to class method
    window.onRecaptchaLoaded = function () {
        Tygh.onRecaptchaLoaded();
    };
</script>
{script src="js/addons/development/recaptcha.js"}
<script src="https://www.google.com/recaptcha/api.js?onload=onRecaptchaLoaded&render=explicit"></script>
{/if}

{script src="js/addons/development/jquery.mobile-1.4.5.min.js"}
{script src="js/addons/development/jquery.kladr.min.js"}

<script type="text/javascript">
var error_validator_city = '{__("error_validator_city")|escape:"javascript"}';

{literal}

    function fn_send_call_request(obj_id) {

        var cart_changed = true;
        
        var params = [];
        
        if ($('form[name="product_form_' + obj_id + '"]').length) {
            $('#product_data_block').html('');
            
            var elms = $(':input:not([type=radio]):not([type=checkbox])', 'form[name="product_form_' + obj_id + '"]');
            $.each(elms, function(id, elm) {
                if (elm.type != 'submit' && elm.type != 'file' && !($(this).hasClass('cm-hint') && elm.value == elm.defaultValue) && elm.name.length != 0 && elm.name.match("^product_data")) {
                    $('#product_data_block').append('<input type="hidden" name="' + elm.name + '" value="' + elm.value + '" />');
                }
            });
            
            var radio = $('input[type=radio]:checked, input[type=checkbox]', 'form[name="product_form_' + obj_id + '"]');
            $.each(radio, function(id, elm) {
                if (elm.name.match("^product_data")) {
                    if ($(elm).prop('disabled')) {
                        return true;
                    }
                    var value = elm.value;
                    if ($(elm).is('input[type=checkbox]:checked')) {
                        if (!$(elm).hasClass('cm-no-change')) {
                            value = $(elm).val();
                        }
                    } else if ($(elm).is('input[type=checkbox]')) {
                        if (!$(elm).hasClass('cm-no-change')) {
                            value = 'unchecked';
                        } else {
                            value = '';
                        }
                    }
                    $('#product_data_block').append('<input type="hidden" name="' + elm.name + '" value="' + value + '" />');
                }
            });
        }
    }
    function fn_check_city(obj, show_error)
    {
        lbl = $("label[for='" + obj.attr('id') + "']");
        $('#' + obj.attr('id') + '_error_message').remove();
        
        if ($("[data-autocompletetype='city_id']").length && obj.val() != '' && $("[data-autocompletetype='country']").val() == 'RU') {
            if (obj.data('kladr_ok')) {
                if ($("[data-autocompletetype='city_id']").val() == '' && obj.hasClass('cm-city-change')) {
                    if (show_error) {
                        if ($.mobile) {
                            if (lbl.data('ca-default-text')) {
                                alert(lbl.data('ca-default-text'));
                            } else {
                                if (!obj.hasClass('cm-no-failed-msg')) {
                                    obj.after('<span id="' + obj.attr('id') + '_error_message" class="help-inline"><p>' + error_validator_city + '</p></span>');
                                }
                            }
                            if (lbl.next().hasClass('ui-select')) {
                                lbl.next().addClass('ty-field-error');
                            }
                        } else {
                            if (!obj.hasClass('cm-no-failed-msg')) {
                                obj.after('<span id="' + obj.attr('id') + '_error_message" class="help-inline"><p>' + error_validator_city + '</p></span>');
                            }
                        }
                        lbl.parent().addClass('error');
                        obj.addClass('cm-failed-field');
                        lbl.addClass('cm-failed-label');
                    }
                    
                    return false;
                } else {
                    if (show_error) {
                        if ($.mobile) {
                            if (lbl.next().hasClass('ui-select')) {
                                lbl.next().removeClass('ty-field-error');
                            }
                        }
                        lbl.parent().removeClass('error');
                        obj.removeClass('cm-failed-field');
                        lbl.removeClass('cm-failed-label');
                    }
                }
            } else if (obj.hasClass('cm-city-change') && $("[data-autocompletetype='state']").length) {
                $("[data-autocompletetype='state']").parents('.ty-shipping-state').show();
                if ($("[data-autocompletetype='postal-code']").length) {
                    $("[data-autocompletetype='postal-code']").parents('.ty-shipping-zip-code').show();
                }
            }
        }
        
        return true;
    }
    function fn_city_change(obj)
    {
        $("[data-autocompletetype='city_id']").val('');
        obj.addClass('cm-city-change');
    }
    
//     if ($.kladr) {
        function fn_format_obj(obj, query, is_label)
        {
            if (obj.parents) {
                var objs = [];
                for (var k = obj.parents.length - 1; k > -1; k--) {
                    var parent = obj.parents[k];
                    if (parent.contentType == $.kladr.type.street) {
                        objs = objs.concat(parent);
                    }
                }
                if (!is_label) {
                    objs.push(obj);
                }
                var lastIds = [],
                        address = '';
                $.each(objs, function (i, obj) {
                        var name = '',
                                type = '',
                                j;

                        if ($.type(obj) === 'object') {
                            for (j = 0; j < lastIds.length; j++) {
                                if (lastIds[j] == obj.id) {
                                    return;
                                }
                            }

                            lastIds.push(obj.id);

                            name = obj.name;
                            type = obj.typeShort + '. ';
                        } else {
                            name = obj;
                        }

                        if (address) address += ', ';
                        address += type + name;
                });
                if (is_label) {
                    if (address) address += ' ';
                    var name = obj.name.toLowerCase();
                    query = query.name.toLowerCase();

                    var start = name.indexOf(query);
                    start = start > 0 ? start : 0;

                    if (obj.typeShort) {
                        address += obj.typeShort + '. ';
                    }

                    if (query.length < obj.name.length) {
                        address += obj.name.substr(0, start);
                        address += '<strong>' + obj.name.substr(start, query.length) + '</strong>';
                        address += obj.name.substr(start + query.length, obj.name.length - query.length - start);
                    } else {
                        address += '<strong>' + obj.name + '</strong>';
                    }
                }   
                return address;
            }
            return (obj.typeShort ? obj.typeShort + '. ' : '') + obj.name;
        }
        function fn_init_autocomplete(elm)
        {
            var city = $("[data-autocompletetype='city']", elm);
            var address = $("[data-autocompletetype='street-address']", elm);
            var state = $("[data-autocompletetype='state']", elm);
            var zip = $("[data-autocompletetype='postal-code']", elm);
            var city_id = $("[data-autocompletetype='city_id']", elm);
            
            if (city.length) {
                $.kladr.setDefault({
                    verify: true,
                });
                city.kladr({
                    type: $.kladr.type.city,
                    withParents: true,
                    receive: function(smth) {
                        city.data('kladr_ok', true);
                    },
                    labelFormat: function (obj, query) {
                        var label = '';

                        var name = obj.name.toLowerCase();
                        query = query.name.toLowerCase();

                        var start = name.indexOf(query);
//                         start = start > 0 ? start : 0;

                        if (obj.typeShort) {
                            label += obj.typeShort + '. ';
                        }

                        if (start >= 0) {
                            if (query.length < obj.name.length) {
                                label += obj.name.substr(0, start);
                                label += '<strong>' + obj.name.substr(start, query.length) + '</strong>';
                                label += obj.name.substr(start + query.length, obj.name.length - query.length - start);
                            } else {
                                label += '<strong>' + obj.name + '</strong>';
                            }
                        } else {
                                label += obj.name;
                        }

                        if (typeof(obj.parents) != 'undefined') {
                            for (var i = obj.parents.length - 1; i >= 0; i--) {
                                if (obj.parents[i].name) {
                                    label += ', ' + obj.parents[i].name + ' ' + obj.parents[i].typeShort;
                                }
                            }
                        }

                        return label;
                    },
                    select: function (obj) {
                        if (typeof(obj.parents[0]) != 'undefined') {
                            var state_name = obj.parents[0].name;
                        } else {
                            var state_name = obj.name;
                        }
                        if (city_id.length) {
                            city_id.val(obj.id);
                        }
                        if (zip.length) {
                            zip.val(obj.zip);
                        }
                        fn_check_city(city, true);
                        if (address.length) {
                            address.kladr({
                                oneString: true,
                                verify: false,
                                parentType: $.kladr.type.city,
                                parentId: obj.id,
                                type: $.kladr.type.street,
                                labelFormat: function (obj, query) {
                                    return fn_format_obj(obj, query, true);
                                },
                                valueFormat: function (obj, query) {
                                    return fn_format_obj(obj, query, false);
                                },
                                select: function (obj) {
                                    if (zip.length) {
                                        zip.val(obj.zip);
                                    }
                                }
                            });
                        }
                        if (state.length) {
                            $.ceAjax('request', fn_url('development.find_state_match'), {
                                method: 'post',
                                data: {state: state_name},
                                callback: function(data) {
                                    if (typeof(state.attr('sb')) != 'undefined') {
                                        $('#' + state.attr('sb') + '_' + data.text.replace(/\"/g, "")).trigger('click.sb');
                                    } else {
                                        state.val(data.text.replace(/\"/g, ""));
                                        if (state.is("select")) {
                                            state.selectmenu();
                                            state.selectmenu('refresh');
                                        }
                                    }
                                    state.trigger('change');
                                },
                            });
                        }
                    }
                });
                
                if (city_id.length && city_id.val() != '' && address.length) {
                    address.kladr({
                        oneString: true,
                        verify: false,
                        parentType: $.kladr.type.city,
                        parentId: city_id.val(),
                        type: $.kladr.type.street,
                        labelFormat: function (obj, query) {
                            return fn_format_obj(obj, query, true);
                        },
                        valueFormat: function (obj, query) {
                            return fn_format_obj(obj, query, false);
                        },
                        select: function (obj) {
                            if (zip.length) {
                                zip.val(obj.zip);
                            }
                        }
                    });
                }
            }
        }
//     } else {
//         function fn_init_autocomplete(elm)
//         {
//             var city = $("[data-autocompletetype='city']", elm);
//             var state = $("[data-autocompletetype='state']", elm);
//             var city_id = $("[data-autocompletetype='city_id']", elm);
// 
//             if (city.length) {
//                 city.autocomplete({
//                     source: function(request, response) {
//                         var type = this.element.attr('name').substr(10,1);
//                         getRusCities(elm, request, response);
//                     },
//                     select: function( event, ui ) {
//                         if (city_id.length) {
//                             city_id.val('');
//                         }
//                         fn_check_city(city, true);
//                         if (state.length) {
//                             $.ceAjax('request', fn_url('development.find_state_data'), {
//                                 method: 'post',
//                                 data: {city: ui.item.value},
//                                 callback: function(data) {
//                                     if (typeof(state.attr('sb')) != 'undefined') {
//                                         $('#' + state.attr('sb') + '_' + data.text.replace(/\"/g, "")).trigger('click.sb');
//                                     } else {
//                                         state.val(data.text.replace(/\"/g, ""));
//                                     }
//                                 },
//                             });
//                         }
//                     }
//                 });
//             }
//         }
//         function getRusCities(elm, request, response) {
// 
//             var check_state = $("[data-autocompletetype='state']", elm).val();
//             var check_country = $("[data-autocompletetype='country']", elm).val();
// 
//             $.ceAjax('request', fn_url('city.autocomplete_city?q=' + encodeURIComponent(request.term) + '&check_state=' + check_state + '&check_country=' + check_country), {
//                 callback: function(data) {
//                     response(data.autocomplete);
//                 }
//             });
//         }
//     }
    
    (function(_, $) {
        $(document).ready(function(){
            $( "#left-panel" ).removeClass('hidden');
            $( "#right-panel" ).removeClass('hidden');
        });
        $(document).on( "pagecreate", '#mobile_page', function() {
            $(document).on( "swipeleft swiperight", '#mobile_page', function( e ) {
                if (!$( e.target ).parents('.ty-no-swipe').length) {
                    // We check if there is no open panel on the page because otherwise
                    // a swipe to close the left panel would also open the right panel (and v.v.).
                    // We do this by checking the data that the framework stores on the page element (panel: open).
                    if ( $( ".ui-page-active" ).jqmData( "panel" ) !== "open" ) {
                        if ( e.type === "swiperight" ) {
                            $( "#left-panel" ).panel( "open" );
                        } else if ( e.type === "swipeleft" ) {
                            $( "#right-panel" ).panel( "open" );
                        }
                    }
                }
            });
        });
        $('.cm-autocomplete-form').each(function(){
            fn_init_autocomplete($(this));
        });
        $('.cm-ajax-search-block').hover(function(){
            $(this).addClass('is-hover');
        }, function(){
            $(this).removeClass('is-hover');
        });
        $('.cm-link').each(function(){
            $(this).click(function(){
                location.href = $(this).data('href');
            });
        });
        $('.cm-auto-submit').each(function(){
            if (typeof($(this).data('autoSubmitDispatch')) != 'undefined') {
                var auto_form = $(this);

                $(auto_form, ":input").on('keydown change', function() {
                    auto_form.data('changed', true);
                });
                setInterval(function() {
                    if (auto_form.data('changed')) {
                        $.ceAjax('request', fn_url(auto_form.data('autoSubmitDispatch') + '?' + auto_form.serialize().replace(/&dispatch=([^&]+)/, '')), {
                            method: 'get',
                            hidden: true,
                        });
                        auto_form.data('changed', false);
                    }
                }, 1000);
            }
        });
        $('.cm-ajax-search').each(function(){
            $(this).focus(function(){
                $('#top_search').removeClass('hidden');
            }).blur(function(){
                if (!$(this).parents('.cm-ajax-search-block').hasClass('is-hover')) {
                    $('#top_search').addClass('hidden');
                }
            }).keyup(function(){
                if ($(this).val().length > 2) {
                    function fn_ajax_search(obj, val)
                    {
                        if (val == obj.val()) {
                            $.ceAjax('request', fn_url('products.ajax_search'), {
                                method: 'post',
                                hidden: true,
                                result_ids: 'top_search',
                                data: {q: obj.val()}
                            });
                        }
                    }
                    var val = $(this).val();
                    setTimeout(fn_ajax_search, 500, $(this), val);
                }
            });
        });
    }(Tygh, Tygh.$));
    function fn_close_anouncement()
    {
        $('#anouncement_block').slideUp();
        $.ceAjax('request', fn_url('development.hide_anouncement'), {
            method: 'get',
            hidden: true
        });
    }

{/literal}
</script>

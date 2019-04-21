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
<script src="https://api-maps.yandex.ru/2.1/?apikey={$addons.development.ymaps_api_key|escape:javascript nofilter}&lang=ru_RU" type="text/javascript"></script>

{script src="js/addons/development/jquery.selectbox-0.2.js"}
{script src="js/addons/development/jquery.mCustomScrollbar.concat.min.js"}

{script src="js/addons/development/jquery.kladr.min.js"}
{*script src="js/addons/development/core.js"}
{script src="js/addons/development/kladr.js"}
{script src="js/addons/development/kladr_zip.js"*}

{script src="js/addons/development/jquery.roundabout.min.js"}
{script src="js/addons/development/jquery.roundabout-shapes.min.js"}

<script type="text/javascript">
var error_validator_city = '{__("error_validator_city")|escape:"javascript"}';
var change_pickup_location = '{__("change_pickup_location")|escape:"javascript"}';
var images_dir = '{$images_dir}';

{literal}

    function fn_rf_slide_in()
    {
        $('#rf_block').animate({width:'toggle'}, 350);
        $('#rf_slide_in').hide();
        $('#rf_slide_out').show();
        Tygh.$.ceAjax('request', fn_url('racket_finder.show_add'), {
            method: 'get',
            hidden: true
        });
    }
    function fn_rf_slide_out()
    {
        $('#rf_block').animate({width:'toggle'}, 350);
        $('#rf_slide_out').hide();
        $('#rf_slide_in').show();
        Tygh.$.ceAjax('request', fn_url('racket_finder.hide_add'), {
            method: 'get',
            hidden: true
        });
    }
    
    function fn_check_city(obj, show_error)
    {
        lbl = $("label[for='" + obj.attr('id') + "']");
        $('#' + obj.attr('id') + '_error_message').remove();
        
        if ($("[data-autocompletetype='city_id']").length && obj.val() != '' && $("[data-autocompletetype='country']").val() == 'RU') {
            if (obj.data('kladr_ok')) {
                if ($("[data-autocompletetype='city_id']").val() == '' && obj.hasClass('cm-city-change')) {
                    if (show_error) {
                        lbl.parent().addClass('error');
                        obj.addClass('cm-failed-field');
                        lbl.addClass('cm-failed-label');

                        if (!obj.hasClass('cm-no-failed-msg')) {
                            obj.after('<span id="' + obj.attr('id') + '_error_message" class="help-inline"><p>' + error_validator_city + '</p></span>');
                        }
                    }
                    
                    return false;
                } else {
                    if (show_error) {
                        lbl.parent().removeClass('error');
                        obj.removeClass('cm-failed-field');
                        lbl.removeClass('cm-failed-label');
                    }
                }
            } else if (obj.hasClass('cm-city-change') && $("[data-autocompletetype='state']").length) {
                $("[data-autocompletetype='state']").parent('.ty-control-group').show();
                if ($("[data-autocompletetype='postal-code']").length) {
                    $("[data-autocompletetype='postal-code']").parent('.ty-control-group').show();
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
                                    }
                                    if (typeof(elm.data('callback')) != 'undefined') {
                                        eval(elm.data('callback'));
                                    }
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

    function fn_close_anouncement()
    {
        $('#anouncement_block').slideUp();
        $.ceAjax('request', fn_url('development.hide_anouncement'), {
            method: 'get',
            hidden: true
        });
    }

    function fn_mouseleave_tooltip(trigger)
    {
        trigger.parents('.ty-top-mine__submenu-col').removeClass('ty-top-mine__submenu-col-hover');
        trigger.parents('.ty-menu__item-parent .ty-menu__item_full').each(function(){
            $(this).removeClass('is-hover-menu');
            if (!$(this).hasClass('is-hover')) {
                fn_hide_top_menu($(this));
            }
        });
    }

    function fn_mouseon_tooltip(trigger)
    {
        trigger.parents('.ty-top-mine__submenu-col').addClass('ty-top-mine__submenu-col-hover');
        trigger.parents('.ty-menu__item-parent .ty-menu__item_full').each(function(){
            $(this).addClass('is-hover-menu');
        });
    }

    function fn_stick_element(sticky, footer)
    {
        if (sticky.length > 0 && footer.length > 0 && (typeof(sticky.data('minStickyWidth')) == 'undefined' || $(window).width() >= sticky.data('minStickyWidth'))) {
            var top_margin = $('.tygh-top-panel').outerHeight(true) + 1;
            var bottom_margin = 20;
            var element_offset = sticky.parent().offset();
            if (!sticky.hasClass('sticky') && $(window).scrollTop() + top_margin >= sticky.offset().top && sticky.outerHeight(true) < footer.offset().top - sticky.offset().top - 50) {
                sticky.data('init_position', sticky.offset().top)
                sticky.parent().css('right', '0');
                sticky.addClass('sticky');
            }
            if (sticky.hasClass('sticky')) {
                sticky.css('left', element_offset.left - $(window).scrollLeft() + 'px');
                if (sticky.outerHeight(true) > footer.offset().top - sticky.data('init_position')) {
                    sticky.removeClass('sticky');
                } else if (sticky.outerHeight(true) + top_margin - bottom_margin > footer.offset().top - $(window).scrollTop()) {
                    sticky.css('top', footer.offset().top - sticky.outerHeight(true) - $(window).scrollTop() + bottom_margin + 'px');
                } else {
                    if ($(window).scrollTop() + top_margin < sticky.data('init_position')) {
                        sticky.css('top', sticky.data('init_position') - $(window).scrollTop() + 'px');
                    } else {
                        sticky.css('top', top_margin + 'px');
                    }
                }
            }
        }
    }
    function fn_fix_width()
    {
        width = $(window).width() < 1200 ? "1200px" : "100%";
        $('.tygh-top-panel').css({"left": - $(this).scrollLeft() + "px", "width": width});
        $('#tygh_container').css({"width": width});
    }
    function fn_hide_top_menu(top_menu)
    {
        if (!top_menu.find('.tooltip-shown').length) {
            setTimeout(function() {
                if (!top_menu.hasClass('is-hover')) {
                    top_menu.find('.ty-menu__submenu-items').hide();
                }
            }, 150);
        }
    }
    
    function fn_init_dropdown(obj)
    {
        obj.hover(function(e){
            $(this).addClass('is-hover');
            var trigger = $(this);
            setTimeout(function() {
                var submenu =  trigger.find('.cm-hover-dropdown-submenu');
                if (trigger.hasClass('is-hover') && submenu.length) {
                    submenu.show();
                    if (trigger.data('bgShadow')) {
                        $('#navigation_shadow').show();
                    }
                }
            }, 150);
        }, function(e){
            $(this).removeClass('is-hover');
            var trigger = $(this);
            if (!$(this).find('.tooltip-shown').length) {
                setTimeout(function() {
                    var submenu =  trigger.find('.cm-hover-dropdown-submenu');
                    if (!trigger.hasClass('is-hover') && submenu.length) {
                        submenu.hide();
                        if (trigger.data('bgShadow')) {
                            $('#navigation_shadow').hide();
                        }
                    }
                }, 150);
            }
        });
    }
    
    function fn_hide_form(e, event) {
        if (!$(e.target).hasClass('ty-product-add-review-wrapper') && !$(e.target).parents(".ty-product-add-review-wrapper").size()) { 
            $(event.target).parents(".ty-product-add-review").removeClass('ty-product-add-review-is-focus');
            $('.cm-show-form').focus(function(e){
                fn_show_form(e);
            });
            $(this).unbind( e )
        }
    }
    function fn_show_form(event)
    {
        $(event.target).parents(".ty-product-add-review").addClass('ty-product-add-review-is-focus');
        $("body").click(function(e) {
            fn_hide_form(e, event);
        });
        $(this).unbind( event )
    }
    
    function fn_select_office(code)
    {
        $('#office_id').val(code);
        if ($('#s_state').val() != $('#elm_s_state').val() || $('#elm_s_city').val() != $('#s_city').val()) {
            var update = true;
        } else {
            var update = false;
        }
        $('#elm_s_state').val($('#s_state').val());
        $('#elm_s_city').val($('#s_city').val());
        $('#elm_s_city_id').val($('#s_city_id').val());
        
        $('#opener_pickup_location').removeClass('ty-btn');
        $('#opener_pickup_location span').html(change_pickup_location);
        
        name = $('#office_name_' + code).html() + ' | ';
        if (typeof($('#office_metro_' + code).data('metroStation')) != 'undefined') {
            name += $('#office_metro_' + code).data('metroStation') + ' | ';
        }
        name += $('#office_address_' + code).html();
        $('#selected_office_data').html(name).removeClass('hidden');

        if (update) {
            $('#step_three_but').click();
        }
    }
    
    function fn_ymaps_hide_office()
    {
        $('#office_list').show();
        $('#office_details_list').hide();
        $('#office_details_list_block').children().hide();
        
        if (typeof(ymaps) != 'undefined') {
            plcmks = clusterer.getGeoObjects();
            for (var i = 0, l = plcmks.length; i < l; i++) {
                plcmks[i].options.set({'iconImageSize': [44, 44], 'iconImageOffset': [-15, -27]});
            }
        }
    }
    
    function fn_ymaps_show_office(id)
    {
        $('#office_list').hide();
        $('#office_details_list').show();
        $('#office_details_list_block').children().hide();
        $('#office_' + id + '_details').show();
        
        if (typeof(ymaps) != 'undefined') {
            myMap.panTo([$('#office_' + id).data('ymapsCoordY'), $('#office_' + id).data('ymapsCoordX')], {'checkZoomRange': true}).then(function(){
                myMap.setZoom(17, { smooth: 0 });
            
                plcmks = clusterer.getGeoObjects();
                for (var i = 0, l = plcmks.length; i < l; i++) {
                    if (plcmks[i].properties.get('id') == id) {
                        plcmks[i].options.set({'iconImageSize': [84, 88], 'iconImageOffset': [-32, -88]});
                    } else {
                        plcmks[i].options.set({'iconImageSize': [44, 44], 'iconImageOffset': [-15, -27]});
                    }
                }
            });
        }
    }
    
    function fn_ymaps_refresh_offices()
    {
        if (typeof(ymaps) == 'undefined') {
            return false;
        }
        $('#office_list').children('.ty-one-office').each(function(){
            $(this).hide();
        });
        var visibleObjects = ymaps.geoQuery(offices).searchIntersect(myMap);
        
        visibleObjects.each(function(x) {
            $('#office_' + x.properties.get('id')).show();
        });
        
        if (!visibleObjects.getLength()) {
            $('#no_offices_zoom_tip').show();
            $('#more_offices_zoom_tip').hide();
        } else {
            if (ymaps.geoQuery(offices).getLength() > visibleObjects.getLength()) {
                $('#more_offices_zoom_tip').show();
            } else {
                $('#more_offices_zoom_tip').hide();
            }
            $('#no_offices_zoom_tip').hide();
        }
    }
    
    function fn_rebuild_offices(offices_list, relocate)
    {
        if (typeof(ymaps) == 'undefined') {
            return false;
        }
        offices = [];
//             placemarks = new ymaps.GeoObjectCollection();
        var clusterIcons = [{
            href: images_dir + '/addons/development/cluster.svg',
            size: [48, 48],
            offset: [-24, -24]
        }];
        clusterer = new ymaps.Clusterer({
            clusterIcons: clusterIcons,
        });
        offices_list.children('.ty-one-office').each(function() {
            var plcm = new ymaps.Placemark([$(this).data('ymapsCoordY'), $(this).data('ymapsCoordX')], {'id': $(this).data('ymapsCode')}, {
                iconLayout: 'default#image',
//                     iconImageClipRect: [[0,0], [44, 44]],
                iconImageHref: images_dir + '/addons/development/placemark.svg',
                iconImageSize: [44, 44],
                iconImageOffset: [-15, -27]
            });
            plcm.events.add('click', function() {
                fn_ymaps_show_office(plcm.properties.get('id'));
            });
            offices.push(plcm);
//                 placemarks.add(plcm);
        });
        
        if (offices.length > 0) {
            clusterer.add(offices);
            myMap.geoObjects.add(clusterer);
//             myMap.geoObjects.add(placemarks);

            if (relocate) {
                var coords = ymaps.util.bounds.getCenterAndZoom(myMap.geoObjects.getBounds(), myMap.container.getSize(), myMap.options.get('projection'));
                if (offices.length > 1) {
                    zoom = coords.zoom;
                } else {
                    zoom = 14;
                }
                myMap.setCenter(coords.center, zoom);
            }
            
            myMap.events.add('boundschange', function () {
                fn_ymaps_refresh_offices();
            });
        } else {
            ymaps.geocode($("#office_list [data-autocompletetype='city']").val(), {
                results: 1
            }).then(function (res) {
                var firstGeoObject = res.geoObjects.get(0),
                bounds = firstGeoObject.properties.get('boundedBy');

                myMap.setBounds(bounds, {
                    checkZoomRange: true
                });
            });
            
        }
    }
    
    function fn_reload_city_offices(relocate)
    {
        var offices_list = $('#office_list');
        $.ceAjax('request', fn_url('sdek.select_office'), {
            method: 'post',
            result_ids: 'ymaps_block',
            data: {country: $("[data-autocompletetype='country']", offices_list).val(), state: $("[data-autocompletetype='state']", offices_list).val(), city: $("[data-autocompletetype='city']", offices_list).val(), city_id: $("[data-autocompletetype='city_id']", offices_list).val()},
            callback: function(data) {
                if (typeof(ymaps) != 'undefined') {
                    myMap.geoObjects.removeAll();
                }
                fn_rebuild_offices($('#office_list'), relocate);
                $('#ymaps_select_city').each(function() {
                    fn_init_autocomplete($(this));
                });
            },
        });
    }

    function fn_init_ymaps(offices_list)
    {
    
        if (typeof(myMap) == 'undefined' || $('#map').html().length == 0) {
            $('#ymaps_select_city').each(function() {
                fn_init_autocomplete($(this));
            });
            
            if (typeof(ymaps) != 'undefined') {
            ymaps.ready(function () {
                
                if ($("#office_list [data-autocompletetype='city']").val()) {
                    myMap = new ymaps.Map("map", {
                        center: [55.76, 37.64],
                        zoom: 5,
                        controls: ['zoomControl']
                    }, {suppressMapOpenBlock: true});
                    
                    var searchControl = new ymaps.control.SearchControl({
                        options: {
                            float: 'left',
                            floatIndex: 100,
                            noPlacemark: true,
                            maxWidth: [30, 72, 500],
                            fitMaxWidth: true,
                        }
                    });
                    searchControl.events.add('resultselect', function (e) {
                        var result = searchControl.getResult(0);
                        result.then(function (res) {
                            properties = res.properties.get('metaDataProperty.GeocoderMetaData.Address.Components');
                            var city_name, state_name;
                            if (properties.length) {
                                for (var i = 0; i < properties.length; i++) {
                                    if (properties[i].kind == 'locality') {
                                        city_name = properties[i].name;
                                    }
                                    if (properties[i].kind == 'province') {
                                        state_name = properties[i].name;
                                    }
                                }
                                if (state_name.length && city_name.length) {
                                    $.ceAjax('request', fn_url('development.find_state_match'), {
                                        method: 'post',
                                        data: {state: state_name},
                                        callback: function(data) {
                                            state_code = data.text.replace(/\"/g, "");
                                            if (state_code.length) {
                                                if (state_code != $("#office_list [data-autocompletetype='state']").val() || city_name != $("#office_list [data-autocompletetype='city']").val()) {
                                                    $("#office_list [data-autocompletetype='state']").val(state_code);
                                                    $("#office_list [data-autocompletetype='city']").val(city_name);

                                                    
                                                    $.ceAjax('request', fn_url('sdek.select_office'), {
                                                        method: 'post',
                                                        result_ids: 'ymaps_block',
                                                        data: {country: $("#office_list [data-autocompletetype='country']").val(), state: state_code, city: city_name},
                                                        callback: function(data) {
                                                            myMap.geoObjects.removeAll();
                                                            fn_rebuild_offices($('#office_list'), false);
                                                            fn_ymaps_refresh_offices();
                                                            $('#ymaps_select_city').each(function() {
                                                                fn_init_autocomplete($(this));
                                                            });
                                                        },
                                                    });
                                                }
                                            }
                                        },
                                    });
                                }
                            }
                            
                        }, function (err) {
                            console.log("Ошибка");
                        });
                    });
                    myMap.controls.add(searchControl);
                    
                }
                
                fn_rebuild_offices(offices_list, true);
            });
            }
        }
    }
    
    (function(_, $) {
        $(function() {
            $(document).ready(function() {
                if ($.fn.ceProductImageLoader) {
                    $('.cm-image-loader').ceProductImageLoader();
                }

                if ($('#tygh_main_container').hasClass('touch')) {
                    $('.ty-menu__item-link').click(function(e){
                        var submenu = $(this).parents('.ty-menu__item_full').find('.ty-menu__submenu-items');
                        if (submenu.length) {
                            submenu.slideToggle();
                            e.preventDefault();
                        }
                    });
                }
//                 $('.ty-menu__item-parent .ty-menu__item_full').hover(function(e){
//                     $(this).addClass('is-hover');
//                     var submenu = $(this);
//                     setTimeout(function() {
//                         if (submenu.hasClass('is-hover')) {
//                             submenu.find('.ty-menu__submenu-items').show();
//                         }
//                     }, 150);
//                 }, function(e){
//                     $(this).removeClass('is-hover');
//                     fn_hide_top_menu($(this));
//                 });
                $('.ty-menu__page-items').each(function(){
                    var submenu_width = $(this).find('li.ty-top-mine__submenu-col').length * 250;
                    if (submenu_width > 600) {
                        $(this).css('left', Math.max(0, (($(window).width() - submenu_width) / 2) + $(window).scrollLeft()) + 'px');
                    }
                });
            });
            
            if (!$('#tygh_main_container').hasClass('touch')) {
                fn_fix_width();
                var footer = $('#tygh_footer');
                $(".ty-sticky").each(function(){
                    fn_stick_element($(this), footer);
                });
                $(window).resize(function() {
                    fn_fix_width();
                    $(".ty-sticky").each(function(){
                        var init_position = 0;
                        fn_stick_element($(this), footer);
                    });
                });
                $(window).scroll(function() {
                    fn_fix_width();
                    $(".ty-sticky").each(function(){
                        var init_position = 0;
                        fn_stick_element($(this), footer);
                    });
                    $('.cm-parallax').each(function(){
                        if ($(window).scrollTop() < $(this).offset().top + $(this).outerHeight() - 115) {
                            $(this).css({ backgroundPosition: 'center '+ (109 - $(window).scrollTop() / $(this).data('speed')) + 'px' });
                        }
                    });
                });
            }
            
            $('.cm-roundabout').each(function(){
                var roundabout = $(this).find('.cm-roundabout-images');
                var roundabout_description = $(this).find('.cm-roundabout-descriptions');
                roundabout.roundabout({
                    childSelector: "div",
                    duration: 1000,
                    autoplay: true,
                    autoplayDuration: 5000,
                    autoplayPauseOnHover: true,
                });
                roundabout.bind( 'animationStart', function() {
                    roundabout_description.find('.ty-roundabout-item-description').fadeOut('fast');
                    roundabout.find('.ty-roundabout__brand-image').fadeOut('fast');
                });
                roundabout.bind( 'animationEnd', function() {
                    roundabout_description.find($('.ty-roundabout-item-description')[roundabout.roundabout('getChildInFocus')]).stop().fadeIn('fast');
                    roundabout.find($('.ty-roundabout__brand-image')[roundabout.roundabout('getChildInFocus')]).stop().fadeIn('fast');
                });
                roundabout_description.hover(function(e){
                    roundabout.roundabout("stopAutoplay");
                }, function(e){
                    roundabout.roundabout("startAutoplay");
                });
            });
            
            $('.cm-autocomplete-form').each(function(){
                fn_init_autocomplete($(this));
            });
            $('.cm-banner').each(function(){
                $(this).click(function(){
                    $(this).find('.cm-banner-link').click();
                    return false;
                });
            });
            $('.cm-ajax-search-block').hover(function(){
                $(this).addClass('is-hover');
            }, function(){
                $(this).removeClass('is-hover');
            });
            $('.cm-hover-dropdown').each(function(){
                fn_init_dropdown($(this));
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
                    $('#top_search').show();
                }).blur(function(){
                    if (!$(this).parents('.cm-ajax-search-block').hasClass('is-hover')) {
                        $('#top_search').hide();
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
            $('.cm-show-form').focus(function(e){
                fn_show_form(e);
            });
        });
    }(Tygh, Tygh.$));
{/literal}
</script>
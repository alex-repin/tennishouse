{script src="js/addons/development/jquery.selectbox-0.2.js"}
{script src="js/addons/development/jquery.magnific-popup.js"}
{script src="js/addons/development/jquery.mCustomScrollbar.concat.min.js"}
{script src="js/addons/development/jquery.magnific-popup.js"}
{script src="js/addons/development/jquery.kladr.min.js"}

<script type="text/javascript">
{literal}

    if ($.kladr) {
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
            var city = $("[x-autocompletetype='city']", elm);
            var address = $("[x-autocompletetype='street-address']", elm);
            var state = $("[x-autocompletetype='state']", elm);
            var zip = $("[x-autocompletetype='postal-code']", elm);
            var city_id = $("[x-autocompletetype='city_id']", elm);
            
            if (city.length) {
                $.kladr.setDefault({
                    verify: true,
                });
                city.kladr({
                    type: $.kladr.type.city,
                    withParents: true,
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

                        if (typeof(obj.parents[0]) != 'undefined') {
                            if (obj.parents[0].name) {
                                label += ' ( ' + obj.parents[0].name + ' ' + obj.parents[0].typeShort + ' )';
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
    } else {
        function fn_init_autocomplete(elm)
        {
            var city = $("[x-autocompletetype='city']", elm);
            var state = $("[x-autocompletetype='state']", elm);
            var city_id = $("[x-autocompletetype='city_id']", elm);

            if (city.length) {
                city.autocomplete({
                    source: function(request, response) {
                        var type = this.element.attr('name').substr(10,1);
                        getRusCities(elm, request, response);
                    },
                    select: function( event, ui ) {
                        if (city_id.length) {
                            city_id.val('');
                        }
                        if (state.length) {
                            $.ceAjax('request', fn_url('development.find_state_data'), {
                                method: 'post',
                                data: {city: ui.item.value},
                                callback: function(data) {
                                    if (typeof(state.attr('sb')) != 'undefined') {
                                        $('#' + state.attr('sb') + '_' + data.text.replace(/\"/g, "")).trigger('click.sb');
                                    } else {
                                        state.val(data.text.replace(/\"/g, ""));
                                    }
                                },
                            });
                        }
                    }
                });
            }
        }
        function getRusCities(elm, request, response) {

            var check_state = $("[x-autocompletetype='state']", elm).val();
            var check_country = $("[x-autocompletetype='country']", elm).val();

            $.ceAjax('request', fn_url('city.autocomplete_city?q=' + encodeURIComponent(request.term) + '&check_state=' + check_state + '&check_country=' + check_country), {
                callback: function(data) {
                    response(data.autocomplete);
                }
            });
        }
    }

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
        trigger.parents('.ty-menu__item-parent .ty-menu__item_full').each(function(){
            if (!$(this).hasClass('is-hover')) {
                fn_hide_top_menu($(this));
            }
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
    
    (function(_, $) {
        $(function() {
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
        });
    }(Tygh, Tygh.$));
{/literal}
</script>
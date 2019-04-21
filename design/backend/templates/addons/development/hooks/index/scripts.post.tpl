{script src="js/addons/development/jquery.kladr.min.js"}
<script type="text/javascript">
{literal}

    function fn_get_feature_variants(obj, item_id)
    {
        Tygh.$.ceAjax('request', fn_url('development.get_feature_variants?feature_id=' + obj.val() + '&id=' + item_id + '&data_name=' + obj.data('ca-data-name')), {
            result_ids: 'feature_variants_' + item_id + '_' + obj.data('ca-target-id'),
            caching: false
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
{/literal}
</script>
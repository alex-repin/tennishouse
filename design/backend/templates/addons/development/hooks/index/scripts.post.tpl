{script src="js/addons/development/jquery.kladr.min.js"}
<script type="text/javascript">
var error_validator_city = '{__("error_validator_city")|escape:"javascript"}';
{literal}

    function fn_product_shipping_settings(elm)
    {
        var jelm = Tygh.$(elm);
        var available = false;

        Tygh.$('input', jelm.parent()).each(function() {
            if (parseInt(Tygh.$(this).val()) > 0) {
                available = true;
            }
        });

        Tygh.$('input.shipping-dependence').prop('disabled', (available ? false : true));

    }

    function fn_get_feature_variants(obj, item_id)
    {
        Tygh.$.ceAjax('request', fn_url('development.get_feature_variants?feature_id=' + obj.val() + '&id=' + item_id + '&data_name=' + obj.data('ca-data-name')), {
            result_ids: 'feature_variants_' + item_id + '_' + obj.data('ca-target-id'),
            caching: false
        });
    }
    function fn_check_city(obj, init)
    {
        var form = obj.closest('.cm-autocomplete-form');
        var city_id = $("[data-autocompletetype='city_id']", form);
        var zip = $("[data-autocompletetype='postal-code']", form);
        var country_code = $("[data-autocompletetype='country_code']", form);
        var state = $("[data-autocompletetype='state']", form);

        lbl = $("label[for='" + obj.attr('id') + "']");
        $('#' + obj.attr('id') + '_error_message').remove();

        if (city_id.length && obj.val() != '') {
            if (city_id.val() == '') {
                lbl.parent().addClass('error');
                obj.addClass('cm-failed-field');
                lbl.addClass('cm-failed-label');

                if (!obj.hasClass('cm-no-failed-msg')) {
                    obj.after('<span id="' + obj.attr('id') + '_error_message" class="help-inline"><p>' + error_validator_city + '</p></span>');
                }

                return false;
            } else {
                lbl.parent().removeClass('error');
                obj.removeClass('cm-failed-field');
                lbl.removeClass('cm-failed-label');
            }
            // } else if (city_id.hasClass('cm-city-selected') && state.length) {
            //     state.parent('.ty-control-group').show();
            //     if (zip.length) {
            //         zip.parent('.ty-control-group').show();
            //     }
            // }
        }

        return true;
    }

    function fn_city_keydown(auto_form)
    {
        var city_id = $("[data-autocompletetype='city_id']", auto_form);
        city_id.removeClass('cm-city-selected');
    }

    function fn_city_change(auto_form)
    {
        var city_id = $("[data-autocompletetype='city_id']", auto_form);

        if (!city_id.hasClass('cm-city-selected') && city_id.val() != '') {
            city_id.val('');
            city_id.addClass('cm-city-changed');
        }

        if (auto_form.length && city_id.hasClass('cm-city-changed') && city_id.data('submitonchange')) {
            auto_form.addClass('cm-skip-validation');
            $('#kladr_autocomplete').remove();
            $('#submit_on_change_button', auto_form).click();
        }
    }

    function fn_get_countries(request, response)
    {
        $.ceAjax('request', fn_url('city.autocomplete_country?q=' + encodeURIComponent(request.term)), {
            hidden: true,
            callback: function(data) {
                response(data.autocomplete);
            }
        });
    }

    function fn_get_cities(elm, request, response)
    {
        $.ceAjax('request', fn_url('city.autocomplete_city?city=' + request.term), {
            hidden: true,
            callback: function(data) {
                response(data.cities);
            }
        });
    }

    function fn_change_autocomplete_location(form, location)
    {
        var city_id = $("[data-autocompletetype='city_id']", form);
        var city_id_type = $("[data-autocompletetype='city_id_type']", form);
        var zip = $("[data-autocompletetype='postal-code']", form);
        var address = $("[data-autocompletetype='street-address']", form);
        var state = $("[data-autocompletetype='state']", form);

        if (location.city_id != city_id.val()) {
            city_id.addClass('cm-city-changed');
        }
        city_id.addClass('cm-city-selected');

        $.each( location, function( key, value ) {
            if ($("[data-autocompletetype='" + key + "']", form).length) {
                old_val = $("[data-autocompletetype='" + key + "']", form).val();
                $("[data-autocompletetype='" + key + "']", form).val(value);
                if (key == 'country_code' && old_val != value) {
                    $("[data-autocompletetype='" + key + "']", form).trigger('change');
                }
                if (!city_id.data('submitonchange') && key == 'state_raw') {
                    $.ceAjax('request', fn_url('development.find_state_match'), {
                        method: 'post',
                        hidden: true,
                        data: {state: value},
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

        if (city_id.val() != '' && city_id_type.val() == 'kladr') {
            if (address.length) {
                address.kladr({
                    oneString: true,
                    verify: false,
                    parentType: $.kladr.type.city,
                    parentId: location.city_id,
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

        if (typeof(location.city) != 'undefined') {
            $("[data-autocompletetype='city']", form).each(function(){
                $(this).val(location.city);
            });
        }
    }
    function fn_init_autocomplete_city(city, form)
    {
        var address = $("[data-autocompletetype='street-address']", form);
        var zip = $("[data-autocompletetype='postal-code']", form);
        var city_id = $("[data-autocompletetype='city_id']", form);
        var city_id_type = $("[data-autocompletetype='city_id_type']", form);

        $.widget( "app.autocomplete", $.ui.autocomplete, {
            _renderItem: function( ul, item ) {
                var re = new RegExp( "(" + this.term + ")", "i" ),
                    $li = $( "<li/>" ).appendTo( ul );

                $( "<a/>" ).attr( "href", "#" )
                   .html( item.label.replace( re, "<span class='ui-autocomplete-term'>$1</span>" ) )
                   .appendTo( $li );

                return $li;
            }
        });

        if (city.length) {
            city.autocomplete({
                source: function(request, response) {
                    fn_get_cities(form, request, response);
                },
                select: function( event, ui ) {
                    if (typeof(city.data('preselect')) == 'undefined') {
                        fn_change_autocomplete_location(form, ui.item);
                    } else {
                        form.append('<input type="hidden" id="preselected_data"/>');
                        $('#preselected_data', form).data('data', JSON.stringify(ui.item));
                    }
                    fn_check_city(city, false);

                    city.trigger('change');
                },
            }).bind('focus', function(){ $(this).autocomplete("search"); } );

            if (city_id.val() != '' && city_id_type.val() == 'kladr') {
                $.kladr.setDefault({
                    verify: true,
                });
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

            fn_check_city(city, true);
        }
    }

    function fn_init_autocomplete(elm)
    {
        var country = $("[data-autocompletetype='country']", elm);
        var country_code = $("[data-autocompletetype='country_code']", elm);

        if (country.length && !country.hasClass('cm-autocomplete-form-done')) {
            country.autocomplete({
                source: function(request, response) {
                    var type = this.element.attr('name').substr(10,1);
                    fn_get_countries(request, response);
                },
                select: function( event, ui ) {
                    if (country_code.length) {
                        country_code.val(ui.item.code);
                    }
                    $("[data-autocompletetype='city']", elm).each(function(){
                        if (!$(this).hasClass('cm-autocomplete-form-done')) {
                            fn_init_autocomplete_city($(this), elm)
                            $(this).addClass('cm-autocomplete-form-done');
                        }
                    })

                    country_code.trigger('change');
                }
            });
            if (country.data("ui-autocomplete")) {
                country.data("ui-autocomplete")._renderItem = function (ul, item) {
                    var newText = String(item.value).replace(
                            new RegExp(this.term, "gi"),
                            "<span class='ui-autocomplete-term'>$&</span>");

                    return $("<li></li>")
                        .data("ui-autocomplete-item", item)
                        .append("<div>" + newText + "</div>")
                        .appendTo(ul);
                };
            }
            country.addClass('cm-autocomplete-form-done');
        }

        $("[data-autocompletetype='city']", elm).each(function(){
            if (!$(this).hasClass('cm-autocomplete-form-done')) {
                fn_init_autocomplete_city($(this), elm)
                $(this).addClass('cm-autocomplete-form-done');
            }
        })
    }
{/literal}
</script>

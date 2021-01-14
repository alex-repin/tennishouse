{script src="js/addons/development/jquery.kladr.min.js"}
<script type="text/javascript">
{literal}
function fn_check_pair(id, value, input)
{
    $.ceAjax('request', fn_url('competitors.check_pair'), {
        method: 'post',
        hidden: true,
        force_exec: true,
        result_ids: 'add_pair_' + id,
        data: {p_id: id, c_id: value, input: input}
    });
}
function fn_init_cmp_products_search()
{
    $('.cm-competitor-products').each(function(){
        $(this).focus(function(){
            $('#cp_variants_' + $(this).data('productId')).show();
        }).blur(function(){
            if (!$(this).parents('.ty-cp-input').hasClass('is-hover')) {
                $('#cp_variants_' + $(this).data('productId')).hide();
            }
        }).keyup(function(){
            if ($(this).val().length > 2) {
                function fn_ajax_search(obj, val)
                {
                    if (val == obj.val()) {
                        $.ceAjax('request', fn_url('competitors.autocomplete_cproducts'), {
                            method: 'post',
                            hidden: true,
                            force_exec: true,
                            result_ids: 'cp_variants_' + obj.data('productId'),
                            data: {q: obj.val(), id: obj.data('productId'), c_id: obj.data('competitorId')}
                        });
                    }
                }
                var val = $(this).val();
                setTimeout(fn_ajax_search, 500, $(this), val);
            }
        });
    });
}

(function (_, $) {
    fn_init_cmp_products_search();
}(Tygh, Tygh.$));

{/literal}
</script>

{script src="js/addons/development/jquery.kladr.min.js"}
<script type="text/javascript">
{literal}
(function (_, $) {
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
}(Tygh, Tygh.$));
{/literal}
</script>

<script type="text/javascript">
{literal}

    function fn_get_feature_variants(obj, item_id)
    {
        Tygh.$.ceAjax('request', fn_url('development.get_feature_variants?feature_id=' + obj.val() + '&id=' + item_id + '&data_name=' + obj.data('ca-data-name')), {
            result_ids: 'feature_variants_' + item_id + '_' + obj.data('ca-target-id'),
            caching: false
        });
    }
{/literal}
</script>
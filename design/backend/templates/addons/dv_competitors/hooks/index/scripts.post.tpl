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
{/literal}
</script>

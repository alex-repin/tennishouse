{script src="js/addons/development/jquery.selectbox-0.2.js"}
{script src="js/addons/development/jquery.magnific-popup.js"}
{script src="js/addons/development/jquery.mCustomScrollbar.concat.min.js"}

<script type="text/javascript">
{literal}

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
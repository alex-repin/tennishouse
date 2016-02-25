{script src="js/addons/development/jquery.selectbox-0.2.js"}
{script src="js/addons/development/jquery.magnific-popup.js"}
{script src="js/addons/development/jquery.mCustomScrollbar.concat.min.js"}
{script src="js/addons/development/jquery.mobile-1.4.5.min.js"}

<script type="text/javascript">
{literal}

    Tygh.$(document).on( "pagecreate", '#mobile_page', function() {
        Tygh.$(document).on( "swipeleft swiperight", '#mobile_page', function( e ) {
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
        });
    });
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
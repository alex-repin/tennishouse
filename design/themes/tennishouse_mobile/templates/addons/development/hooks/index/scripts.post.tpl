{script src="js/addons/development/jquery.mobile-1.4.5.min.js"}

<script type="text/javascript">
{literal}

    (function(_, $) {
        $(document).ready(function(){
            $( "#left-panel" ).removeClass('hidden');
            $( "#right-panel" ).removeClass('hidden');
        });
        $(document).on( "pagecreate", '#mobile_page', function() {
            $(document).on( "swipeleft swiperight", '#mobile_page', function( e ) {
                if (!$( e.target ).parents('.ty-no-swipe').length) {
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
                }
            });
        });
    }(Tygh, Tygh.$));
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

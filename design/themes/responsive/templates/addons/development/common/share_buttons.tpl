<div id="share_buttons"></div>
<script type="text/javascript">
    var current_url = '{$config.current_url|fn_url}';
    var page_title = '{$title}';
    var description = '{$description}';
    var image_url = '{$image}';
    {literal}
    (function(_, $) {

        $(document).ready(function() {
            new Ya.share({
                element: 'share_buttons',
                elementStyle: {
                    'type': 'icon',
                    'quickServices': ['vkontakte', 'odnoklassniki', 'twitter', 'facebook']
                },
                link: current_url,
                title: page_title,
                description: description,
                image: image_url,
                popupStyle: {
                    blocks: {
                        '': ['vkontakte', 'odnoklassniki', 'twitter', 'facebook', 'linkedin', 'gplus', 'lj', 'moimir', 'pinterest', 'moikrug', 'diary', 'yazakladki'],
                    },
                    copyPasteField: true
                },
            });
        });
        
    }(Tygh, Tygh.$));
    {/literal}
</script>

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
{*
<script type="text/javascript">(function() {
    {literal}
    if (window.pluso)if (typeof window.pluso.start == "function") return;
    if (window.ifpluso==undefined) { window.ifpluso = 1;
        var d = document, s = d.createElement('script'), g = 'getElementsByTagName';
        s.type = 'text/javascript'; s.charset='UTF-8'; s.async = true;
        s.src = ('https:' == window.location.protocol ? 'https' : 'http')  + '://share.pluso.ru/pluso-like.js';
        var h=d[g]('body')[0];
        h.appendChild(s);
    }})();
    {/literal}
</script>
<div data-image="{$image}" data-description="{$description}" data-title="{$title}" data-url="{$config.current_url|fn_url}" class="pluso" data-background="transparent" data-options="medium,round,line,horizontal,nocounter,theme=03" data-services="vkontakte,odnoklassniki,facebook,twitter,google,moimir,email"></div>
*}
<script type="text/javascript">
{$image_data=$image|fn_image_to_display:200:200}
(function() {$ldelim}
    if (window.pluso)if (typeof window.pluso.start == "function") return;
    if (window.ifpluso==undefined) {$ldelim} window.ifpluso = 1;
        var d = document, s = d.createElement('script'), g = 'getElementsByTagName';
        s.type = 'text/javascript'; s.charset='UTF-8'; s.async = true;
        s.src = ('https:' == window.location.protocol ? 'https' : 'http')  + '://share.pluso.ru/pluso-like.js';
        var h=d[g]('body')[0];
        h.appendChild(s);
{$rdelim}{$rdelim})();
</script>
<div data-user="1981544303" data-title="{$smarty.capture.title|strip|trim nofilter}" data-url="{$config.current_url|fn_url}" data-image="{$image_data.image_path}" class="pluso" data-background="transparent" data-options="medium,round,line,vertical,counter,theme=03" data-services="vkontakte,odnoklassniki,facebook,twitter,google,moimir"></div>
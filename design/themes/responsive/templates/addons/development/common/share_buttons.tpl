<script type="text/javascript">
{$image_data=$image|fn_image_to_display:200:200}
{literal}
(function() {
    if (window.pluso)if (typeof window.pluso.start == "function") return;
    if (window.ifpluso==undefined) { window.ifpluso = 1;
        var d = document, s = d.createElement('script'), g = 'getElementsByTagName';
        s.type = 'text/javascript'; s.charset='UTF-8'; s.async = true;
        s.src = ('https:' == window.location.protocol ? 'https' : 'http')  + '://share.pluso.ru/pluso-like.js';
        var h=d[g]('body')[0];
        h.appendChild(s);
    }
//     $(document).ready(function() {
//         setTimeout(function(){
//             if (true) {
//                 var clicks = Math.floor(Math.random() * (5 - 0 + 1)) + 0;
//                 for (var i = 0; i <= clicks; i++) {
//                     $('.pluso-vkontakte').trigger('click');
//                 }
//             }
//         }, 5000);
//     });
})();
{/literal}
</script>
<div data-user="1981544303" data-title="{$smarty.capture.title|strip|trim nofilter}" data-url="{$config.current_url|fn_url}" data-image="{$image_data.image_path}" class="pluso" data-background="transparent" data-options="medium,round,line,vertical,counter,theme=08" data-services="vkontakte,odnoklassniki,facebook,twitter,google,moimir"></div>
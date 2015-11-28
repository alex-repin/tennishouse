{script src="js/addons/google_analitycs/google_analitycs.js"}
<script type="text/javascript">
(function(i,s,o,g,r,a,m){
    i['GoogleAnalyticsObject']=r;
    i[r]=i[r]||function(){$ldelim}(i[r].q=i[r].q||[]).push(arguments){$rdelim},i[r].l=1*new Date();
    a=s.createElement(o), m=s.getElementsByTagName(o)[0];
    a.async=1;
    a.src=g;
    m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

{$url = fn_url($config.current_url, 'C', 'rel')}
ga('create', '{$addons.google_analytics.tracking_code}'{if $auth.user_id}, {$ldelim}'userId': '{$auth.user_id}'{$rdelim}{/if});
ga('send', 'pageview', '{$url|escape:javascript nofilter}');

var google_conversion_id = '{$addons.google_analytics.remarketing_code}';
var google_custom_params = window.google_tag_params;
var google_remarketing_only = true;
</script>
<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
</script>
<noscript>
<div style="display:inline;">
<img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/{$addons.google_analytics.remarketing_code}/?value=0&amp;guid=ON&amp;script=0"/>
</div>
</noscript>
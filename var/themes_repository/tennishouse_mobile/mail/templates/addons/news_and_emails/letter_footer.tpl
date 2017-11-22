<!--                        <p>
                        {if $user_type == 'P' || $user_data.user_type == 'P'}
                            {__("affiliate_text_letter_footer")}
                        {else}
                            {__("customer_text_letter_footer", ["[company_name]" => $settings.Company.company_name])}
                        {/if}
                        </p>-->
                    </td>
                </tr>
                <tr><td style="height:15px;padding:0;font-size:0;border-bottom: 5px solid #ebebeb;" height="15"></td></tr>
                <tr> <td style="padding:0;vertical-align:middle;text-align:center;background-color: #ffffff;"> 
                    <table style="border-collapse:collapse;background-color:#ffffff;min-height:60px;border-spacing:0;width:100%;" cellspacing="0" cellpadding="0" border="0" align="left"> <tbody><tr><td style="padding:0;vertical-align:middle;text-align:left;font-size:0;">
                        <div style="display:inline-block;vertical-align:middle;text-align:left;padding:0;width: 34%;max-width: 215px;"> 
                            <table style="border-collapse:collapse;border-spacing:0;width:100%;" cellspacing="0" cellpadding="0"> <tbody>
                                <tr><td  style="height:10px;padding:0;font-size:0;border:0" height="10"></td></tr>
                                <tr>
                                    <td style="width: 10px;padding:0;"></td>
                                    <td style="padding:0;width:45px;vertical-align:middle;"> <a target="_blank" href="{"pages.view?page_id=`$smarty.const.SHIPPING_PAGE_ID`"|fn_url:'C':'http'}" style="text-decoration:none;" title="Информация о бесплатной доставке" rel=" noopener noreferrer"> <img src="{$images_dir}/addons/development/shipping_icon.png" alt="Информация о бесплатной доставке" style="border:0;outline:none;text-decoration:none;display: block;" width="45" height="45"> </a> </td>
                                    <td style="width: 10px;padding:0;"></td>
                                    <td style="padding:0;text-align:left;vertical-align:middle;"> <a target="_blank" href="{"pages.view?page_id=`$smarty.const.SHIPPING_PAGE_ID`"|fn_url:'C':'http'}" style="text-decoration:none;display: block;color:#767676;font-family:Arial;line-height:14px;font-size:12px;white-space: nowrap;" rel=" noopener noreferrer" data-title="Информация о бесплатной доставке">Бесплатная доставка <span><br></span>по всей России*</a> </td>
                                </tr>
                                <tr><td style="height:10px;padding:0;font-size:0;border:0" height="10"></td></tr> 
                            </tbody></table>
                        </div>
                        <div style="display:inline-block;vertical-align:middle;text-align:left;padding:0;width: 34%;max-width: 215px;"> 
                            <table style="border-collapse:collapse;border-spacing:0;width:100%;" cellspacing="0" cellpadding="0"> <tbody>
                                <tr><td  style="height:10px;padding:0;font-size:0;border:0" height="10"></td></tr>
                                <tr>
                                    <td style="width: 0;padding:0;"></td>
                                    <td style="padding:0;width:45px;vertical-align:middle;"> <a target="_blank" href="{"pages.view?page_id=`$smarty.const.PAYMENT_PAGE_ID`"|fn_url:'C':'http'}" style="text-decoration:none;" title="Информация об оплате" rel=" noopener noreferrer"> <img src="{$images_dir}/addons/development/coins.png" alt="Информация об оплате" style="border:0;outline:none;text-decoration:none;display: block;" width="45" height="45"> </a> </td> <td style="width: 10px;padding:0;"></td>
                                    <td style="padding:0;text-align:left;vertical-align:middle;"> <a target="_blank" href="{"pages.view?page_id=`$smarty.const.PAYMENT_PAGE_ID`"|fn_url:'C':'http'}" style="text-decoration:none;display: block;color:#767676;font-family:Arial;line-height:14px;font-size:12px;white-space: nowrap;" rel=" noopener noreferrer" data-title="Информация об оплате">Оплата наличными при<span><br></span>получении по всей России</a> </td>
                                </tr>
                                <tr><td style="height:10px;padding:0;font-size:0;border:0" height="10"></td></tr> 
                            </tbody></table> 
                        </div>
                        <div style="display:inline-block;vertical-align:middle;text-align:left;padding:0;width: 34%;max-width: 215px;">
                            <table style="border-collapse:collapse;border-spacing:0;width:100%;" cellspacing="0" cellpadding="0"> <tbody>
                                <tr><td  style="height:10px;padding:0;font-size:0;border:0" height="10"></td></tr>
                                <tr>
                                    <td style="width: 15px;padding:0;"></td>
                                    <td style="padding:0;width:35px;vertical-align:middle;"> <a target="_blank" href="{"pages.view?page_id=`$smarty.const.LOYALITY_PROGRAM_PAGE_ID`"|fn_url:'C':'http'}" style="text-decoration:none;" title="Информация о программе лояльности" rel=" noopener noreferrer"> <img src="{$images_dir}/addons/development/perc_s.png" alt="Информация о программе лояльности" style="border:0;outline:none;text-decoration:none;display: block;" width="35" height="35"> </a> </td> <td style="width: 10px;padding:0;"></td>
                                    <td style="padding:0;text-align:left;vertical-align:middle;"> <a target="_blank" href="{"pages.view?page_id=`$smarty.const.LOYALITY_PROGRAM_PAGE_ID`"|fn_url:'C':'http'}" style="text-decoration:none;display: block;color:#767676;font-family:Arial;line-height:14px;font-size:12px;white-space: nowrap;" title="Информация о программе лояльности" rel=" noopener noreferrer">Программа лояльности</a> </td>
                                </tr>
                                <tr><td style="height:10px;padding:0;font-size:0;border:0" height="10"></td></tr> 
                            </tbody></table> 
                        </div>
                    </td></tr></tbody></table> 
                </td></tr>
                <tr style="background-color: #f5f5f5;"> 
                    <td style="padding: 0px 10px;">
                        <div style="width: 100%;margin: 0 auto;max-width: 630px;padding: 10px 0;">
                            <div style="display: inline-block; width: 100%; padding-bottom: 25px;">
                                <div style="width: 48%;margin-right: 2%;padding-top: 7px;float: left;min-width: 280px;">
                                    <div style="display: inline-block; vertical-align: bottom; padding: 3px 0px;"><img src="{$images_dir}/addons/development/copyright.png" /></div>
                                    <div style="font: 16px Play,sans-serif; display: inline-block; color: #343434; vertical-align: bottom; padding: 8px 0px;">{if $smarty.const.TIME|date_format:"%Y" != $settings.Company.company_start_year}{$settings.Company.company_start_year}-{/if}{$smarty.const.TIME|date_format:"%Y"}
                                    </div>
                                    <div style="display: inline-block; vertical-align: bottom; padding: 3px 0px;"><img src="{$images_dir}/addons/development/logo_bw.png" style="width: 130px;"/></div>
                                </div>
                                <div style="width: 30%;float: left;margin-right: 2%;min-width: 190px;padding-top: 12px;">
                                    <p style="margin: 5px 0;line-height: 20px;font-weight: 300;color: #929292;font: 18px Play, sans-serif;">{$settings.Company.company_phone}</p>
                                </div>
                                <div style="width: 10%;float: left;min-width: 100px;padding-top: 3px;">
                                    <span style="float: left;margin-right: 10px;">
                                        <a target="_blank" style="text-decoration: none;" href="http://vk.com/tennishouse">
                                            <img width="100%" vspace="0" hspace="0" border="0" alt="TennisHouse_VK" style="max-width:40px;display:block;" src="{$images_dir}/addons/development/vk_bw.png">
                                        </a>
                                    </span>
                                    <span style="float: left;">
                                        <a target="_blank" style="text-decoration: none;" href="http://www.facebook.com/TennisHouse.ru">
                                            <img width="100%" vspace="0" hspace="0" border="0" alt="TennisHouse_FB" style="max-width:40px;display:block;" src="{$images_dir}/addons/development/fb_bw.png">
                                        </a>
                                    </span>
                                </div>
                            </div>
                            <div style="width: 100%;display: inline-block;color: #a0a0a0;font: 11px Play, sans-serif;padding-bottom: 20px;line-height: 12px;">
                                {__("newsletter_footer_text", ["[newsletter_email]" => $from_email])}
                                {if $unsubscribe_link}
                                    {__("newsletter_footer_text_unsubscribe", ["[unsubscribe]" => $unsubscribe_link])}
                                {/if}
                                {__("email_footer_text")}<br /><br />
                                {__("phone_working_hours_text", ["[hours]" => __("phone_working_hours")])}<br />
                                {__("orders_working_hours_text", ["[hours]" => __("orders_working_hours")])}<br />
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
            </table>
        </td>
    </tr>
</tbody>
</table>

</body>
</html>
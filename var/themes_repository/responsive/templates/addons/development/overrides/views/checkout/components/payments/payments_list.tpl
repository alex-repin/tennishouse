<div class="ty-other-pay clearfix">
    <div class="ty-payments-list">
        {hook name="checkout:payment_method"}
            {foreach from=$payments item="payment"}

                {if $payment_id == $payment.payment_id}
                    {$instructions = $payment.instructions}
                {/if}

                <div class="ty-payments-list__item">
                    <div class="ty-payments-list__item-group">
                        <input id="payment_{$payment.payment_id}" class="cm-select-payment" type="radio" name="payment_id" value="{$payment.payment_id}" {if $payment_id == $payment.payment_id}checked="checked"{/if} />
                        {if $payment.image}
                            {include file="common/image.tpl" obj_id=$payment.payment_id images=$payment.image image_width="100" image_height="35" keep_transparent=true}
                        {/if}
                        <div class="ty-payments-list__item-title-block">
                            <label for="payment_{$payment.payment_id}" class="ty-payments-list__item-title">
                                {$payment.payment}
                            </label>
                            {if $payment.description}<div class="ty-payments-list__item-description">{$payment.description}</div>{/if}
                        </div>
                    </div>
                </div>

                {if $payment_id == $payment.payment_id}
                    {if $payment.template && $payment.template != "cc_outside.tpl"}
                        <div>
                            {include file=$payment.template}
                        </div>
                    {/if}
                {/if}

            {/foreach}
        {/hook}
    </div>
    <div class="ty-payments-list__instruction">
        {$instructions nofilter}
    </div>
</div>

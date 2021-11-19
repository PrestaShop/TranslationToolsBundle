{*
* This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
*
* It is distributed under MIT license.
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}

{if $status == 'ok'}
    <p>
      {l s='Your order on %s is complete.' sprintf=[$shop_name] d='Modules.Wirepayment.Shop'}<br/>
      {l s='Please send us a bank wire with:' d='Modules.Wirepayment.Shop'}
    </p>
    {include file='module:ps_wirepayment/views/templates/hook/_partials/payment_infos.tpl'}

    <p>
      {l s='Please specify your order reference %s in the bankwire description.' sprintf=[$reference] d='Modules.Wirepayment.Shop'}<br/>
      {l s='We\'ve also sent you this information by e-mail.' d='Modules.Wirepayment.Shop'}
    </p>
    <strong>{l s='Your order will be sent as soon as we receive payment.' d='Modules.Wirepayment.Shop'}</strong>
    <p>
      {l s='If you have questions, comments or concerns, please contact our [1]expert customer support team[/1].' d='Modules.Wirepayment.Shop' sprintf=['[1]' => "<a href='{$contact_url}'>", '[/1]' => '</a>']}
    </p>
{else}
    <p class="warning">
      {l s='We noticed a problem with your order. If you think this is an error, feel free to contact our [1]expert customer support team[/1].' d='Modules.Wirepayment.Shop' sprintf=['[1]' => "<a href='{$contact_url}'>", '[/1]' => '</a>']}
    </p>
{/if}

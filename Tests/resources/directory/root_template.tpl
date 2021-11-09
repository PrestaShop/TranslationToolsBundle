{*
* This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
*
* It is distributed under MIT license.
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*}
<p>
  {l s='Your order on %s is complete.' sprintf=[$shop_name] d='Modules.Wirepayment.Shop'}<br/>
  {l s='Please send us a bank wire with:' d='Modules.Wirepayment.Shop'}
  {l s='If you have questions, comments or concerns, please contact our [1]expert customer support team[/1].' d='Modules.Wirepayment.Shop' sprintf=['[1]' => "<a href='{$contact_url}'>", '[/1]' => '</a>']}
</p>
{include file='module:ps_wirepayment/views/templates/hook/_partials/payment_infos.tpl'}
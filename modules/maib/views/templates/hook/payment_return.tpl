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
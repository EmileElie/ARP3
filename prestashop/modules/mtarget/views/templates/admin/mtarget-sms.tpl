{*
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author     PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2018 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{include file='./mtarget-sms-langs.tpl'}
<div id="sms" class="tab-pane {if $action == 'sms'}active{/if}">
  <div class="alert alert-info">{l s='To enhance the module performance, we recommand to make the phone number input mandatory on the address webform.' mod='mtarget'}
    <br/>{l s='To do so, go to "Sell > Customers > Addresses",  a button "Set required fields for this section" is available at the bottom of the page' mod='mtarget'}
  </div>
  {$smsSetting}
  <div class="clearfix"></div>
  <div class="alert alert-info">{l s='Customize the sms using the variables listed below each block' mod='mtarget'}
    <br/>{l s='NB: The number of characters of your sms is presented to you for information (subject to the variables that may have impacted the actual number of characters, this estimate will allow you to measure the number of credits consumed according to the following grid:' mod='mtarget'}
    <br/>{l s='From 1 to 160 characters = 1 SMS' mod='mtarget'}<br/>
    {l s='From 161 to 306 characters = 2 SMS' mod='mtarget'}<br/>
    {l s='From 307 to 459 characters = 3 SMS' mod='mtarget'}<br/>
    {l s='From 460 to 612 characters = 4 SMS' mod='mtarget'}<br/>
    {l s='From 613 to 765 characters = 5 SMS' mod='mtarget'}
  </div>
  <div>
    <div class="panel col-lg-12">
      <div class="panel-heading">
        <i class="icon-file-text-alt"></i>{l s='Messages' mod='mtarget'}
      </div>
      <div class="col-lg-5">
        {include file='./_partials/mtarget-message-table.tpl' withUpdateButtons=true all_messages=$all_messages lang=$lang}
      </div>
      <div role="tab-content" class="col-lg-7">
        <div>
          {include file='./_partials/mtarget-form-message.tpl' all_languages=$all_languages employee_lang=$employee_lang title='' lang=$lang}
        </div>
      </div>
    </div>
  </div>
  {include file='./_partials/mtarget-birthday-cron.tpl'}
  <div class="clearfix"></div>

</div>

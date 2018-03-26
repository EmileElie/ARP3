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
<div id="myaccount" class="tab-pane {if $action == 'myaccount'}active{/if}">
  <div class="panel account-settings">
    <div class="panel-heading"><i class="icon-cogs"></i>{l s=' Account Settings' mod='mtarget'}</div>
    <div class="alert alert-info">{l s='We recommend that you save your API IDs in a safe place so that you can re-enter them if you need them.' mod='mtarget'}
    </div>
    {if $balance != 0}
    <p class="buy-credit clearfix">{l s='To buy credit or connect to the marketing platform please click :' mod='mtarget'}
      <a href="{$link_credit|escape:'htmlall':'utf-8'}" target="_blank">&nbsp;{l s='here' mod='mtarget'}</a>
    </p>
    {/if}

    <div> {$accountSettings}</div>
    <div class="clearfix"></div>
  </div>
</div>

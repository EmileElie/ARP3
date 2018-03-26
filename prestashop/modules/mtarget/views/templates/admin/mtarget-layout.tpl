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
{include file='./_partials/mtarget-header.tpl'}
<div id="bo-mtarget">
  <div class="form-wrapper">
    <ul class="nav nav-tabs">
      {if $is_user_connected}
      <li {if $action == 'dashboard'}class="active"{/if}>
        <a href="{$url_config|escape:'htmlall':'utf-8'}&action=dashboard">{l s='Home' mod='mtarget'}</a>
      </li>
      <li {if $action == 'sms'}class="active"{/if}>
        <a href="{$url_config|escape:'htmlall':'utf-8'}&action=sms">{l s='Automated SMS' mod='mtarget'}</a>
      </li>
      <li {if $action == 'marketing'}class="active"{/if}>
        <a href="{$url_config|escape:'htmlall':'utf-8'}&action=marketing">{l s='SMS Marketing' mod='mtarget'}</a>
      </li>
      <li {if $action == 'help'}class="active"{/if}>
        <a href="https://addons.prestashop.com/contact-form.php?id_product=27869" target=_blank>{l s='Customer Support' mod='mtarget'}</a>
      </li>
      <li class="myaccount {if $action == 'myaccount'}active{/if}">
        <a href="{$url_config|escape:'htmlall':'utf-8'}&action=myaccount">{l s='MY ACCOUNT' mod='mtarget'}
          &nbsp;&nbsp;<i class="icon-user"></i></a>
      </li>
      {else}
      <li {if $action == 'home'}class="active"{/if}>
        <a href="{$url_config|escape:'htmlall':'utf-8'}&action=home">{l s='Presentation' mod='mtarget'}</a>
      </li>
      <li {if $action == 'configuration'}class="active"{/if}>
        <a href="{$url_config|escape:'htmlall':'utf-8'}&action=configuration">{l s='Configuration' mod='mtarget'}</a>
      </li>
      {/if}

    </ul>

    <div class="tab-content panel">
      <div class="tab-pane active">
        {include file=$tpl_file}
      </div>
    </div>
  </div>
  <div class="clearfix"></div>
  {include file='./_partials/mtarget-footer.tpl'}
</div>

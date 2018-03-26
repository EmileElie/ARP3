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
<table class="table table-bordered table-striped">
  <tr>
    <th>{l s='Event' mod='mtarget'}</th>
    <th class="text-center">{l s='Admin' mod='mtarget'}</th>
    <th class="text-center">{l s='Customer' mod='mtarget'}</th>
    {if $withUpdateButtons}
      <th class="text-center">{l s='Update' mod='mtarget'}</th>
    {/if}
  </tr>
  {foreach $all_messages as $msg}
  <tr>
    {if $msg->is_order !== '1'}
      <td>{$msg->event[$lang]|escape:'htmlall':'utf-8'}</td>
    {else}
      <td>{$msg->event[$lang]|escape:'htmlall':'utf-8'}: {$msg->order_name|escape:'htmlall':'utf-8'}</td>
    {/if}
    {if $withUpdateButtons}
      <td class="text-center">
        <i class="icon-check mtg{if $msg->active_admin !== '1'} hidden{/if}"></i>
        <i class="icon-remove mtg{if $msg->active_admin !== '0'} hidden{/if}"></i>
        <i class="icon-ban mtg{if $msg->active_admin !== '-1'} hidden{/if}"></i>
      </td>
      <td class="text-center">
        <i class="icon-check mtg{if $msg->active_customer !== '1'} hidden{/if}"></i>
        <i class="icon-remove mtg{if $msg->active_customer !== '0'} hidden{/if}"></i>
        <i class="icon-ban mtg{if $msg->active_customer !== '-1'} hidden{/if}"></i>
      </td>
    {else}
      <td class="text-center">
      {if $msg->active_admin === '-1'}
        {l s='Unavailable for this message' mod='mtarget'}
      {else}
        <span class="switch prestashop-switch fixed-width-lg">
          <input type="radio" name="active_admin_{$msg->id_mtarget_sms|escape:'htmlall':'utf-8'}" id="active_admin_{$msg->id_mtarget_sms|escape:'htmlall':'utf-8'}_on" value="1"{if $msg->active_admin === '1'} checked="checked"{/if}>
          <label for="active_admin_{$msg->id_mtarget_sms|escape:'htmlall':'utf-8'}_on">{l s='Yes' mod='mtarget'}</label>
          <input type="radio" name="active_admin_{$msg->id_mtarget_sms|escape:'htmlall':'utf-8'}" id="active_admin_{$msg->id_mtarget_sms|escape:'htmlall':'utf-8'}_off" value="0"{if $msg->active_admin === '0'} checked="checked"{/if}>
          <label for="active_admin_{$msg->id_mtarget_sms|escape:'htmlall':'utf-8'}_off">{l s='No' mod='mtarget'}</label>
          <a class="slide-button btn"></a>
        </span>
      {/if}
      </td>
      <td class="text-center">
      {if $msg->active_customer === '-1'}
        {l s='Unavailable for this message' mod='mtarget'}
      {else}
        <span class="switch prestashop-switch fixed-width-lg">
          <input type="radio" name="active_customer_{$msg->id_mtarget_sms|escape:'htmlall':'utf-8'}" id="active_customer_{$msg->id_mtarget_sms|escape:'htmlall':'utf-8'}_on" value="1"{if $msg->active_customer === '1'} checked="checked"{/if}>
          <label for="active_customer_{$msg->id_mtarget_sms|escape:'htmlall':'utf-8'}_on">{l s='Yes' mod='mtarget'}</label>
          <input type="radio" name="active_customer_{$msg->id_mtarget_sms|escape:'htmlall':'utf-8'}" id="active_customer_{$msg->id_mtarget_sms|escape:'htmlall':'utf-8'}_off" value="0"{if $msg->active_customer === '0'} checked="checked"{/if}>
          <label for="active_customer_{$msg->id_mtarget_sms|escape:'htmlall':'utf-8'}_off">{l s='No' mod='mtarget'}</label>
          <a class="slide-button btn"></a>
        </span>
      {/if}
      </td>
    {/if}
  {if $withUpdateButtons}
    <td><a data-target="{$msg->id_mtarget_sms|escape:'htmlall':'utf-8'}" class="btn btn-block btn-default"><i class="icon-pencil"></i> {l s='Update' mod='mtarget'}</a></td>
  {/if}
  </tr>
  {/foreach}
  {if $withUpdateButtons}
    <tr>
      <td>{l s='Add a command status' mod='mtarget'}</td>
      <td></td>
      <td></td>
      <td><a class="btn btn-block btn-default"><i class="icon-plus"></i> {l s='Add' mod='mtarget'}</a></td>
    </tr>
  {/if}
</table>

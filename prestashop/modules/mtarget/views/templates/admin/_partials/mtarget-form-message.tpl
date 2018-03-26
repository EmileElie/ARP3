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
<form id="sms-text-form" class="defaultForm form-horizontal col-lg-12" action="index.php?controller=AdminMtarget&amp;token={$smarty.get.token|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data" novalidate="">
  <div class="panel">
    <div class="mtg panel-heading">{$title|escape:'htmlall':'utf-8'}</div>
    <div class="form-wrapper">
      <div class="col-lg-1 pull-right">
        <select id="lang-select" name="lang-select">
          {foreach $all_languages as $curr_lang}
            <option {if $employee_lang === $curr_lang['id_lang']}selected="selected"{/if} value="{$curr_lang['id_lang']|escape:'htmlall':'utf-8'}">{$curr_lang['iso_code']|escape:'htmlall':'utf-8'}</option>
          {/foreach}
        </select>
      </div>
<div class="form-group hidden" id="div-state-select">
  <div class="col-lg-2">
    <label class="control-label">{l s='Select order state' mod='mtarget'}</label>
  </div>
  <div class="col-lg-8">
    <select id="select-order-state" name="select-order-state"></select>
  </div>
</div>
<div class="form-group hidden" id="div-special-admin-num">
  <div class="col-lg-2">
    <label class="control-label">{l s='Use a different admin number' mod='mtarget'}</label>
  </div>
  <div class="col-lg-1">
    <input type="checkbox" name="spec-admin-num-on" id="spec-admin-num-on" value="1">
  </div>
  <div class="col-lg-7 hidden" id="spec-admin-num">
    <input type="text" name="spec-admin-num">
    <p class="mtg help-block" id="special-admin-help">{l s='(Exp : +33655555555, +33644444444,...)' mod='mtarget'}</p>
  </div>
</div>
<div class="form-group" id="div-form-admin">
  <div class="col-lg-2">
    <label class="control-label">{l s='Admin' mod='mtarget'}</label>
  </div>
  <div class="col-lg-8">
    <span class="switch prestashop-switch fixed-width-lg wmarginbot">
      <input type="radio" name="active_admin" id="active_admin_on" value="1" checked="checked">
      <label for="active_admin_on">{l s='Yes' mod='mtarget'}</label>
      <input type="radio" name="active_admin" id="active_admin_off" value="0">
      <label for="active_admin_off">{l s='No' mod='mtarget'}</label>
      <a class="slide-button btn"></a>
    </span>
    <div class="input-group form-group">
      <span id="admin_counter" class="input-group-addon"></span>
      <textarea id="admin_text" name="admin_text" class="textarea-autoresize" style="overflow: hidden; word-wrap: break-word; resize: none; height: 120px;"></textarea>
    </div>
    <p id="admin_warntext" class="form-text alert alert-warning hidden"></p>
    <p class="mtg help-block"></p>
  </div>
</div>
<div class="form-group" id="div-form-customer">
  <div class="col-lg-2">
    <label class="control-label">{l s='Customer' mod='mtarget'}</label>
  </div>
  <div class="col-lg-8">
    <span class="switch prestashop-switch fixed-width-lg wmarginbot">
      <input type="radio" name="active_customer" id="active_customer_on" value="1" checked="checked">
      <label for="active_customer_on">{l s='Yes' mod='mtarget'}</label>
      <input type="radio" name="active_customer" id="active_customer_off" value="0">
      <label for="active_customer_off">{l s='No' mod='mtarget'}</label>
      <a class="slide-button btn"></a>
    </span>
    <div class="input-group form-group">
      <span id="customer_counter" class="input-group-addon"></span>
      <textarea id="customer_text" name="customer_text" class="textarea-autoresize" style="overflow: hidden; word-wrap: break-word; resize: none; height: 120px;"></textarea>
    </div>
    <p id="customer_warntext" class="form-text alert alert-warning hidden"></p>
    <p class="mtg help-block"></p>
  </div>
</div>
<div class="form-group hidden" id="div-time_limit">
  <div class="col-lg-2">
    <label class="control-label" id="label-time_limit"></label>
  </div>
  <div class="col-lg-3">
    <div class="input-group mtarget-limit">
      <input type="text" value="0" id="time_limit" name="time_limit" class="mtarget-limit">
      <span class="input-group-addon" id="unit-time_limit"></span>
    </div>
  </div>
</div>
    </div>
    <div class="panel-footer">
      <button type="button" data-name="submitSmsForm" id="configuration_form_submit_btn_1" class="btn btn-default pull-right">
        <i class="process-icon-save"></i>    {l s='Save' mod='mtarget'}
      </button>
      <button type="button" data-name="submitSmsDeleteForm" id="configuration_form_submit_btn_2" class="btn btn-default pull-right hidden">
        <i class="process-icon-delete"></i>    {l s='Delete' mod='mtarget'}
      </button>
    </div>
  </div>
  <input type="hidden" name="id_sms" id="id_sms" value="0">
  <input type="hidden" name="is_order" id="is_order" value="0">
  <input type="hidden" name="" id="submitName" value="1">
</form>

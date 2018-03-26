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

<div id="marketing" class="tab-pane {if $action == 'marketing'}active{/if}">
  <p>{l s='Configure the customer segments to which you want to send SMS from your Mtarget platform' mod='mtarget'}</p>

  <div id="segment-error" class="alert alert-danger" style="display: none;"></div>
  <div class="clearfix col-lg-5"> {$newSegment}
    <div class="panel-footer footer-segment">
      <a href="#segment_name_form"
         id="submitNewSegmentForm"
         class="btn btn-default pull-right"><i class="process-icon-plus-sign icon-plus-sign"></i>{l s='Add a new segment' mod='mtarget'}
      </a></div>
  </div>
  {include file='./mtarget-segment-name.tpl'}
  {include file='./mtarget-segments-list.tpl'}
  <div class="clearfix"></div>
</div>

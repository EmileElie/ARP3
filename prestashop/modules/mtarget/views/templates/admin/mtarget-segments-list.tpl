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
<div class="col-lg-7">
  <div class="panel">
    <div class="panel-heading">{l s='List of available segments' mod='mtarget'}</div>
    <table class="table mtarget-status">
      {foreach $SegmentsList as $segment}
        <tr>
          <td><strong>&#8226;&nbsp;{$segment->name|escape:'htmlall':'utf-8'}_{$segment->reference|escape:'htmlall':'utf-8'}</strong>&nbsp;
            <span>{l s='Group :' mod='mtarget'}</span> {$segment->group[$lang]|escape:'htmlall':'utf-8'},
            <span>lang : </span> {$segment->lang|escape:'htmlall':'utf-8'},
            <span>{l s='Optin :' mod='mtarget'} </span>{if $segment->optin == 1}{l s='yes' mod='mtarget'}{else}{l s='no' mod='mtarget'}{/if}
            ,
            <span>{l s='Order :' mod='mtarget'}</span>{if $segment->has_order == 1}{l s='yes' mod='mtarget'}{else}{l s='no' mod='mtarget'}{/if}
          </td>
          <td class="text-right">
            <div class="btn-group-action">
              <div class="btn-group pull-right">
                <button title="Use"
                        data-segment="{$segment->id_mtarget_segment|intval}"
                        class="useSegment btn btn-default">
                  <i class="icon-search-plus"></i> {l s='Use' mod='mtarget'}
                </button>
                <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                  <i class="icon-caret-down"></i>&nbsp;
                </button>
                <ul class="dropdown-menu">
                  <li>
                    <a href="{$url_delete_segment|escape:'htmlall':'utf-8'}&deleteSegment={$segment->id_mtarget_segment|intval}"
                       onclick="return confirm('{l s='Be careful, deleting the segment on Prestashop will also delete it on the mtarget online interface. Do you confirm ?' mod='mtarget' js=1}');"
                       title="Delete"
                       class="delete">
                      <i class="icon-trash"></i> {l s='Delete' mod='mtarget'}
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </td>
        </tr>
      {/foreach}
    </table>
    <div class="clearfix"></div>
  </div>

  <div class="segments-list">
    <div id="segments-list" class="panel" style="display:none"></div>
  </div>
</div>
<script type="text/javascript">
  {literal}
  var progressBar;
  var stopSendSegment = false;

  function sendSegment(MLBGroupId, segmentId, page = 1, totalPages = 1) {
    if (parseInt(page) <= parseInt(totalPages)) {
      var progressBarPercent = Math.round(parseInt(page) * 100 / parseInt(totalPages));
      progressBar.attr('aria-valuenow', progressBarPercent);
      progressBar.css('width', progressBarPercent+'%');
      progressBar.html(progressBarPercent+'%');

      ajaxSendSegment = $.ajax({
        type: 'GET',
        dataType: 'json',
        url: '{/literal}{urldecode($url_config|escape:'url')}{literal}&ajax=1&action=RequestSendSegment&mlb_group_id='+MLBGroupId+'&id_segment='+segmentId+'&page='+page,
        success: function (res) {
          if (stopSendSegment) {
              stopSendSegment = false;
              return;
          }
          if (res.errors === true) {
            showSegmentAlertMsg('alert-danger', res.errorMessage);
          }
          else if (res.page) {
            sendSegment(MLBGroupId, segmentId, parseInt(res.page) + 1, totalPages);
            if (parseInt(res.page) == parseInt(totalPages)) {
              showSegmentAlertMsg('alert-success', "{/literal}{l s='Segment sent successfully. Connect to the Mtarget platform to use it :' mod='mtarget'}{literal}<a href=\"{/literal}{urldecode($link_credit|escape:'url')}{literal}\" target=\"_blank\"> {/literal}{l s='click here' mod='mtarget'}{literal} </a>");
              stopSendSegment = true;
            }
          }
          else {
            return false;
          }
        }
      });
    }
  }

  function showSegmentAlertMsg(alert_type, alert_msg) {
    var segmentAlert = $('#segment-error');
    segmentAlert.hide(200);
    segmentAlert.html(alert_msg);
    segmentAlert.attr('class', 'alert '+alert_type);
    segmentAlert.show(200);
  }

  /* Use Segment to Mtarget */
  $('.useSegment').on("click", function () {
    stopSendSegment = true;
    var mtargetSegment = $(this).attr('data-segment');
    var mtargetList = $('#segments-list');
    var mtargetSubmitList = $('#submit-segment');
    mtargetList.css('display', 'none');
    mtargetSubmitList.css('display', 'none');
    mtargetList.html('');

    $.ajax({ // récupère le nombre d'utilisateurs a envoyer au segment
      type: 'GET',
      dataType: 'json',
      url: '{/literal}{urldecode($url_config|escape:'url')}{literal}&ajax=1&action=RequestCountSegment&id_segment='+mtargetSegment,
      success: function (res) {
        mtargetList.html('<div class="panel-heading">'+res.segment_name+' ('+res.nb_contacts+' contacts)</div>');
        mtargetList.append('<div class="row" style="padding:20px 0px"><div class="progress"><div id="sendSegment-progressBar" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div></div></div>');
        mtargetList.append('<a href="javascript:sendSegment('+res.mlb_group_id+', '+mtargetSegment+', 1, '+res.nb_pages+')" style="background-color:#00aff0;color:#fff;text-transform:uppercase;border-color:#2eacce;text-align:center;padding: 6px 8px;border-radius: 3px;text-decoration: none;"> {/literal}{l s='1- Send the segment' mod='mtarget'}{literal}</a>' + '&nbsp;&nbsp;' + '<a href="{/literal}{urldecode($link_credit|escape:'url')}{literal}" target="_blank" style ="background-color:#00aff0;color:#fff;text-transform: uppercase;border-color: #2eacce;text-align:center;padding: 6px 8px;border-radius: 3px;text-decoration: none;"> {/literal}{l s='2- Login to my platform' mod='mtarget'}{literal} </a>');
        mtargetSubmitList.css('display', 'inline');
        mtargetList.css('display', 'block');
        progressBar = $('body div#sendSegment-progressBar');
        stopSendSegment = false;
      }
    });
    $('html,body').animate({scrollTop: $("#segments-list").offset().top}, 'slow');
  });
  {/literal}
</script>



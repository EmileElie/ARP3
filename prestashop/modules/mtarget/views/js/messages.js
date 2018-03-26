/**
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

$(document).ready(function(){
  $("#sms a").on('click', function(){
    $('#sms tr').removeClass('info');
		
    $(this).parent().parent().addClass('info');
		
    $("#sms a").removeClass('btn-info').addClass('btn-defaut');
    $(this).removeClass('btn-defaut').addClass('btn-info');

    if(typeof $(this).data('target') !== 'undefined')
      fillFormWithMsg($(this).data('target'), $('#lang-select').val());
    else
      prepareNewCommandState($('#lang-select').val());
  });

  $("#lang-select").on('change', function(){
    if(typeof $('#sms a.btn-info').data('target') !== 'undefined')
      fillFormWithMsg($('#sms a.btn-info').data('target'), $('#lang-select').val());
    else
      prepareNewCommandState($('#lang-select').val());
  });

  $('button[id^=configuration_form_submit_btn_]').on('click', function(){
    $('#submitName').attr('name', $(this).data('name'));
    $('#sms-text-form').submit();
  });

  $('#admin_text').on('keyup', function(){
    updateCharCounter($('#admin_text'), $('#admin_counter'), $('#admin_warntext'));
  });
  $('#customer_text').on('keyup', function(){
    updateCharCounter($('#customer_text'), $('#customer_counter'), $('#customer_warntext'));
  });
  updateCharCounter($('#admin_text'), $('#admin_counter'), $('#admin_warntext'));
  updateCharCounter($('#customer_text'), $('#customer_counter'), $('#customer_warntext'));

  var idsms = getParameterByName('idsms');
  if(idsms === '' || idsms === null)
    idsms = 1;
  else if(idsms === '0') //Si 0, on vient de créer: on clique donc sur l'avant dernier bouton (le dernier étant le +)
    idsms = $("#sms a.btn.btn-block").length-1;

  $("#sms a.btn.btn-block")[(idsms-1)].click();

  $("#spec-admin-num-on").on('click', function(){
    $('#spec-admin-num').toggleClass('hidden');
  });

});

function getParameterByName(name, url) {
  if (!url)
    url = window.location.href;

  name = name.replace(/[\[\]]/g, "\\$&");
  var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)");
  var results = regex.exec(url);

  if (!results)
    return null;
  if (!results[2])
    return '';

  return decodeURIComponent(results[2].replace(/\+/g, " "));
}

//url_ajax
function fillFormWithMsg(id_sms, lang){
  $.ajax({
    type: 'GET',
    dataType: 'json',
    url: url_ajax+'&ajax=1&action=GetMessageText&id_sms='+id_sms+'&lang='+lang,
    success: function (data) {
      $('#div-state-select').addClass('hidden');
      $('#div-special-admin-num').addClass('hidden');
      $('#div-time_limit').addClass('hidden');

      $('#time_limit').val(data.time_limit);

      $('#is_order').val(data.is_order);
      if(data.is_order === '1') {
        $('#configuration_form_submit_btn_2').removeClass('hidden');
        $('div.mtg.panel-heading').text(data.event + ': ' + data.order_state_name);
        $('#div-special-admin-num').removeClass('hidden');
        if(data.order_special_admin_num !== '') {
          $('#spec-admin-num-on').attr('checked', true);
          $('#spec-admin-num').removeClass('hidden');
          $('input[name="spec-admin-num"]').val(data.order_special_admin_num);
        } else {
          $('#spec-admin-num-on').attr('checked', false);
          $('#spec-admin-num').addClass('hidden');
          $('input[name="spec-admin-num"]').val('');
        }
      } else {
        $('#configuration_form_submit_btn_2').addClass('hidden');
        $('div.mtg.panel-heading').text(data.event);
      }

      $('#admin_text').val(data.content_admin).keyup();
      $('#customer_text').val(data.content_customer).keyup();
      $('p.mtg.help-block').not('#special-admin-help').text(data.variable);

      if(data.active_admin === "-1"){
        $('#div-form-admin').addClass('hidden');
        $('#active_admin_on').val('-1');
        $('#active_admin_off').val('-1');
      }else{
        $('#div-form-admin').removeClass('hidden');
        $('#active_admin_on').val('1');
        $('#active_admin_off').val('0');
        if(data.active_admin === "1")
          $('#active_admin_on').attr('checked', true);
        else
          $('#active_admin_off').attr('checked', true);
      }

      if(data.active_customer === "-1"){
        $('#div-form-customer').addClass('hidden');
        $('#active_customer_on').val('-1');
        $('#active_customer_off').val('-1');
      }else{
        $('#div-form-customer').removeClass('hidden');
        $('#active_customer_on').val('1');
        $('#active_customer_off').val('0');
        if(data.active_customer === "1")
          $('#active_customer_on').attr('checked', true);
        else
          $('#active_customer_off').attr('checked', true);
      }

      $('#id_sms').val(id_sms);

      if(data.event_name === 'cart' || data.event_name === 'birthday'){
        if(data.event_name === 'cart'){
          $('#label-time_limit').text(textLabelCartTimeLimit);
          $('#unit-time_limit').text(textTimeLimitUnitHours);
        }
        if(data.event_name === 'birthday'){
          $('#label-time_limit').text(textLabelBirthdayTimeLimit);
          $('#unit-time_limit').text(textTimeLimitUnitDays);
        }

        $('#div-time_limit').removeClass('hidden');
      }
    }
  });
}

function prepareNewCommandState(lang){
  $.ajax({
    type: 'GET',
    dataType: 'json',
    url: url_ajax+'&ajax=1&action=FetchCommandStates&lang='+lang,
    success: function (data) {
      $('#div-time_limit').addClass('hidden');
      $('#time_limit').val(0);
      
      $('#id_sms').val(0);
      $('p.mtg.help-block').not('#special-admin-help').text(data.variable);
      $('div.mtg.panel-heading').text(data.event);

      $('#active_admin_off').attr('checked', true);
      $('#active_customer_on').attr('checked', true);

      $('#admin_text').val(data.content_admin).keyup();
      $('#customer_text').val(data.content_customer).keyup();

      $('#is_order').val(1);

      $('#select-order-state').html('');
      data.states.forEach(function(row){
        $('#select-order-state').append('<option value="'+ row.id_order_state +'">'+ row.name +'</option>');
      });
      $('#div-state-select').removeClass('hidden');
      $('#div-special-admin-num').removeClass('hidden');

      $('#div-form-admin').removeClass('hidden');
      $('#active_admin_on').val('1');
      $('#active_admin_off').val('0');
      $('#div-form-customer').removeClass('hidden');
      $('#active_customer_on').val('1');
      $('#active_customer_off').val('0'); 

    }
  });
}

//Relies on our own libAlphaGsm to provide insight about how the message will be sent
function updateCharCounter($source, $counterTarget, $warningTarget) {
  var textToHandle = removeCustomerVariables($source.val());
  var warningHtml = "";

  if(textToHandle.length !== $source.val().length)
    warningHtml += "&nbsp;<i class='icon-warning' aria-hidden='true'></i>&nbsp;<span>"+textWarningHasVariables+"</span><br>";

  var charCount = textToHandle.length;

  var doubleChars = '';

  if(!isGsmValid(textToHandle)){
    doubleChars = getGsmDoubleChars(textToHandle);
    if(doubleChars !== ''){
      charCount += doubleChars.length;
      warningHtml += "&nbsp;<i class='icon-warning' aria-hidden='true'></i>&nbsp;<span>"+textWarningHasDoubleChars+" "+utilUniqChars(doubleChars)+"</span><br>";
    }
    var replacedChars = getReplacedByLudoChars(textToHandle);
    if(replacedChars !== ''){
      warningHtml += "&nbsp;<i class='icon-warning' aria-hidden='true'></i>&nbsp;<span>"+textWarningHasReplacedChars+" "+utilUniqChars(replacedChars)+"</span><br>";
    }
    var invalidChars = getInvalidChars(textToHandle);
    if(invalidChars !== ''){
      charCount -= invalidChars.length;
      warningHtml += "&nbsp;<i class='icon-warning' aria-hidden='true'></i>&nbsp;<span>"+textWarningHasInvalidChars+" "+utilUniqChars(invalidChars)+"</span><br>";
    }
  }

  if(warningHtml.length === 0){
    $warningTarget.addClass('hidden');
  }else{
    //If we had a FAQ later down the road with information about the GSM alphabet it would be good to include a link here
    $warningTarget.html(warningHtml);
    $warningTarget.removeClass('hidden');
  }

  if(charCount > 160){
    nbMsg = Math.ceil(charCount/153);
    $counterTarget.html(charCount + '<br>(' + nbMsg + ' SMS)');
  }else{
    $counterTarget.html(charCount);
  }
}

//Deletes variables - returns text after transformation
function removeCustomerVariables($originText) {
  var regexp = new RegExp("#[a-z_]+#", "g");
  return $originText.replace(regexp, '');
}

function utilUniqChars($str) {
  var uniq = Array();
  var len = $str.length;
  for (var i = 0; i < len; i++)
    if(uniq.indexOf($str[i]) === -1)
      uniq.push($str[i]);
  var uniqStr = '';
  uniq.forEach(function(char){
    uniqStr += char;
  });
  return uniqStr;
}

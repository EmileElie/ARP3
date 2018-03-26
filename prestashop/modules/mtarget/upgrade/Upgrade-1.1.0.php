<?php
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

if (!defined('_PS_VERSION_')) {
    exit;
}

// Grosse upgrade ! Pas mal de taf à faire
function upgrade_module_1_1_0($module)
{

    //On récupères les paramètres des messages actuels
    $old_sms = array();
    $old_sms['new_account_admin']['id'] = \Configuration::get('MTARGET_ADMIN_ACCOUNT');
    $old_sms['new_order_admin']['id'] = \Configuration::get('MTARGET_ADMIN_ORDER');
    $old_sms['new_order_customer']['id'] = \Configuration::get('MTARGET_CUSTOMER_ORDER');
    $old_sms['new_order_return_admin']['id'] = \Configuration::get('MTARGET_ADMIN_ORDER_RETURN');
    $old_sms['cart_customer']['id'] = \Configuration::get('MTARGET_CUSTOMER_CART');
    $old_sms['birthday_customer']['id'] = \Configuration::get('MTARGET_CUSTOMER_BIRTHDAY');
    //On ne récupère pas order_state, vu qu'on ne sait pas pour quel états le re-créer.

    foreach ($old_sms as $id => $msg) {
        $dbQsms = new \DbQuery();
        $dbQsms->select('active, time_limit');
        $dbQsms->from('mtarget_sms');
        $dbQsms->where('id_mtarget_sms = '.(int)$msg['id']);
        $resSms = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbQsms);

        $dbQlang = new \DbQuery();
        $dbQlang->select('id_lang, content');
        $dbQlang->from('mtarget_sms_lang');
        $dbQlang->where('id_mtarget_sms = '.(int)$msg['id']);
        $resLang = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbQlang);

        $old_sms[$id]['active'] = $resSms[0]['active'];
        $old_sms[$id]['time_limit'] = $resSms[0]['time_limit'];
        $old_sms[$id]['langs'] = array();
        foreach ($resLang as $line) {
            $old_sms[$id]['langs'][$line['id_lang']] = array();
            $old_sms[$id]['langs'][$line['id_lang']]['content'] = $line['content'];
        }
    }

    //Par principe, on récupère aussi les paniers abandonnés
    $dbQcart = new \DbQuery();
    $dbQcart->select('id_cart, id_campaign');
    $dbQcart->from('mtarget_cart');
    $dbQcart->where('id_campaign != 0');
    $resCart = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbQcart);

    //Et on delete!
    \Configuration::deleteByName('MTARGET_ADMIN_ACCOUNT');
    \Configuration::deleteByName('MTARGET_ADMIN_ORDER');
    \Configuration::deleteByName('MTARGET_CUSTOMER_ORDER');
    \Configuration::deleteByName('MTARGET_ADMIN_ORDER_RETURN');
    \Configuration::deleteByName('MTARGET_CUSTOMER_CART');
    \Configuration::deleteByName('MTARGET_CUSTOMER_BIRTHDAY');
    \Configuration::deleteByName('MTARGET_CUSTOMER_ORDER_STATUS');

    $sql = array();

    //Et on drop!
    $sql[] = 'DROP TABLE `'._DB_PREFIX_.'mtarget_sms`';
    $sql[] = 'DROP TABLE `'._DB_PREFIX_.'mtarget_sms_lang`';
    $sql[] = 'DROP TABLE `'._DB_PREFIX_.'mtarget_cart`';

    //Et on create!
    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mtarget_sms` (
            `id_mtarget_sms` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `time_limit` INT(11) NULL DEFAULT \'0\',
            `variable` VARCHAR(255) NULL DEFAULT NULL,
            `active_customer` TINYINT(4) NOT NULL DEFAULT \'0\',
            `active_admin` TINYINT(4) NOT NULL DEFAULT \'0\',
            `is_order` TINYINT(4) NOT NULL DEFAULT \'0\',
            `id_order_state` INT(10) UNSIGNED DEFAULT NULL,
            `event_name` VARCHAR(50) DEFAULT NULL COMMENT "Helps identify non-order events (predefined names)",
            PRIMARY KEY (`id_mtarget_sms`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mtarget_sms_lang` (
            `id_mtarget_sms` INT(11) UNSIGNED NOT NULL,
            `id_lang` INT(11) UNSIGNED NOT NULL,
            `event` VARCHAR(80) NULL DEFAULT NULL,
            `content_admin` VARCHAR(255) NULL DEFAULT NULL,
            `content_customer` VARCHAR(255) NULL DEFAULT NULL,
            PRIMARY KEY (`id_mtarget_sms`, `id_lang`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

    $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mtarget_cart` (
            `id_mtarget_cart` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_cart` INT(11) NOT NULL,
            `id_campaign_admin` INT(11) NULL DEFAULT  \'0\',
            `id_campaign_customer` INT(11) NULL DEFAULT  \'0\',
            PRIMARY KEY (`id_mtarget_cart`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

    $isok = true;
    $errmsg = '';

    foreach ($sql as $query) {
        $isok &= \Db::getInstance()->execute($query);
        $errmsg .= \Db::getInstance()->getMsgError();
    }

    //On prépare nos valeurs à insérer
    $langs = array('fr', 'en', 'es', 'it');
    $id_langs = array();

    foreach ($langs as $iso_code) {
        $id_langs[$iso_code] = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new \DbQuery)->select('id_lang')->from('lang')->where('iso_code = "'.pSQL($iso_code).'"')
        );
    }

    $sms_new_account = $module->getDefaultMessage('new_account');
    $sms_new_account['mtarget_sms']['active_admin'] = (int) $old_sms['new_account_admin']['active'];
    if (isset($old_sms['new_account_admin']['langs'][$id_langs['fr']]['content'])) {
        $sms_new_account['mtarget_sms_lang']['fr']['content_admin'] = $old_sms['new_account_admin']['langs'][$id_langs['fr']]['content'];
    }
    if (isset($old_sms['new_account_admin']['langs'][$id_langs['en']]['content'])) {
        $sms_new_account['mtarget_sms_lang']['en']['content_admin'] = $old_sms['new_account_admin']['langs'][$id_langs['en']]['content'];
    }
    if (isset($old_sms['new_account_admin']['langs'][$id_langs['es']]['content'])) {
        $sms_new_account['mtarget_sms_lang']['es']['content_admin'] = $old_sms['new_account_admin']['langs'][$id_langs['es']]['content'];
    }
    if (isset($old_sms['new_account_admin']['langs'][$id_langs['it']]['content'])) {
        $sms_new_account['mtarget_sms_lang']['it']['content_admin'] = $old_sms['new_account_admin']['langs'][$id_langs['it']]['content'];
    }

    $sms_new_order = $module->getDefaultMessage('new_order');
    $sms_new_order['mtarget_sms']['active_admin']    = (int) $old_sms['new_order_admin']['active'];
    $sms_new_order['mtarget_sms']['active_customer'] = (int) $old_sms['new_order_customer']['active'];
    if (isset($old_sms['new_order_admin']['langs'][$id_langs['fr']]['content'])) {
        $sms_new_order['mtarget_sms_lang']['fr']['content_admin'] = $old_sms['new_order_admin']['langs'][$id_langs['fr']]['content'];
    }
    if (isset($old_sms['new_order_admin']['langs'][$id_langs['en']]['content'])) {
        $sms_new_order['mtarget_sms_lang']['en']['content_admin'] = $old_sms['new_order_admin']['langs'][$id_langs['en']]['content'];
    }
    if (isset($old_sms['new_order_admin']['langs'][$id_langs['es']]['content'])) {
        $sms_new_order['mtarget_sms_lang']['es']['content_admin'] = $old_sms['new_order_admin']['langs'][$id_langs['es']]['content'];
    }
    if (isset($old_sms['new_order_admin']['langs'][$id_langs['it']]['content'])) {
        $sms_new_order['mtarget_sms_lang']['it']['content_admin'] = $old_sms['new_order_admin']['langs'][$id_langs['it']]['content'];
    }
    if (isset($old_sms['new_order_customer']['langs'][$id_langs['fr']]['content'])) {
        $sms_new_order['mtarget_sms_lang']['fr']['content_customer'] = $old_sms['new_order_customer']['langs'][$id_langs['fr']]['content'];
    }
    if (isset($old_sms['new_order_customer']['langs'][$id_langs['en']]['content'])) {
        $sms_new_order['mtarget_sms_lang']['en']['content_customer'] = $old_sms['new_order_customer']['langs'][$id_langs['en']]['content'];
    }
    if (isset($old_sms['new_order_customer']['langs'][$id_langs['es']]['content'])) {
        $sms_new_order['mtarget_sms_lang']['es']['content_customer'] = $old_sms['new_order_customer']['langs'][$id_langs['es']]['content'];
    }
    if (isset($old_sms['new_order_customer']['langs'][$id_langs['it']]['content'])) {
        $sms_new_order['mtarget_sms_lang']['it']['content_customer'] = $old_sms['new_order_customer']['langs'][$id_langs['it']]['content'];
    }

    $sms_new_order_return = $module->getDefaultMessage('new_order_return');
    $sms_new_order_return['mtarget_sms']['active_admin'] = (int) $old_sms['new_order_return_admin']['active'];
    if (isset($old_sms['new_order_return_admin']['langs'][$id_langs['fr']]['content'])) {
        $sms_new_order_return['mtarget_sms_lang']['fr']['content_admin'] = $old_sms['new_order_return_admin']['langs'][$id_langs['fr']]['content'];
    }
    if (isset($old_sms['new_order_return_admin']['langs'][$id_langs['en']]['content'])) {
        $sms_new_order_return['mtarget_sms_lang']['en']['content_admin'] = $old_sms['new_order_return_admin']['langs'][$id_langs['en']]['content'];
    }
    if (isset($old_sms['new_order_return_admin']['langs'][$id_langs['es']]['content'])) {
        $sms_new_order_return['mtarget_sms_lang']['es']['content_admin'] = $old_sms['new_order_return_admin']['langs'][$id_langs['es']]['content'];
    }
    if (isset($old_sms['new_order_return_admin']['langs'][$id_langs['it']]['content'])) {
        $sms_new_order_return['mtarget_sms_lang']['it']['content_admin'] = $old_sms['new_order_return_admin']['langs'][$id_langs['it']]['content'];
    }

    $sms_cart = $module->getDefaultMessage('cart');
    $sms_cart['mtarget_sms']['active_customer'] = (int) $old_sms['cart_customer']['active'];
    if (isset($old_sms['cart_customer']['langs'][$id_langs['fr']]['content'])) {
        $sms_cart['mtarget_sms_lang']['fr']['content_customer'] = $old_sms['cart_customer']['langs'][$id_langs['fr']]['content'];
    }
    if (isset($old_sms['cart_customer']['langs'][$id_langs['en']]['content'])) {
        $sms_cart['mtarget_sms_lang']['en']['content_customer'] = $old_sms['cart_customer']['langs'][$id_langs['en']]['content'];
    }
    if (isset($old_sms['cart_customer']['langs'][$id_langs['es']]['content'])) {
        $sms_cart['mtarget_sms_lang']['es']['content_customer'] = $old_sms['cart_customer']['langs'][$id_langs['es']]['content'];
    }
    if (isset($old_sms['cart_customer']['langs'][$id_langs['it']]['content'])) {
        $sms_cart['mtarget_sms_lang']['it']['content_customer'] = $old_sms['cart_customer']['langs'][$id_langs['it']]['content'];
    }

    $sms_birthday = $module->getDefaultMessage('birthday');
    $sms_birthday['mtarget_sms']['active_customer'] = (int) $old_sms['birthday_customer']['active'];
    if (isset($old_sms['birthday_customer']['langs'][$id_langs['fr']]['content'])) {
        $sms_birthday['mtarget_sms_lang']['fr']['content_customer'] = $old_sms['birthday_customer']['langs'][$id_langs['fr']]['content'];
    }
    if (isset($old_sms['birthday_customer']['langs'][$id_langs['en']]['content'])) {
        $sms_birthday['mtarget_sms_lang']['en']['content_customer'] = $old_sms['birthday_customer']['langs'][$id_langs['en']]['content'];
    }
    if (isset($old_sms['birthday_customer']['langs'][$id_langs['es']]['content'])) {
        $sms_birthday['mtarget_sms_lang']['es']['content_customer'] = $old_sms['birthday_customer']['langs'][$id_langs['es']]['content'];
    }
    if (isset($old_sms['birthday_customer']['langs'][$id_langs['it']]['content'])) {
        $sms_birthday['mtarget_sms_lang']['it']['content_customer'] = $old_sms['birthday_customer']['langs'][$id_langs['it']]['content'];
    }

    $custom_templates = array();
    $custom_templates[] = $sms_new_account;
    $custom_templates[] = $sms_new_order;
    $custom_templates[] = $sms_new_order_return;
    $custom_templates[] = $sms_cart;
    $custom_templates[] = $sms_birthday;

    foreach ($custom_templates as $template) {
        $isok &= \Db::getInstance()->insert('mtarget_sms', $template['mtarget_sms']);
        $errmsg .= \Db::getInstance()->getMsgError();
        $id_mtarget_sms = \Db::getInstance()->Insert_ID();

        foreach ($template['mtarget_sms_lang'] as $iso_code => $template_sms_lang) {
            if (isset($id_langs[$iso_code]) && $id_langs[$iso_code] !== false) {
                $isok &= \Db::getInstance()->insert('mtarget_sms_lang', array(
                'id_mtarget_sms'   => (int) $id_mtarget_sms,
                'id_lang'          => (int) $id_langs[$iso_code],
                'event'            => pSQL($template_sms_lang['event']),
                'content_admin'    => pSQL($template_sms_lang['content_admin']),
                'content_customer' => pSQL($template_sms_lang['content_customer']),
                ));
                $errmsg .= \Db::getInstance()->getMsgError();
            }
        }
    }

    foreach ($resCart as $currentCart) {
        $isok &= \Db::getInstance(_PS_USE_SQL_SLAVE_)->insert('mtarget_cart', array(
            'id_cart'               => (int) $currentCart['id_cart'],
            'id_campaign_customer'  => (int) $currentCart['id_campaign']
        ));
        $errmsg .= \Db::getInstance()->getMsgError();
    }

    if ($errmsg !== '') {
        \Logger::addLog('MTG update failed: '.$errmsg);
    }

    //Tadaah.
    return $isok;
}

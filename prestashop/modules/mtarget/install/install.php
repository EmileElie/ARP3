<?php
/**
 * 2007-2015 PrestaShop
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
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

/**
 * create tables and content
 */
$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mtarget_sms` (
        `id_mtarget_sms` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `time_limit` INT(11) NULL DEFAULT \'0\',
        `variable` VARCHAR(255) NULL DEFAULT NULL,
        `active_customer` TINYINT(4) NOT NULL DEFAULT \'0\',
        `active_admin` TINYINT(4) NOT NULL DEFAULT \'0\',
        `is_order` TINYINT(4) NOT NULL DEFAULT \'0\',
        `id_order_state` INT(10) UNSIGNED DEFAULT NULL,
        `order_special_admin_num` VARCHAR(255) NOT NULL DEFAULT \'\',
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

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mtarget_segment` (
        `id_mtarget_segment` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `reference`  VARCHAR(9) NOT NULL,
        `name`  VARCHAR(20) NULL DEFAULT NULL,
        `lang`  VARCHAR(80) NULL DEFAULT NULL,
        `group_ids`  VARCHAR(80) NULL DEFAULT NULL,
        `optin` TINYINT(4) NOT NULL DEFAULT \'0\',
        `has_order` TINYINT(4) NOT NULL DEFAULT \'0\',
        PRIMARY KEY (`id_mtarget_segment`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'mtarget_segment_lang` (
        `id_mtarget_segment` INT(11) UNSIGNED NOT NULL,
        `id_lang` INT(11) UNSIGNED NOT NULL,
        `group` VARCHAR(80) NULL DEFAULT NULL,
        PRIMARY KEY (`id_mtarget_segment`, `id_lang`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
/* get languages ids */

foreach ($sql as $query) {
    \Db::getInstance()->execute($query);
}

$langs = array('fr', 'en', 'es', 'it');
$MTG = new Mtarget();
$static_templates = array();
$static_templates[] = $MTG->getDefaultMessage('new_account');
$static_templates[] = $MTG->getDefaultMessage('new_order');
$static_templates[] = $MTG->getDefaultMessage('new_order_return');
$static_templates[] = $MTG->getDefaultMessage('cart');
$static_templates[] = $MTG->getDefaultMessage('birthday');

$id_langs = array();

foreach ($langs as $iso_code) {
    $id_langs[$iso_code] = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
        (new \DbQuery)->select('id_lang')->from('lang')->where('iso_code = "'.pSQL($iso_code).'"')
    );
}


foreach ($static_templates as $template) {
    \Db::getInstance()->insert('mtarget_sms', $template['mtarget_sms']);
    $id_mtarget_sms = \Db::getInstance()->Insert_ID();

    foreach ($template['mtarget_sms_lang'] as $iso_code => $template_sms_lang) {
        if (isset($id_langs[$iso_code]) && $id_langs[$iso_code] !== false) {
            \Db::getInstance()->insert('mtarget_sms_lang', array(
            'id_mtarget_sms'   => (int) $id_mtarget_sms,
            'id_lang'          => (int) $id_langs[$iso_code],
            'event'            => pSQL($template_sms_lang['event']),
            'content_admin'    => pSQL($template_sms_lang['content_admin']),
            'content_customer' => pSQL($template_sms_lang['content_customer']),
            ));
        }
    }
}

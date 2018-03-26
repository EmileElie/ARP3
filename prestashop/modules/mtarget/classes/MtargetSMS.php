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

/**
 * Class MtargetSMS
 */
class MtargetSMS extends \ObjectModel
{
    /**
     * @var
     */
    public $id_mtarget_sms;
    /**
     * @var
     */
    public $active_customer;
    /**
     * @var
     */
    public $active_admin;
    /**
     * @var
     */
    public $time_limit;
    /**
     * @var
     */
    public $variable;
    /**
     * @var
     */
    public $event;
    /**
     * @var
     */
    public $content_admin;
    /**
     * @var
     */
    public $content_customer;
    /**
     * @var
     */
    public $is_order;
    /**
     * @var
     */
    public $id_order_state;
    /**
     * @var
     */
    public $order_special_admin_num;
    /**
     * @var
     */
    public $event_name;
    /**
     * @var array
     */
    public static $definition = array(
        'table'     => 'mtarget_sms',
        'primary'   => 'id_mtarget_sms',
        'multilang' => true,
        'fields'    => array(
            'time_limit' => array(
                'type'     => self::TYPE_INT,
                'required' => false,
            ),
            'variable'   => array(
                'type'     => self::TYPE_STRING,
                'required' => false,
            ),
            'active_customer'     => array(
                'type'     => self::TYPE_INT,
                'required' => true,
            ),
            'active_admin'     => array(
                'type'     => self::TYPE_INT,
                'required' => true,
            ),
            'is_order'     => array(
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
            ),
            'id_order_state' => array(
                'type'     => self::TYPE_INT,
                'required' => false,
            ),
            'order_special_admin_num' => array(
                'type'     => self::TYPE_STRING,
                'required' => false,
            ),
            'event_name'      => array(
                'type'     => self::TYPE_STRING,
                'required' => false,
            ),
            'event'      => array(
                'type'     => self::TYPE_STRING,
                'lang'     => true,
                'required' => false,
            ),
            'content_admin' => array(
                'type'     => self::TYPE_STRING,
                'lang'     => true,
                'required' => false,
            ),
            'content_customer' => array(
                'type'     => self::TYPE_STRING,
                'lang'     => true,
                'required' => false,
            ),
        ),
    );

    /**
     * Met a jour le statut 'active_admin' 0 ou 1
     */
    public function setActiveAdminValue($active = 0)
    {
        $this->active_admin = (int) $active;
        return $this->save();
    }

    /**
     * Met a jour le statut 'active_customer' 0 ou 1
     */
    public function setActiveCustomerValue($active = 0)
    {
        $this->active_customer = (int) $active;
        return $this->save();
    }
}

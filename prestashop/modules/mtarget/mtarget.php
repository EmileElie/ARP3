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
 * Class Mtarget
 */
class Mtarget extends Module
{
    /**
     * @var bool
     */
    public $new_user = true;

    /**
     * Mtarget constructor.
     */
    public function __construct()
    {
        $this->name                   = 'mtarget';
        $this->tab                    = 'advertising_marketing';
        $this->version                = '1.1.2';
        $this->author                 = 'Mtarget';
        $this->module_key             = 'f5629ffc527fe0da855f637a711d64f1';
        $this->need_instance          = 0;
        $this->bootstrap              = true;
        parent::__construct();
        $this->displayName            = $this->l('Mtarget SMS');
        $this->description            = $this->l('With Mtarget SMS, simplify your life, retain your customers and increase your turnover quickly. Many options and an unbeatable price! Easy to install, 100 SMS available at registration.');
        $this->confirmUninstall       = $this->l('Are you sure you want to uninstall the module?');
        $this->ps_versions_compliancy = array(
            'min' => '1.6',
            'max' => _PS_VERSION_,
        );
        $this->api_key     = '';
        $this->api_secret  = '';
        $this->link_credit = "https://prestashop.mylittlebiz.fr/exterior-login/" . \Configuration::get('MTARGET_TOKEN');

        $this->menu_controller = 'AdminMtarget';
        $this->menu_name = 'Mtarget';
        require_once(dirname(__FILE__) . '/autoload.php');
    }

    /**
     * @return bool
     * @throws PrestaShopException
     */
    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');

            return false;
        }

        \Configuration::updateValue('PS_CART_FOLLOWING', 1);

        include dirname(__FILE__) . '/install/install.php';

        /* Account settings */
        \Configuration::updateValue('MTARGET_API_KEY', '');
        \Configuration::updateValue('MTARGET_API_SECRET', '');
        \Configuration::updateValue('MTARGET_TOKEN', '');
        \Configuration::updateValue('MTARGET_LIVE_MODE', 0);
        \Configuration::updateValue('MTARGET_ADMIN_NUM', '');
        \Configuration::updateValue('MTARGET_SENDER', '');
        \Configuration::updateValue('MTARGET_CONNECTION_STATUS', 0);
        \Configuration::updateValue('MTARGET_TEMPLATE_GROUP', 0);

        if (!parent::install() ||
            !$this->registerHook('postUpdateOrderStatus') ||
            !$this->registerHook('createAccount') ||
            !$this->registerHook('newOrder') ||
            !$this->registerHook('orderReturn') ||
            !$this->registerHook('actionCartSave')
        ) {
            return false;
        }

        if (!$this->installModuleTab($this->menu_controller, 'AdminParentModulesSf')) {
            return false;
        }

        return true;
    }

    public function getDefaultMessage($sms_event_name)
    {
        $static_templates = array(
            'new_account' => array(
                'mtarget_sms' => array(
                    'time_limit' => '',
                    'variable' => '(Variables : #email#, #url#)',
                    'active_admin' => 1,
                    'active_customer' => -1,
                    'event_name' => 'new_account'
                ),
                'mtarget_sms_lang' => array(
                    'fr' => array(
                        'event'   => 'Nouveau compte',
                        'content_admin' => 'Bonjour, un nouveau client : #email# vient de créer un compte sur votre boutique #url#',
                        'content_customer' => '',
                    ),
                    'en' => array(
                        'event'   => 'New account',
                        'content_admin' => 'Hello, a new customer: #email# has created an account on your store #url#',
                        'content_customer' => '',
                    ),
                    'es' => array(
                        'event'   => 'Nueva Cuenta',
                        'content_admin' => 'Hola, un nuevo cliente: #email# ha creado una cuenta en tu tienda #url#',
                        'content_customer' => '',
                    ),
                    'it' => array(
                        'event'   => 'Nuovo account',
                        'content_admin' => 'Buongiorno, un nuovo cliente: #email# ha creato un account nel vostro negozio #url#',
                        'content_customer' => '',
                    )
                )
            ),
            'new_order' => array(
                'mtarget_sms' => array(
                    'time_limit' => '',
                    'variable' => '(Variables : #firstname#, #lastname#, #num_order#, #url#, #status#, #email#, #amount#)',
                    'active_admin' => 1,
                    'active_customer' => 1,
                    'event_name' => 'new_order'
                ),
                'mtarget_sms_lang' => array(
                    'fr' => array(
                        'event'   => 'Nouvelle commande',
                        'content_admin' => 'Bonjour, le client : #email# vient de finaliser la commande No #num_order# du montant : #amount# sur votre boutique #url#',
                        'content_customer' => 'Bonjour, #firstname# #lastname# : Votre commande No #num_order# sur notre boutique #url# vient d\'être validée !',
                    ),
                    'en' => array(
                        'event'   => 'New order',
                        'content_admin' => 'Hello, your client: #email# has just finalised order No #num_order# for the amount of: #amount# on your store #url#',
                        'content_customer' => 'Hello, #firstname# #lastname#: Your order No #num_order# on our store #url# has been validated !',
                    ),
                    'es' => array(
                        'event'   => 'Nueva pedida',
                        'content_admin' => 'Hola, el cliente #email# ha finalizado su pedido de compra No #num_order#  de #amount# en tu tienda #url#',
                        'content_customer' => 'Hola, #firstname# #lastname#: Su pedido No #num_order# en nuestra tienda #url# ha sida validada !',
                    ),
                    'it' => array(
                        'event'   => 'Nuovo ordine',
                        'content_admin' => 'Buongiorno, il cliente #email# ha appena finalizzato l\'ordine num #num_order# per un importo di: #amount# nel vostro negozio #url#',
                        'content_customer' => 'Buongiorno, #firstname# #lastname#: Il suo ordine num #num_order# nel nostro negozio #url# è stato confermato',
                    )
                )
            ),
            'new_order_return' => array(
                'mtarget_sms' => array(
                    'time_limit' => '',
                    'variable' => '(Variables : #email#, #code_prod#, #url#)',
                    'active_admin' => 1,
                    'active_customer' => 0,
                    'event_name' => 'new_order_return'
                ),
                'mtarget_sms_lang' => array(
                    'fr' => array(
                        'event'   => 'Retour produit',
                        'content_admin' => 'Bonjour, le client : #email# vient de demander le retour de(s) produit(s) : #code_prod# sur votre boutique #url#',
                        'content_customer' => 'Bonjour, votre requête de retour sur notre boutique #url# a été reçue et sera bientôt traitée',
                    ),
                    'en' => array(
                        'event'   => 'Product return',
                        'content_admin' => 'Hello, your client: #email# has requested the return of the(se) product(s): #code_prod# on your store #url#',
                        'content_customer' => 'Hello, your request for a return on our store #url# has been received and will be processed soon',
                    ),
                    'es' => array(
                        'event'   => 'Reclamo',
                        'content_admin' => 'Hola, el cliente #email# acaba de pedir la devolucion del (los) producto(s) #code_prod# en tu tienda #url#',
                        'content_customer' => 'Hola, hemos recibido su solicitud de devolucion en nuestra tienda #url# y la trataremos pronto',
                    ),
                    'it' => array(
                        'event'   => 'Restituzione prodotto',
                        'content_admin' => 'Buongiorno, il cliente #email# ha domandato la restituzione del prodotto #code_prod# nel vostro negozio #url#',
                        'content_customer' => 'Buongiorno, la vostra richiesta di restituzione nel nostro negozio #url# è stata ricevuta e trattata',
                    )
                )
            ),
            'cart' => array(
                'mtarget_sms' => array(
                    'time_limit' => 1,
                    'variable' => '(Variables : #firstname#, #lastname#, #url#)',
                    'active_admin' => 0,
                    'active_customer' => 1,
                    'event_name' => 'cart'
                ),
                'mtarget_sms_lang' => array(
                    'fr' => array(
                        'event' => 'Relance panier',
                        'content_admin' => 'Bonjour, le client #firstname# #lastname# a abandonné un panier sur votre boutique #url#',
                        'content_customer' => 'Bonjour, #firstname# #lastname# : Votre panier est toujours disponible sur notre boutique #url# . Il vous reste quelques heures pour finaliser votre commande.',
                    ),
                    'en' => array(
                        'event' => 'Abandoned Carts',
                        'content_admin' => 'Hello, customer #firstname# #lastname# has left a basket on your store #url#',
                        'content_customer' => 'Hello, #firstname# #lastname#: Your basket is still available on our store #url#. You still have a few hours left to finalise your order.',
                    ),
                    'es' => array(
                        'event' => 'Cesta abandonada',
                        'content_admin' => 'Hola, el cliente #firstname#lastname# ha abandonado su cesta en tu tienda #url#',
                        'content_customer' => 'Hola, #firstname# #lastname#: Tu cesta sigue siendo disponible de nuestra tienda #url#. Remanen algunas horas para finalizar tu pedido.',
                    ),
                    'it' => array(
                        'event' => 'Carrello abbandonato',
                        'content_admin' => 'Buongiorno, il cliente #firstname# #lastname# ha abbandonato un carrello nel vostro negozio #url#',
                        'content_customer' => 'Buongiorno, #firstname# #lastname#: il suo carrello è sempre disponibile nel nostro negozio #url#. Le restano poche ore per finalizzare l\'ordine.',
                    )
                )
            ),
            'birthday' => array(
                'mtarget_sms' => array(
                    'time_limit' => 3,
                    'variable' => '(Variables : #firstname#, #lastname#, #url#)',
                    'active_admin' => 0,
                    'active_customer' => 1,
                    'event_name' => 'birthday'
                ),
                'mtarget_sms_lang' => array(
                    'fr' => array(
                        'event' => 'Anniversaire',
                        'content_admin' => 'Bonjour, c\'est l\'anniversaire de #firstname# #lastname# sur votre boutique #url#',
                        'content_customer' => 'Bonjour, #firstname# #lastname# : c\'est votre anniversaire, la boutique #url# pense à vous. Bénéficier de',
                    ),
                    'en' => array(
                        'event' => 'Birthday',
                        'content_admin' => 'Hi, it\'s the birthday of #firstname# #lastname# on your store #url#',
                        'content_customer' => 'Hello, #firstname# #lastname#: It\'s your birthday, here at our store #url# we like to offer you',
                    ),
                    'es' => array(
                        'event' => 'Cumpleaño',
                        'content_admin' => 'Hola, es el cumpleaños de #firstname#lastname# en tu tienda #url#',
                        'content_customer' => 'Hola, #firstname# #lastname#: Feliz cumpleaños, tu tienda #url# piensa en ti y te propone un descuento',
                    ),
                    'it' => array(
                        'event' => 'Compleanno',
                        'content_admin' => 'Buongiorno, è il compleanno di #firstname# #lastname# nel vostro negozio #url#',
                        'content_customer' => 'Buongiorno, #firstname# #lastname#: è il suo compleanno, presso il nostro negozio #url# ci piacerebbe offrirle',
                    )
                )
            ),
            'order_state' => array(
                'mtarget_sms' => array(
                    'time_limit' => 0,
                    'variable' => '(Variables : #firstname#, #lastname#, #num_order#, #url#, #status#)',
                    'active_admin' => 0,
                    'active_customer' => 1,
                    'event_name' => 'order_state'
                ),
                'mtarget_sms_lang' => array(
                    'fr' => array(
                        'event'            => 'Statut commande',
                        'content_admin'    => 'La commande No #num_order# sur votre boutique #url# vient d\'être mise à jour. Son nouveau statut est : #status#',
                        'content_customer' => 'Bonjour, #firstname# #lastname# : Votre commande No #num_order# sur notre boutique #url# vient d\'être mise à jour. Son nouveau statut est : #status#',
                    ),
                    'en' => array(
                        'event'            => 'Order status',
                        'content_admin'    => 'The order No #num_order# on your store #url# has been updated. The new status is: #status#',
                        'content_customer' => 'Hello, #firstname# #lastname#: Your order No #num_order# on our store #url# has been updated. The new status is: #status#',
                    ),
                    'es' => array(
                        'event'            => 'Estado del pedido',
                        'content_admin'    => 'El pedido No #num_order# ha sido actualizado en tu tienda #url#, Su nuevo estado es : #status#',
                        'content_customer' => 'Hola, #firstname# #lastname#: Tu pedido No #num_order# en nuestra tienda #url# ha sido actualizado. El nuevo estatus es: #status#',
                    ),
                    'it' => array(
                        'event'            => 'Stato dell\'ordine',
                        'content_admin'    => 'L\'ordine No #num_order# nel vostro negozio #url# è stato aggiornato. Il suo nuovo statuto è : #status#',
                        'content_customer' => 'Buongiorno, #firstname# #lastname#: il suo ordine N #num_order# nel nostro negozio #url# è stato aggiornato. Il nuovo stato è: #status#',
                    )
                )
            ),

        );

        return $static_templates[$sms_event_name];
    }

    protected static function getIdTab($tabClassName)
    {
        return (int) \Db::getInstance()->getValue(
            'SELECT id_tab FROM '._DB_PREFIX_.'tab WHERE class_name = \''.pSQL($tabClassName).'\''
        );
    }

    public function installModuleTab($tabClassName, $TabParentName)
    {
        $tab = new \Tab();
        $tabName = array();

        $langues = \Language::getLanguages(false);
        foreach ($langues as $langue) {
            $tabName[$langue['id_lang']] = $this->menu_name;
        }

        $tab_parent_id = self::getIdTab($TabParentName);

        $tab->name       = $tabName;
        $tab->class_name = $tabClassName;
        $tab->module     = $this->name;
        $tab->id_parent  = $tab_parent_id;
        $id_tab          = $tab->save();
        if (!$id_tab) {
            return false;
        }

        return true;
    }

    public function installcleanPositions($id, $id_parent)
    {
        $result = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT `id_tab`,`position`
            FROM `' ._DB_PREFIX_.'tab`
            WHERE `id_parent` = ' .(int) $id_parent.'
            AND `id_tab` != ' .(int) $id.'
            ORDER BY `position`');
        $sizeof = count($result);
        for ($i = 0; $i < $sizeof; ++$i) {
            \Db::getInstance()->execute('
            UPDATE `' ._DB_PREFIX_.'tab`
            SET `position` = ' .(int) ($result[$i]['position'] + 1).'
            WHERE `id_tab` = ' .(int) $result[$i]['id_tab']);
        }

        \Db::getInstance()->execute('
            UPDATE `' ._DB_PREFIX_.'tab`
            SET `position` = 2
            WHERE `id_tab` = ' .(int) $id);

        return true;
    }

    protected function uninstallModuleTab($tabClass)
    {
        $idTab = self::getIdTab($tabClass);
        if ($idTab != 0) {
            $tab = new \Tab($idTab);
            $tab->delete();

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        /* Account settings */
        \Configuration::deleteByName('MTARGET_API_KEY');
        \Configuration::deleteByName('MTARGET_API_SECRET');
        \Configuration::deleteByName('MTARGET_TOKEN');
        \Configuration::deleteByName('MTARGET_LIVE_MODE');
        \Configuration::deleteByName('MTARGET_CONNECTION_STATUS');
        \Configuration::deleteByName('MTARGET_ADMIN_NUM');
        \Configuration::deleteByName('MTARGET_SENDER');
        \Configuration::deleteByName('MTARGET_TEMPLATE_GROUP');

        \Db::getInstance()
            ->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'mtarget_sms');
        \Db::getInstance()
            ->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'mtarget_sms_lang');
        \Db::getInstance()
            ->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'mtarget_cart');
        \Db::getInstance()
            ->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'mtarget_segment');
        \Db::getInstance()
            ->execute('DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'mtarget_segment_lang');

        $this->uninstallModuleTab($this->menu_controller);

        return parent::uninstall();
    }

    public function setFlashMsg($key, $value)
    {
        $this->context->cookie->__set($key, $value);
    }

    public function getContent()
    {
        /* check if a new user */
        $this->checkMtargetUser($this->api_key, $this->api_secret);

        $token = \Tools::getAdminToken(
            'AdminMtarget'.(int) \Tab::getIdFromClassName('AdminMtarget').
            (int) $this->context->cookie->id_employee
        );

        /* launch birthdays cron */
        $action = \Tools::getValue('action');
        if ($action == 'launchBirthdays') {
            $this->launchSMSBirthdays();
        }

        return \Tools::redirectAdmin('index.php?controller=AdminMtarget&token='.$token.'&action=dashboard');
    }

    public function getBirthdaysUrl()
    {
        $mtarget_birthdays_url = $this->context->link->getModuleLink($this->name, 'cronbirthdays');
        $mtarget_birthdays_url .= (parse_url($mtarget_birthdays_url, PHP_URL_QUERY)) ? "&" : "?";
        $mtarget_birthdays_url .= 'secure_key='.md5(_COOKIE_KEY_ . \Configuration::get('PS_SHOP_NAME'));

        return $mtarget_birthdays_url;
    }

    /**
     * @return array
     */
    public function getConfigFieldsValues()
    {
        return array(
            'MTARGET_API_KEY'    => \Configuration::get('MTARGET_API_KEY'),
            'MTARGET_API_SECRET' => \Configuration::get('MTARGET_API_SECRET'),
            'MTARGET_TOKEN'      => \Configuration::get('MTARGET_TOKEN'),
        );
    }

    public function getAllMessages()
    {
        $dbQuery = new \DbQuery();
        $dbQuery->select('s.id_mtarget_sms');
        $dbQuery->from('mtarget_sms', 's');
        $sms_ids = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbQuery);

        $messages = array();
        foreach ($sms_ids as $id) {
            $curr_sms = new MtargetSMS($id['id_mtarget_sms']);
            if ($curr_sms->is_order === '1') {
                $curr_sms->order_name = (new \OrderState($curr_sms->id_order_state))->name[$this->context->language->id];
            }
            $messages[] = $curr_sms;
        }

        return $messages;
    }

    /**
     * get user information settings
     * @param string $api_key
     * @param string $api_secret
     */
    public function checkMtargetUser($api_key, $api_secret)
    {
        $apiConnect = new MtargetApiConnect;
        $check_user = $apiConnect->checkMtargetKeys($api_key, $api_secret);

        if (isset($check_user['data']) && $check_user['code'] == 200) {
            $this->new_user = false;
            \Configuration::updateValue('MTARGET_CONNECTION_STATUS', 1);
            $balance = $apiConnect->getMtargetBalance();
            $this->context->smarty->assign('active', 'dashboard');
            $this->context->smarty->assign('balance', $balance['data']);

            return $check_user['data'];
        }

        return false;
    }

    /** Send SMS to the admin when a new account is created
     *
     * @param $params
     */
    public function hookCreateAccount($params)
    {
        $id_shop = (int) $params['newCustomer']->id_shop;
        $email = $params['newCustomer']->email;
        $shop = new \Shop((int) $id_shop);
        $shop_url = $shop->getBaseURL();

        /* SMS TO ADMIN */
        /* update content template admin */
        $dbQuery = new \DbQuery();
        $dbQuery->select('id_mtarget_sms');
        $dbQuery->from('mtarget_sms');
        $dbQuery->where('event_name = "new_account"');
        $id = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($dbQuery);

        $sms = new MtargetSMS($id);
        if ($sms->active_admin === "1") {
            $template_sms = $sms->content_admin[(int) $this->context->language->id];
            $template_sms = str_replace('#email#', $email, $template_sms);
            $template_sms = str_replace('#url#', $shop_url, $template_sms);
            /* create template sms admin */
            $sms_alerting = new MtargetApiSmsAlerting;
            $post_params = array(
                'title' => $sms->event_name,
                'sender' => \Configuration::get('MTARGET_SENDER'),
                'content' => $template_sms,
                'editable' => 1,
            );
            $response = $sms_alerting->createTemplate($post_params);
            if ($response['code'] == 200) {
                /* launch campaign */
                $id_template = (int) $response['data']->id;
                $post_launch_params = array(
                    'name' => $sms->event_name,
                    'numbers' => preg_replace('/\s+/', '', \Configuration::get('MTARGET_ADMIN_NUM')),
                    'out_of_offers' => 'send',
                    'send_now' => 1,
                );
                $sms_alerting->launchCampaign((int) $id_template, $post_launch_params);
            }
        }
    }

    /**
     * Send SMS to the admin and the customer when a new order is created
     *
     * @param array $params
     */
    public function hookNewOrder($params)
    {
        $num_order     = $params['order']->reference;
        $total_order   = $params['order']->total_paid . " " . $params['currency']->sign;
        $id_shop       = (int) $params['order']->id_shop;
        $email         = $params['customer']->email;
        $firstname     = $params['customer']->firstname;
        $lastname      = $params['customer']->lastname;
        $customer_lang = $params['customer']->id_lang;
        $address       = new \Address((int) $params['order']->id_address_delivery);
        $phone         = (!empty($address->phone_mobile)) ? $address->phone_mobile : $address->phone;
        $shop          = new \Shop((int) $id_shop);
        $shop_url      = $shop->getBaseURL();
        
        $dbQuery = new \DbQuery();
        $dbQuery->select('id_mtarget_sms');
        $dbQuery->from('mtarget_sms');
        $dbQuery->where('event_name = "new_order"');
        $id = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($dbQuery);

        $sms = new MtargetSMS($id);
        if ($sms->active_admin === "1") {
            $template_sms = $sms->content_admin[(int) $this->context->language->id];
            $template_sms = preg_replace(array('/#email#/','/#url#/','/#num_order#/','/#amount#/'), array($email, $shop_url, $num_order, $total_order), $template_sms);
            /* create template sms admin */
            $MtargetApiSmsAlerting = new MtargetApiSmsAlerting;
            $response = $MtargetApiSmsAlerting->createTemplate(array(
                'title'    => $sms->event_name,
                'sender'   => \Configuration::get('MTARGET_SENDER'),
                'content'  => $template_sms,
                'editable' => 1,
            ));
            if ($response['code'] == 200) {
                /* launch campaign */
                $id_template = (int) $response['data']->id;
                $MtargetApiSmsAlerting->launchCampaign((int) $id_template, array(
                    'name'          => $sms->event_name,
                    'numbers'       => preg_replace('/\s+/', '', \Configuration::get('MTARGET_ADMIN_NUM')),
                    'out_of_offers' => 'send',
                    'send_now'      => 1,
                ));
            }
        }
        if ($sms->active_customer === "1" && $params['customer']->id !== null) {
            $template_sms = $sms->content_customer[(int) $customer_lang];
            $template_sms = preg_replace(array('/#firstname#/','/#lastname#/','/#url#/','/#num_order#/'), array($firstname, $lastname, $shop_url, $num_order), $template_sms);
            /* create template sms admin */
            $MtargetApiSmsAlerting = new MtargetApiSmsAlerting;
            $post_params = array(
                'title' => $sms->event_name,
                'sender' => \Configuration::get('MTARGET_SENDER'),
                'content' => $template_sms,
                'editable' => 1,
            );
            $response = $MtargetApiSmsAlerting->createTemplate($post_params);
            if ($response['code'] == 200) {
                /* launch campaign */
                $id_template = (int) $response['data']->id;
                $post_launch_params = array(
                    'name' => $sms->event_name,
                    'numbers' => $phone,
                    'out_of_offers' => 'send',
                    'send_now' => 1,
                );
                $MtargetApiSmsAlerting->launchCampaign((int) $id_template, $post_launch_params);
            }
        }
        /* Delete campaign for Abandoned Carts for this order */
        (new MtargetComponent)->deleteCampaign((int) $params['order']->id_cart);
    }

    /**
     * Send SMS to the admin
     * @param array $params
     */
    public function hookOrderReturn($params)
    {
        $id_customer     = (int) $params['orderReturn']->id_customer;
        $id_order        = (int) $params['orderReturn']->id_order;
        $customer        = new \Customer((int) $id_customer);
        $order           = new \Order($id_order);
        $address         = new \Address((int) $order->id_address_delivery);
        $phone           = (!empty($address->phone_mobile)) ? $address->phone_mobile : $address->phone;
        $order_return    = new \OrderReturn($params['orderReturn']->id);
        $shop            = new \Shop((int) $params['cart']->id_shop);
        $shop_url        = $shop->getBaseURL();
        $return_products = $order_return->getOrdersReturnProducts((int) $params['orderReturn']->id, $order);
        $codes           = array();
        foreach ($return_products as $product) {
            $codes[] = $product['reference'];
        }
        $codes_list = implode(', ', $codes);

        $dbQuery = new \DbQuery();
        $dbQuery->select('id_mtarget_sms');
        $dbQuery->from('mtarget_sms');
        $dbQuery->where('event_name = "new_order_return"');
        $id = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($dbQuery);

        $sms = new MtargetSMS($id);
        if ($sms->active_admin === "1") {
            $template_sms = $sms->content_admin[(int) $this->context->language->id];
            $template_sms = str_replace('#email#', $customer->email, $template_sms);
            $template_sms = str_replace('#code_prod#', $codes_list, $template_sms);
            $template_sms = str_replace('#url#', $shop_url, $template_sms);

            /* create template sms */
            $sms_alerting = new MtargetApiSmsAlerting;
            $post_params = array(
                'title' => $sms->event_name,
                'sender' => \Configuration::get('MTARGET_SENDER'),
                'content' => $template_sms,
                'editable' => 1,
            );
            $response = $sms_alerting->createTemplate($post_params);
            if ($response['code'] == 200) {
                /* launch campaign */
                $id_template = (int) $response['data']->id;
                $post_launch_params = array(
                    'name' => $sms->event_name,
                    'numbers' => preg_replace('/\s+/', '', \Configuration::get('MTARGET_ADMIN_NUM')),
                    'out_of_offers' => 'send',
                    'send_now' => 1,
                );
                $sms_alerting->launchCampaign((int) $id_template, $post_launch_params);
            }
        }
        if ($sms->active_customer === "1" && $customer->id !== null) {
            $template_sms = $sms->content_customer[(int) $customer->id_lang];
            $template_sms = str_replace('#email#', $customer->email, $template_sms);
            $template_sms = str_replace('#code_prod#', $codes_list, $template_sms);
            $template_sms = str_replace('#url#', $shop_url, $template_sms);

            /* create template sms */
            $sms_alerting = new MtargetApiSmsAlerting;
            $post_params = array(
                'title' => $sms->event_name,
                'sender' => \Configuration::get('MTARGET_SENDER'),
                'content' => $template_sms,
                'editable' => 1,
            );
            $response = $sms_alerting->createTemplate($post_params);
            if ($response['code'] == 200) {
                /* launch campaign */
                $id_template = (int) $response['data']->id;
                $post_launch_params = array(
                    'name' => $sms->event_name,
                    'numbers' => $phone,
                    'out_of_offers' => 'send',
                    'send_now' => 1,
                );
                $sms_alerting->launchCampaign((int) $id_template, $post_launch_params);
            }
        }
    }

    /**
     * Send SMS to the customer if order status is changed
     * @param array $params
     */
    public function hookPostUpdateOrderStatus($params)
    {
        $dbQuery = new \DbQuery();
        $dbQuery->select('id_mtarget_sms');
        $dbQuery->from('mtarget_sms');
        $dbQuery->where('id_order_state = '.(int)$params['newOrderStatus']->id);
        $id = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($dbQuery);
        if ($id !== false) {
            $sms = new MtargetSMS($id);

            $id_customer = (int) $params['cart']->id_customer;
            $id_order = (int) $params['id_order'];
            $customer = new \Customer((int) $id_customer);
            $order = new \Order((int) $id_order); //refrence
            $address = new \Address((int) $params['cart']->id_address_delivery);
            $phone = (!empty($address->phone_mobile)) ? $address->phone_mobile : $address->phone;
            $shop = new \Shop((int) $params['cart']->id_shop);
            $shop_url = $shop->getBaseURL();
            if ($sms->active_admin === "1") {
                $template_sms = $sms->content_admin[(int) $this->context->language->id];
                $template_sms = str_replace('#firstname#', $customer->firstname, $template_sms);
                $template_sms = str_replace('#lastname#', $customer->lastname, $template_sms);
                $template_sms = str_replace('#num_order#', $order->reference, $template_sms);
                $template_sms = str_replace('#amount#', $order->total_paid, $template_sms);
                $template_sms = str_replace('#url#', $shop_url, $template_sms);
                $template_sms = str_replace('#status#', $params['newOrderStatus']->name, $template_sms);

                /* We can have special admin num per order state */
                if ($sms->order_special_admin_num !== '') {
                    $sendto = preg_replace('/\s+/', '', $sms->order_special_admin_num);
                } else {
                    $sendto = preg_replace('/\s+/', '', \Configuration::get('MTARGET_ADMIN_NUM'));
                }
                /* create template sms */
                $sms_alerting = new MtargetApiSmsAlerting;
                $post_params = array(
                    'title' => $sms->event_name,
                    'sender' => \Configuration::get('MTARGET_SENDER'),
                    'content' => $template_sms,
                    'editable' => 1,
                );
                $response = $sms_alerting->createTemplate($post_params);
                if ($response['code'] == 200) {
                    /* launch campaign */
                    $id_template = (int) $response['data']->id;
                    $post_launch_params = array(
                        'name' => $sms->event_name,
                        'numbers' => $sendto,
                        'out_of_offers' => 'send',
                        'send_now' => 1,
                    );
                    $sms_alerting->launchCampaign((int) $id_template, $post_launch_params);
                }
            }
            if ($sms->active_customer === "1" && $customer->id !== null) {
                $template_sms = $sms->content_customer[(int) $customer->id_lang];
                $template_sms = str_replace('#firstname#', $customer->firstname, $template_sms);
                $template_sms = str_replace('#lastname#', $customer->lastname, $template_sms);
                $template_sms = str_replace('#num_order#', $order->reference, $template_sms);
                $template_sms = str_replace('#amount#', $order->total_paid, $template_sms);
                $template_sms = str_replace('#url#', $shop_url, $template_sms);
                $template_sms = str_replace('#status#', $params['newOrderStatus']->name, $template_sms);
                /* create template sms */
                $sms_alerting = new MtargetApiSmsAlerting;
                $post_params = array(
                    'title' => $sms->event_name,
                    'sender' => \Configuration::get('MTARGET_SENDER'),
                    'content' => $template_sms,
                    'editable' => 1,
                );
                $response = $sms_alerting->createTemplate($post_params);
                if ($response['code'] == 200) {
                    /* launch campaign */
                    $id_template = (int) $response['data']->id;
                    $post_launch_params = array(
                        'name' => $sms->event_name,
                        'numbers' => $phone,
                        'out_of_offers' => 'send',
                        'send_now' => 1,
                    );
                    $sms_alerting->launchCampaign((int) $id_template, $post_launch_params);
                }
            }
        }
    }

    /**
     * Manages the cart SMSs: create a campaign that will launch at the correct time, and deletes/re-create it on cart updates
     * @param array $params
     */
    public function hookActionCartSave($params)
    {
        $dbQuery = new \DbQuery();
        $dbQuery->select('id_mtarget_sms');
        $dbQuery->from('mtarget_sms');
        $dbQuery->where('event_name = "cart"');
        $id_sms = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($dbQuery);
        /* check if mode test not activated */
        $sms = new MtargetSMS($id_sms);

        if (isset($params['cart'])) {
            $customer = new \Customer((int) $params['cart']->id_customer);
            $shop     = new \Shop((int) $params['cart']->id_shop);
            $id_cart  = (int) $params['cart']->id;

            //Sans adresse on a pas de numéro de téléphone et on peut rien faire
            if (isset($params['cart']->id_address_delivery)) {
                $address  = new \Address((int) $params['cart']->id_address_delivery);
                $phone    = (!empty($address->phone_mobile)) ? $address->phone_mobile : $address->phone;
                $shop_url = $shop->getBaseURL();

                /* check if id_cart exists in table mtarget_cart */
                $dbQuery = new \DbQuery();
                $dbQuery->select('id_campaign_admin, id_campaign_customer');
                $dbQuery->from('mtarget_cart');
                $dbQuery->where('id_cart = ' . (int) $id_cart);
                $ids_campaigns = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbQuery);
                $manage = new MtargetManage();
                // Checks if cart is empty
                $cartProducts = $manage->getCartProducts((int) $id_cart);
                // Si on a 0 id_campaign, c'est qu'on a rien en base pour ce panier - on va donc le créer
                if (count($ids_campaigns) === 0) {
                    // En théorie, si on crée le panier, y'a des produits. Au cas où, on vérifie, mais si y'a rien, je sais pas trop quoi faire (donc je fais rien)
                    if ($cartProducts !== false) {
                        \Db::getInstance()->insert('mtarget_cart', array('id_cart' => (int) $id_cart, 'id_campaign_admin' => '', 'id_campaign_customer' => ''));

                        if ($sms->active_customer === "1" && $customer->id !== null) {
                            /* update content template */
                            $template_sms = $sms->content_customer[(int) $customer->id_lang];
                            $template_sms = str_replace('#firstname#', $customer->firstname, $template_sms);
                            $template_sms = str_replace('#lastname#', $customer->lastname, $template_sms);
                            $template_sms = str_replace('#url#', $shop_url, $template_sms);
                            /* create template sms */
                            $sms_alerting = new MtargetApiSmsAlerting;
                            $post_params = array(
                                'title'    => $sms->event_name,
                                'sender'   => \Configuration::get('MTARGET_SENDER'),
                                'content'  => $template_sms,
                                'editable' => 1,
                            );
                            $response = $sms_alerting->createTemplate($post_params);
                            if ($response['code'] == 200) {
                                /* launch campaign after n hours entered by user */
                                $dateTime = new \DateTime('now');
                                $dateInterval = new \DateInterval('PT' . (int) $sms->time_limit . 'H');
                                $dateTime->add($dateInterval);
                                $send_date = $dateTime->format('Y-m-d H:i:s');
                                $id_template = (int) $response['data']->id;
                                $post_launch_params = array(
                                    'name'          => $sms->event_name,
                                    'numbers'       => $phone,
                                    'out_of_offers' => 'send',
                                    'send_date'     => $send_date,
                                );
                                $response_launch = $sms_alerting->launchCampaign((int) $id_template, $post_launch_params);

                                if ($response_launch['code'] == 200) {
                                    /* save id campaign in table mtarget_cart */
                                    \Db::getInstance()
                                    ->update(
                                        'mtarget_cart',
                                        array('id_campaign_customer' => (int) $response_launch['data']->id),
                                        'id_cart = ' . (int) $id_cart
                                    );
                                }
                            }
                        }
                        
                        if ($sms->active_admin === "1") {
                            /* update content template */
                            $template_sms = $sms->content_admin[(int) $this->context->language->id];
                            $template_sms = str_replace('#firstname#', $customer->firstname, $template_sms);
                            $template_sms = str_replace('#lastname#', $customer->lastname, $template_sms);
                            $template_sms = str_replace('#url#', $shop_url, $template_sms);
                            /* create template sms */
                            $sms_alerting = new MtargetApiSmsAlerting;
                            $post_params = array(
                                'title'    => $sms->event_name,
                                'sender'   => \Configuration::get('MTARGET_SENDER'),
                                'content'  => $template_sms,
                                'editable' => 1,
                            );
                            $response = $sms_alerting->createTemplate($post_params);
                            if ($response['code'] == 200) {
                                /* launch campaign after n hours entered by user */
                                $dateTime = new \DateTime('now');
                                $dateInterval = new \DateInterval('PT' . (int) $sms->time_limit . 'H');
                                $dateTime->add($dateInterval);
                                $send_date = $dateTime->format('Y-m-d H:i:s');
                                $id_template = (int) $response['data']->id;
                                $post_launch_params = array(
                                    'name'          => $sms->event_name,
                                    'numbers'       => preg_replace('/\s+/', '', \Configuration::get('MTARGET_ADMIN_NUM')),
                                    'out_of_offers' => 'send',
                                    'send_date'     => $send_date,
                                );
                                $response_launch = $sms_alerting->launchCampaign((int) $id_template, $post_launch_params);

                                if ($response_launch['code'] == 200) {
                                    /* save id campaign in table mtarget_cart */
                                    \Db::getInstance()
                                    ->update(
                                        'mtarget_cart',
                                        array('id_campaign_admin' => (int) $response_launch['data']->id),
                                        'id_cart = ' . (int) $id_cart
                                    );
                                }
                            }
                        }
                    }
                    //Ici, on a déjà une campagne pour ce panier, on va la supprimer/relancer/remplacer
                } else {
                    $sms_alerting = new MtargetApiSmsAlerting;

                    if ($sms->active_admin === "1") {
                        if ($ids_campaigns[0]['id_campaign_admin'] !== "0") {
                            $response_admin = $sms_alerting->deleteCampaign((int) $ids_campaigns[0]['id_campaign_admin']);
                        } else {
                            $response_admin = array();
                            $response_admin['code'] = 200;
                        }

                        if ($response_admin['code'] === 200) {
                            /* update content template */
                            $template_sms = $sms->content_admin[(int) $this->context->language->id];
                            $template_sms = str_replace('#firstname#', $customer->firstname, $template_sms);
                            $template_sms = str_replace('#lastname#', $customer->lastname, $template_sms);
                            $template_sms = str_replace('#url#', $shop_url, $template_sms);
                            /* create template sms */
                            $post_params = array(
                                'title'    => $sms->event_name,
                                'sender'   => \Configuration::get('MTARGET_SENDER'),
                                'content'  => $template_sms,
                                'editable' => 1,
                            );
                            $response = $sms_alerting->createTemplate($post_params);
                            if ($response['code'] == 200) {
                                /* launch campaign after n hours entered by user */
                                $dateTime = new \DateTime('now');
                                $dateInterval = new \DateInterval('PT' . (int) $sms->time_limit . 'H');
                                $dateTime->add($dateInterval);
                                $send_date = $dateTime->format('Y-m-d H:i:s');
                                $id_template = (int) $response['data']->id;
                                $post_launch_params = array(
                                    'name'          => $sms->event_name,
                                    'numbers'       => preg_replace('/\s+/', '', \Configuration::get('MTARGET_ADMIN_NUM')),
                                    'out_of_offers' => 'send',
                                    'send_date'     => $send_date,
                                );
                                $response_update_admin = $sms_alerting->launchCampaign((int) $id_template, $post_launch_params);
                                $campaign_admin = $response_update_admin['data']->id;
                            }
                        }
                    }

                    if ($sms->active_customer === "1" && $customer->id !== null) {
                        if ($ids_campaigns[0]['id_campaign_customer'] !== "0") {
                            $response_customer = $sms_alerting->deleteCampaign((int) $ids_campaigns[0]['id_campaign_customer']);
                        } else {
                            $response_customer = array();
                            $response_customer['code'] = 200;
                        }

                        if ($response_customer['code'] === 200) {
                            /* update content template */
                            $template_sms = $sms->content_customer[(int) $customer->id_lang];
                            $template_sms = str_replace('#firstname#', $customer->firstname, $template_sms);
                            $template_sms = str_replace('#lastname#', $customer->lastname, $template_sms);
                            $template_sms = str_replace('#url#', $shop_url, $template_sms);
                            /* create template sms */
                            $post_params = array(
                                'title'    => $sms->event_name,
                                'sender'   => \Configuration::get('MTARGET_SENDER'),
                                'content'  => $template_sms,
                                'editable' => 1,
                            );
                            $response = $sms_alerting->createTemplate($post_params);
                            if ($response['code'] == 200) {
                                /* launch campaign after n hours entered by user */
                                $dateTime = new \DateTime('now');
                                $dateInterval = new \DateInterval('PT' . (int) $sms->time_limit . 'H');
                                $dateTime->add($dateInterval);
                                $send_date = $dateTime->format('Y-m-d H:i:s');
                                $id_template = (int) $response['data']->id;
                                $post_launch_params = array(
                                    'name'          => $sms->event_name,
                                    'numbers'       => $phone,
                                    'out_of_offers' => 'send',
                                    'send_date'     => $send_date,
                                );
                                $response_update_customer = $sms_alerting->launchCampaign((int) $id_template, $post_launch_params);
                                $campaign_customer = $response_update_customer['data']->id;
                            }
                        }
                    }
                    if (!isset($campaign_admin)) {
                        $campaign_admin = '0';
                    }
                    if (!isset($campaign_customer)) {
                        $campaign_customer = '0';
                    }


                    \Db::getInstance()
                    ->update(
                        'mtarget_cart',
                        array(
                            'id_campaign_admin'    => (int) $campaign_admin,
                            'id_campaign_customer' => (int) $campaign_customer,
                        ),
                        'id_cart = ' . (int) $id_cart
                    );
                }
            }
        }
    }

    public function launchSMSBirthdays()
    {
        $dbQuery = new \DbQuery();
        $dbQuery->select('id_mtarget_sms');
        $dbQuery->from('mtarget_sms');
        $dbQuery->where('event_name = "birthday"');
        $id = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($dbQuery);

        $sms = new MtargetSMS($id);
        $sender = \Configuration::get('MTARGET_SENDER');
        if (empty($sender)) {
            return $this->adminDisplayWarning($this->l('Sender has not been configured.'));
        }

        $nb_days = (int) $sms->time_limit;
        /* get list of customers who anniversary date after $nb_days */
        $manage = new MtargetManage();
        $customersList = $manage->getCustomersBirthdays($nb_days);
        if (!empty($customersList)) {
            $nb_contacts = count($customersList);

            $nb_sms_to_send = 0;
            if ($sms->active_customer xor $sms->active_admin) {
                $nb_sms_to_send = $nb_contacts;
            } elseif ($sms->active_customer && $sms->active_admin) {
                $nb_sms_to_send = $nb_contacts * 2;
            }

            $nb_send_sms = 0;
            foreach ($customersList as $customer) {
                $shop = new \Shop((int) $customer->id_shop);
                $shop_url = $shop->getBaseURL();
                //get mobile number
                $addresses = $customer->getAddresses($customer->id_lang);
                $mobile = '';
                if ($addresses) {
                    $mobile = ($addresses[0]['phone_mobile'] == '') ? $addresses[0]['phone'] : $addresses[0]['phone_mobile'];
                }
                // if customer has mobile number (and it's active for customers) : send SMS
                if ($sms->active_customer && $mobile !== '') {
                    $template_sms = $sms->content_customer[$customer->id_lang];
                    $template_sms = preg_replace(
                        array('/#firstname#/','/#lastname#/','/#url#/'),
                        array($customer->firstname, $customer->lastname, $shop_url),
                        $template_sms
                    );
                    /* create template sms */
                    $sms_alerting = new MtargetApiSmsAlerting;
                    $response = $sms_alerting->createTemplate(array(
                        'title'    => $sms->event_name,
                        'sender'   => $sender,
                        'content'  => $template_sms,
                        'editable' => 1,
                    ));

                    if ($response['code'] == 200) {
                        /* launch campaign */
                        $id_template = (int) $response['data']->id;
                        $response_launch = $sms_alerting->launchCampaign((int) $id_template, array(
                            'name'          => $sms->event_name,
                            'numbers'       => $mobile,
                            'out_of_offers' => 'send',
                            'send_now'      => 1,
                        ));
                        if ($response_launch['code'] == 200) {
                            $nb_send_sms++;
                        }
                    }
                }
                if ($sms->active_admin) {
                    $template_sms = $sms->content_admin[(int) $this->context->language->id];
                    $template_sms = preg_replace(
                        array('/#firstname#/','/#lastname#/','/#url#/'),
                        array($customer->firstname, $customer->lastname, $shop_url),
                        $template_sms
                    );
                    /* create template sms */
                    $sms_alerting = new MtargetApiSmsAlerting;
                    $response = $sms_alerting->createTemplate(array(
                        'title'    => $sms->event_name,
                        'sender'   => $sender,
                        'content'  => $template_sms,
                        'editable' => 1,
                    ));

                    if ($response['code'] == 200) {
                        /* launch campaign */
                        $id_template = (int) $response['data']->id;
                        $response_launch = $sms_alerting->launchCampaign((int) $id_template, array(
                            'name'          => $sms->event_name,
                            'numbers'       => preg_replace('/\s+/', '', \Configuration::get('MTARGET_ADMIN_NUM')),
                            'out_of_offers' => 'send',
                            'send_now'      => 1,
                        ));
                        if ($response_launch['code'] == 200) {
                            $nb_send_sms++;
                        }
                    }
                }
            }

                return $this->setFlashMsg('flash_msg_success', $nb_send_sms . ' ' . $this->l('sms sent from') . ' ' . $nb_sms_to_send);
        }

        return $this->adminDisplayWarning($this->l('No customer birthdays today.'));
    }
}

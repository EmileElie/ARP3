<?php
/**
 * NOTICE OF LICENSE.
 *
 * This source file is subject to a commercial license from MTARGET SAS
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the MTARGET SAS is strictly forbidden.
 *
 *  @author     Mtarget
 *  @copyright  2017 Mtarget
 *  @license    All Rights Reserved
 */

class AdminMtargetController extends ModuleAdminController
{
    public $context;

    public static $currentIndex;

    public $is_user_connected;

    protected $mtarget_token;

    public function __construct()
    {
        $this->lang      = true;
        $this->bootstrap = true;
        $this->context   = Context::getContext();
        $this->name      = 'mtarget';

        parent::__construct();

        if (!isset(self::$currentIndex)) {
            self::$currentIndex = 'index.php?controller=AdminMtarget&token='.\Tools::getValue('token');
        }

        $this->is_user_connected = (bool) \Configuration::get('MTARGET_CONNECTION_STATUS');
        $this->link_credit = "https://prestashop.mylittlebiz.fr/exterior-login/" . \Configuration::get('MTARGET_TOKEN');

        // Traite les différentes requêtes POST/GET/AJAX
        new MtargetPostProcess($this, $this->token);
    }

    /**
     * enregistre un message en cookie
     */
    public function _setFlashMsg($key, $value)
    {
        $this->context->cookie->__set($key, $value);
    }

    /**
     * methode globale qui affiche les pages
     */
    public function renderList()
    {
        $action = \Tools::getValue('action');

        $token = \Tools::getAdminToken(
            'AdminMtarget'.(int) Tab::getIdFromClassName('AdminMtarget').(int) $this->context->cookie->id_employee
        );

        if (!$this->is_user_connected) {
            $action = (in_array($action, array('home', 'configuration'))) ? $action : 'home';
        } else {
            $action = (in_array($action, array('dashboard', 'sms', 'marketing', 'myaccount'))) ? $action : 'dashboard';
        }

        $method = 'mtg'.\Tools::toCamelCase($action, true);

        $this->$method();

        $tpl_file = './_partials/mtarget-presentation.tpl';
        if (file_exists(_PS_MODULE_DIR_.'mtarget/views/templates/admin/mtarget-'.$action.'.tpl')) {
            $tpl_file = './mtarget-'.$action.'.tpl';
        }

        $balance = (new MtargetApiConnect)->getMtargetBalance();
        $versionStr = '?v='.(new Mtarget)->version;

        $this->addCSS(_MODULE_DIR_ . 'mtarget/views/css/mtarget.css' . $versionStr, 'all', null, false);
        $this->addJS(_MODULE_DIR_ . 'mtarget/views/js/mtarget.js'  . $versionStr, false);
        if ($action === 'sms') {
            $this->addCSS(_MODULE_DIR_ . 'mtarget/views/css/bootstrap-select.min.css' . $versionStr, 'all', null, false);
            $this->addJS(_MODULE_DIR_ . 'mtarget/views/js/bootstrap-select.min.js' .$versionStr, false);
            $this->addJS(_MODULE_DIR_ . 'mtarget/views/js/libAlphaGsm.js' . $versionStr, false);
            $this->addJS(_MODULE_DIR_ . 'mtarget/views/js/messages.js' . $versionStr, false);
            \Media::addJsDef(array('url_ajax' => 'index.php?controller=AdminMtarget&token='.$token));
        }
        $this->addJqueryPlugin('fancybox');

        $this->context->smarty->assign(array(
            // 'debug'    => (_PS_MODE_DEV_ && !empty($this->api->debug)) ? $this->api->debug : array(),
            'balance'           => $balance,
            'mtarget_img_path'  => _MODULE_DIR_ . 'mtarget/views/img/',
            'tpl_file'          => $tpl_file,
            'is_user_connected' => $this->is_user_connected,
            'action'            => $action,
            'url_config'        => 'index.php?controller=AdminMtarget&token='.$token,
            'connection_status' => \Configuration::get('MTARGET_CONNECTION_STATUS'),
            'lang'              => $this->context->language->id,
        ));

        $flashMsg = '';

        if ($this->is_user_connected) {
            $langue = new \LanguageCore((int) $this->context->language->id);
            $this->context->smarty->assign(array(
                'pdf_guide' => _MODULE_DIR_ .'mtarget/faq-' . (in_array($langue->iso_code, array('fr', 'en', 'es', 'it')) ? $langue->iso_code : 'en') . '.pdf',
                'link_credit'   => $this->link_credit,
            ));

            if ($balance === 0) {
                $flashMsg .= $this->module->displayWarning($this->module->l('No credits available to send! Please top up :', 'mtarget'), false);
            }
        }

        $flashMsg .= $this->getFlashMsg();

        return $flashMsg.$this->context->smarty->fetch(_PS_MODULE_DIR_.'mtarget/views/templates/admin/mtarget-layout.tpl');
    }

    public function mtgHome()
    {
        $this->context->smarty->assign(array(
            'action'                 => 'home',
            'countries'              => \Country::getCountries(\Configuration::get('PS_LANG_DEFAULT')),
            'genders'                => \Gender::getGenders(),
            'authenticationSettings' => (new MtargetManage)->renderAuthenticationSettingsForm(),
            'iso_code'               => '',
        ));

        // return $this->context->smarty->fetch(_PS_MODULE_DIR_.'mtarget/views/templates/admin/mtarget-home.tpl');
    }

    public function mtgConfiguration()
    {
        $this->context->smarty->assign(array(
            'countries'              => \Country::getCountries(\Configuration::get('PS_LANG_DEFAULT')),
            'genders'                => \Gender::getGenders(),
            'authenticationSettings' => (new MtargetManage)->renderAuthenticationSettingsForm(),
            'current_user'           => array(
                'firstname' => $this->context->employee->firstname,
                'lastname'  => $this->context->employee->lastname,
                'email'     => $this->context->employee->email,
            ),
            // 'iso_code'               => '',
        ));
    }

    public function mtgDashboard()
    {
        $this->context->smarty->assign(array(
            'modeSetting'   => (new MtargetManage)->renderModeSettingForm(),
            'all_messages'  => (new MtargetManage)->getAllMessages(),
            'tab_stat'      => (new MtargetComponent)->getMtargetStatisticsByMonths(),
            'link_credit'   => $this->link_credit,
            'lang'          => $this->context->language->id,
        ));

        $this->context->smarty->assign((new MtargetComponent)->getMtargetStatisticsByType($this->context->language->id));
    }

    public function mtgSms()
    {
        $dbQuery = new \DbQuery();
        $dbQuery->select('id_mtarget_sms');
        $dbQuery->from('mtarget_sms');
        $dbQuery->where('event_name = "birthday"');
        $id = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($dbQuery);

        $birthdaySMS           = new MtargetSMS($id);
        $MtargetManage         = new MtargetManage;
        $customersListBirthday = $MtargetManage->getCustomersBirthdays((int) $birthdaySMS->time_limit);

        $this->context->smarty->assign(array(
            'smsSetting'               => $MtargetManage->renderSmsSettingForm(),
            'all_messages'             => (new MtargetManage)->getAllMessages(),
            'mtarget_launch_birthdays' => $this->context->link->getAdminLink('AdminModules', false).'&'.http_build_query(array(
                                                'token'     => \Tools::getAdminTokenLite('AdminModules'),
                                                'configure' => $this->name,
                                                'action'    => 'launchBirthdays'
                                            )),
            'nb_birthday_contacts'     => (!empty($customersListBirthday)) ? count($customersListBirthday) : 0,
            'mtarget_birthdays_url'    => $this->module->getBirthdaysUrl(),
            'lang'                     => $this->context->language->id,
            'all_languages'            => \Language::getLanguages(false),
            'employee_lang'            => $this->context->employee->id_lang,
        ));
    }

    public function mtgMyaccount()
    {
        $this->context->smarty->assign(array(
            'link_credit' => $this->link_credit,
            'accountSettings' => (new MtargetManage)->renderAccountSettingForm(),
        ));
    }

    public function mtgMarketing()
    {
        $this->context->smarty->assign(array(
            'url_delete_segment' => $this->context->link->getAdminLink('AdminMtarget', false).'&'.http_build_query(array(
                'token'     => \Tools::getAdminTokenLite('AdminMtarget'),
            )),
            'newSegment'   => (new MtargetManage)->renderNewSegment(),
            'SegmentsList' => (new MtargetSegment)->getList(),
            'link_credit'  => $this->link_credit,
        ));
    }

    public function mtgAccount()
    {
        $this->context->smarty->assign(array(
            'countries' => \Country::getCountries(\Configuration::get('PS_LANG_DEFAULT')),
            'genders' => \Gender::getGenders(),
        ));
    }

    private function getFlashMsg()
    {
        $flashMsg = '';
        if (!empty($this->context->cookie->flash_msg_success)) {
            $flashMsg .= $this->module->displayConfirmation($this->context->cookie->flash_msg_success);
            unset($this->context->cookie->flash_msg_success);
        }
        if (!empty($this->context->cookie->flash_msg_error)) {
            $flashMsg .= $this->module->displayError($this->context->cookie->flash_msg_error);
            unset($this->context->cookie->flash_msg_error);
        }
        // $this->context->smarty->assign(array('flashMsgSuccess' => $flashMsgSuccess, 'flashMsgError' => $flashMsgError));
        return $flashMsg;
    }
}

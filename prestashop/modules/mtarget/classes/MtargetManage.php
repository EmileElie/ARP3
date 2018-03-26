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
 * sorte de classe 'Helper'
 */
class MtargetManage extends \Mtarget
{

    /**
     * @return HelperForm
     */
    public function initHelperForm()
    {
        $helper = new \HelperForm;
        $helper->module = $this;
        $helper->show_toolbar = false;
        $helper->token = \Tools::getAdminTokenLite('AdminMtarget');
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = \Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminMtarget', false);

        return $helper;
    }

    public function renderSegmentList($id_segment)
    {
        require_once(dirname(__FILE__).'/MtargetSegment.php');
        $segment = new MtargetSegment($id_segment);
        $contactsList = $segment->getContactsList((int) $id_segment);
        $fields_list = array(
            'id_customer' => array(
                'title' => 'ID',
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ),
            'firstname'   => array(
                'title' => $this->l('name', 'mtargetmanage'),
                'type'  => 'text',
            ),
            'lastname'    => array(
                'title' => $this->l('Last Name', 'mtargetmanage'),
                'type'  => 'text',
            ),
            'email'       => array(
                'title' => 'Email',
                'type'  => 'text',
            ),
            'birthday'    => array(
                'title' => $this->l('date of birth', 'mtargetmanage'),
                'type'  => 'text',
            ),
            'optin'       => array(
                'title' => 'Optin',
                'type'  => 'text',
            ),
        );
        $helper = new \HelperList;
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->show_toolbar = false;
        $helper->module = $this;
        $helper->listTotal = count($contactsList);
        $helper->identifier = 'id_customer';
        $title = '<span class="title-list-segment" ><strong>'.$segment->name.'_'.$segment->reference.' :</strong>';
        $title .= '</span><span  class="title-list-segment" >Group : </span>';
        $title .= $segment->group[(int) $this->context->language->id];
        $title .= '; <span  class="title-list-segment" >Optin : </span>'.$segment->optin;
        $title .= '; <span  class="title-list-segment" >Order : </span>'.$segment->has_order.'<br/>';
        $title .= '<span  class="title-list-segment" ><strong>';
        $title .= $this->l('Result :', 'mtargetmanage');
        $title .= '</strong></span>'.$helper->listTotal.' '.$this->l('items in the list', 'mtargetmanage').' <br/> ';
        $helper->title = $title;
        $helper->table = 'customer';
        $helper->token = \Tools::getAdminTokenLite('AdminCustomers');
        // $helper->currentIndex = 'index.php?controller=AdminMtarget&action=marketing';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminCustomers', false);

        return $helper->generateList($contactsList, $fields_list);
    }

    /**
     * @return string
     */
    public function renderAuthenticationSettingsForm()
    {
        $fieldsValue = $this->getConfigFieldsValues();
        $helper = $this->initHelperForm();
        $helper->submit_action = 'submitMtargetAuthentication';
        $helper->name_controller = 'col-lg-6';
        $helper->tpl_vars = array(
            'fields_value' => $fieldsValue,
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );
        $helperHtml = $helper->generateForm(array($this->getAuthenticationSettingsForm()));

        return $helperHtml;
    }

    /**
     * @return string
     */
    public function renderModeSettingForm()
    {
        $helper = $this->initHelperForm();
        $helper->submit_action = 'submitMtargetModeSetting';
        $helper->name_controller = 'col-lg-12';
        $helper->fields_value['MTARGET_LIVE_MODE'] = \Configuration::get('MTARGET_LIVE_MODE');
        $helperHtml = $helper->generateForm(array($this->getModeSettingForm()));

        return $helperHtml;
    }

    /**
     * @return string
     */
    public function renderAccountSettingForm()
    {
        $helper = $this->initHelperForm();
        $helper->name_controller = 'col-lg-12';
        $fieldsValue = $this->getConfigFieldsValues();
        $helper->submit_action = 'submitMtargetAccount';
        $helper->tpl_vars = array(
            'fields_value' => $fieldsValue,
        );
        $helperHtml = $helper->generateForm(array($this->getAccountSettingsForm()));

        return $helperHtml;
    }

    /**
     * @return string
     */
    public function renderSmsSettingForm()
    {
        $helper = $this->initHelperForm();
        $helper->submit_action = 'submitMtargetSmsSetting';
        $helper->name_controller = 'col-lg-12';
        $helper->fields_value['MTARGET_ADMIN_NUM'] = \Configuration::get('MTARGET_ADMIN_NUM');
        $helper->fields_value['MTARGET_SENDER'] = \Configuration::get('MTARGET_SENDER');
        $helperHtml = $helper->generateForm(array($this->getSmsSettingsForm()));

        return $helperHtml;
    }

    /**
     * @return string
     */
    public function renderNewSegment()
    {
        $helper = $this->initHelperForm();
        // $helper->submit_action = 'submitNewSegmentForm';
        // $helper->name_controller = 'col-lg-5';
        $helper->tpl_vars = array(
            'fields_value' => array('order' => '1'),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );
        $helperHtml = $helper->generateForm(array($this->getNewSegmentForm()));

        return $helperHtml;
    }

    /**
     * @return array
     */
    public function getAuthenticationSettingsForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('You have an account ?', 'mtargetmanage'),
                    'icon'  => 'icon-user',
                ),
                'input'  => array(
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('API Key'),
                        'name'     => 'MTARGET_API_KEY',
                        'required' => true,
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('API Secret'),
                        'name'     => 'MTARGET_API_SECRET',
                        'required' => true,
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Token'),
                        'name'     => 'MTARGET_TOKEN',
                        'required' => true,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Log in'),
                ),
            ),
        );
    }

    /**
     * @return array
     */
    public function getModeSettingForm()
    {
        return array(
            'form' => array(
                'input'  => array(
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Enable the module', 'mtargetmanage'),
                        'name'    => 'MTARGET_LIVE_MODE',
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * @return array
     */
    protected function smsStatusToSwitchInput($label_prefix, $sms, $lang_id)
    {
        $switchInputs = array();

        foreach ($sms as $s) {
            $switchInputs[] = array(
                'type'    => 'switch',
                'label'   => $label_prefix . $s->event[$lang_id],
                'name'    => 'sms_' . (int) $s->id_mtarget_sms,
                'is_bool' => true,
                'values'  => array(
                    array(
                        'value' => 1,
                        'label' => $this->l('yes'),
                    ),
                    array(
                        'value' => 0,
                        'label' => $this->l('no'),
                    ),
                ),
            );
        }

        return $switchInputs;
    }

    /**
     * @return array
     */
    public function getUpdateSmsStatusForm($admin_sms, $customer_sms, $lang_id)
    {
        $inputs = array_merge(
            $this->smsStatusToSwitchInput($this->l('Alert Admin : ', 'mtargetmanage'), $admin_sms, $lang_id),
            $this->smsStatusToSwitchInput($this->l('Alert Customer : ', 'mtargetmanage'), $customer_sms, $lang_id)
        );

        return array(
            'form' => array(
                'legend'  => array(
                    'title' => $this->l('Automated SMS', 'mtargetmanage'),
                    'icon'  => 'icon-cogs',
                ),
                'class' => 'form-horizontal',
                'input'  => $inputs,
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * @return array
     */
    public function getAccountSettingsForm()
    {
        return array(
            'form' => array(
                'input'   => array(
                    array(
                        'type'  => 'text',
                        'label' => $this->l('API Key'),
                        'name'  => 'MTARGET_API_KEY',
                        'col'   => '4',
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('API Secret'),
                        'name'  => 'MTARGET_API_SECRET',
                        'col'   => '4',
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Token'),
                        'name'  => 'MTARGET_TOKEN',
                        'col'   => '4',
                    ),
                ),
                'buttons' => array(
                    array(
                        'type'  => 'submit',
                        'name'  => 'submitMtargetLogout',
                        'icon'  => 'process-icon-user icon-user',
                        'title' => $this->l('Logout', 'mtargetmanage'),
                        'class' => 'pull-right',
                    ),
                ),
                'submit'  => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * @return array
     */
    public function getSmsSettingsForm()
    {
        return array(
            'form' => array(
                'legend'  => array(
                    'title' => $this->l('Settings', 'mtargetmanage'),
                    'icon'  => 'icon-cogs',
                ),
                'input'   => array(
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Phone number of the webmaster', 'mtargetmanage'),
                        'desc'     => $this->l('(Exp : +33655555555, +33644444444,...)', 'mtargetmanage'),
                        'name'     => 'MTARGET_ADMIN_NUM',
                        'class'    => 'fixed-width-xxl',
                        'required' => true,
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->l('Name of sms sender( Visible to your recipient)', 'mtargetmanage'),
                        'desc'     => $this->l('(Limited to 11 alphanumeric characters)', 'mtargetmanage'),
                        'name'     => 'MTARGET_SENDER',
                        'class'    => 'fixed-width-xxl',
                        'required' => true,
                    ),
                ),
                'buttons' => array(
                    array(
                        'type'  => 'submit',
                        'name'  => 'submitSmsTest',
                        'icon'  => 'process-icon-check icon-check',
                        'title' => $this->l('SMS Test', 'mtarget'),
                        'class' => 'pull-right',
                    ),
                ),
                'submit'  => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * @return array
     */
    protected function getNewSegmentForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('NEW SEGMENT', 'mtargetmanage'),
                ),
                'input'  => array(
                    array(
                        'type'   => 'checkbox',
                        'label'  => $this->l('Group', 'mtargetmanage'),
                        'name'   => 'group',
                        'class'  => 'checkbox-segment',
                        'values' => array(
                            'query' => \Group::getGroups((int) $this->context->language->id),
                            'id'    => 'id_group',
                            'name'  => 'name',
                        ),
                    ),
                    array(
                        'type'   => 'checkbox',
                        'label'  => $this->l('Langue', 'mtargetmanage'),
                        'name'   => 'lang',
                        'class'  => 'checkbox-segment',
                        'values' => array(
                            'query' => \LanguageCore::getLanguages(),
                            'id'    => 'id_lang',
                            'name'  => 'iso_code',
                        ),
                    ),
                    array(
                        'type'   => 'checkbox',
                        'label'  => $this->l('Limit sending', 'mtargetmanage'),
                        'name'   => 'optin',
                        'class'  => 'checkbox-segment',
                        'values' => array(
                            'query' => array(
                                array(
                                    'id'   => 1,
                                    'name' => $this->l('Subscriber Active', 'mtargetmanage'),
                                ),
                            ),
                            'id'    => 'id',
                            'name'  => 'name',
                        ),
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Ordered ?', 'mtargetmanage'),
                        'name'    => 'order',
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'order_on',
                                'value' => 1,
                                'label' => $this->l('yes'),
                            ),
                            array(
                                'id'    => 'order_off',
                                'value' => 0,
                                'label' => $this->l('no'),
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * check if empty cart
     * @return bool
     */
    public function getCartProducts($id_cart)
    {
        $dbQuery = (new \DbQuery)->select('cp.*')->from('cart_product', 'cp')->where('cp.id_cart = '.(int) $id_cart);
        $result = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbQuery);

        return ($result) ? true : false;
    }

    /**
     * get list of customers who anniversary date after $nb_days days
     * @return array
     */
    public function getCustomersBirthdays($nb_days)
    {
        $dbQuery = (new \DbQuery)->select('c.id_customer, c.birthday')
                         ->from('customer', 'c')
                         ->where("STR_TO_DATE( CONCAT(YEAR(CURDATE()), '-', MONTH(c.birthday), '-', DAY(c.birthday) ), '%Y-%m-%d' ) = DATE_ADD(CURDATE(), INTERVAL ".(int) $nb_days." DAY)");
        $customers = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbQuery);
        $customersList = array_map(function ($c) {
            return new \Customer((int) $c['id_customer']);
        }, $customers);

        return $customersList;
    }

    public function getLatestMonth($lastMonth)
    {
        $currentDateTime = new \DateTime;
        $tabMonths       = array();

        for ($i = 0; $i < $lastMonth; $i++) {
            $tabMonths[$i] = array(
                'firstDay' => '01',
                'lastDay'  => $currentDateTime->format('t'),
                'month'    => $currentDateTime->format('m'),
                'year'     => $currentDateTime->format('Y'),
            );
            $currentDateTime->modify('-1 months');
        }

        return $tabMonths;
    }
}

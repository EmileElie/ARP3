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
 * Traite les envois de formulaires
 */
class MtargetPostProcess
{
    protected $AdminMtargetController;

    public function __construct($AdminMtargetController, $token)
    {
        $this->AdminMtargetController = $AdminMtargetController;

        $this->catchGetRequests($token);

        $this->catchAjaxRequests();

        $this->catchPostRequests($token);
    }

    /**
     * traite les requêtes Ajax
     */
    private function catchAjaxRequests()
    {
        if (\Tools::getIsset('ajax')) {
            $ajaxAction = 'ajaxProcess'.\Tools::toCamelCase(\Tools::getValue('action'));
            if (method_exists($this, $ajaxAction)) {
                $this->$ajaxAction();
            }
            die;
        }
    }

    /**
     * traite les requêtes POST
     */
    private function catchPostRequests($token)
    {
        if (\Tools::isSubmit('submitMtargetAuthentication')) {
            $this->postProcessMtargetSettings(false);
            if ($this->AdminMtargetController->is_user_connected == false) {
                return $this->redirectAdmin($token, 'configuration');
            }
            return $this->redirectAdmin($token, 'dashboard');
        }

        if (\Tools::isSubmit('submitMtargetLogout')) {
            $this->postProcessMtargetLogout();
            return $this->redirectAdmin($token, 'home');
        }

        if (\Tools::isSubmit('submitMtargetAccount')) {
            $this->postProcessMtargetSettings(true);
            return $this->redirectAdmin($token, 'myaccount');
        }

        if (\Tools::isSubmit('submitMtargetRegistration')) {
            if ($this->postProcessMtargetRegistration()) {
                return $this->redirectAdmin($token, 'dashboard');
            }
            return true;
        }

        if (\Tools::isSubmit('submitMtargetModeSetting')) {
            $this->postProcessLiveMode();
            return $this->redirectAdmin($token, 'dashboard');
        }
        if (\Tools::isSubmit('submitSmsTest')) {
            $this->postProcessSmsTest();
            return $this->redirectAdmin($token, 'sms');
        } elseif (\Tools::isSubmit('submitMtargetSmsSetting')) {
            $this->postProcessSmsSetting();
            return $this->redirectAdmin($token, 'sms');
        }
        if (\Tools::isSubmit('submitMtargetUpdateStatus')) {
            $this->postProcessUpdateSmsStatus();
            return $this->redirectAdmin($token, 'dashboard');
        }
        if (\Tools::isSubmit('submitSmsForm')) {
            $this->postProcessSmsText();
            return $this->redirectAdmin($token, 'sms', array('idsms' => \Tools::getValue('id_sms')));
        }
        if (\Tools::isSubmit('submitSmsDeleteForm')) {
            $this->postProcessDeleteSms();
            return $this->redirectAdmin($token, 'sms', array('idsms' => '0'));
        }
    }

    /**
     * Traite les requêtes GET
     */
    private function catchGetRequests($token)
    {
        if (\Tools::getValue('deleteSegment')) {
            $this->postProcessDeleteSegment((int) \Tools::getValue('deleteSegment'));
            return $this->redirectAdmin($token, 'marketing');
        }
    }

    /**
     * Alias de Tools::redirectAdmin
     */
    private function redirectAdmin($token, $action, $vars = null)
    {
        $additionnal_vars = '';
        if ($vars !== null && is_array($vars)) {
            foreach ($vars as $key => $val) {
                $additionnal_vars .= '&' . $key .'=' . $val;
            }
        }
        return \Tools::redirectAdmin('index.php?controller=AdminMtarget&token='.$token.'&action='.$action.$additionnal_vars);
    }

    /**
     * Alias Module->l()
     */
    private function l($text)
    {
        return $this->AdminMtargetController->module->l($text, 'mtargetpostprocess');
    }

    /**
     * @return bool
     */
    public function postProcessMtargetLogout()
    {
        $this->AdminMtargetController->new_user = true;
        \Configuration::updateValue('MTARGET_API_KEY', '');
        \Configuration::updateValue('MTARGET_API_SECRET', '');
        \Configuration::updateValue('MTARGET_TOKEN', '');
        \Configuration::updateValue('MTARGET_CONNECTION_STATUS', 0);
        \Configuration::updateValue('MTARGET_LIVE_MODE', 0);
    }

    /**
     * TODO : méthode à supprimer car fonctionnalité enlevée dans la v1.0.4
     */
    public function postProcessLiveMode()
    {
        \Configuration::updateValue('MTARGET_LIVE_MODE', \Tools::getValue('MTARGET_LIVE_MODE'));

        if ((int) \Configuration::get('MTARGET_LIVE_MODE') === 1) {
            return $this->AdminMtargetController->_setFlashMsg('flash_msg_success', $this->l('Live mode enabled.'));
        }

        return $this->AdminMtargetController->_setFlashMsg('flash_msg_success', $this->l('Live mode disabled'));
    }

    /**
     * Enregistrement du nouveau client vers l'API
     */
    public function postProcessMtargetRegistration()
    {
        $postData = array(
            'email'                => \Tools::getValue('account_email'),
            'pays'                 => pSQL(\Tools::getValue('account_country')),
            'motdepasse'           => pSQL(\Tools::getValue('account_password')),
            'nom'                  => pSQL(\Tools::getValue('account_lastname')),
            'prenom'               => pSQL(\Tools::getValue('account_firstname')),
            'civilite'             => pSQL(\Tools::getValue('account_civility')),
            'entreprise'           => pSQL(\Tools::getValue('account_company')),
            'siret'                => pSQL(\Tools::getValue('account_siret')),
            'mobile'               => pSQL(\Tools::getValue('account_mobile')),
            'adresse'              => pSQL(\Tools::getValue('account_address')),
            'codepostal'           => pSQL(\Tools::getValue('account_cp')),
            'ville'                => pSQL(\Tools::getValue('account_city')),
            'g-recaptcha-response' => pSQL(\Tools::getValue('g-recaptcha-response')),
        );

        if (in_array(false, $postData)) {
            $this->AdminMtargetController->_setFlashMsg('flash_msg_error', $this->l('You must fill in the required fields'));
            return false;
        }

        if (!Validate::isEmail($postData['email'])) {
            $this->AdminMtargetController->_setFlashMsg('flash_msg_error', $this->l('Invalid email'));
            return false;
        }

        /* register a new user */
        $connect = new MtargetApiConnect;
        $registerMtargetUser = $connect->registerMtargetUser($postData);

        /* If registered user */
        if ($registerMtargetUser['code'] == 200) {
            $MtargetComponent = new MtargetComponent;
            /* save API KEYS */
            \Configuration::updateValue('MTARGET_API_KEY', $registerMtargetUser['data']->API_key);
            \Configuration::updateValue('MTARGET_API_SECRET', $registerMtargetUser['data']->API_secret);
            \Configuration::updateValue('MTARGET_TOKEN', $registerMtargetUser['data']->token_connexion);
            \Configuration::updateValue('MTARGET_ADMIN_NUM', $postData['mobile']);
            $MtargetComponent->checkMtargetUser($registerMtargetUser['data']->API_key, $registerMtargetUser['data']->API_secret);
            \Configuration::updateValue('MTARGET_LIVE_MODE', 1);
            /* Create group SMS */
            $MtargetComponent->createGroupSms($postData['email']);
            $this->AdminMtargetController->_setFlashMsg('flash_msg_success', $this->l('Your account has been created, the module is ready to be used!.'));
            return true;
        }

        /* If not registered user : check fields */
        if (isset($registerMtargetUser['data']) && $registerMtargetUser['data'] == 'invalid number !') {
            $this->AdminMtargetController->_setFlashMsg('flash_msg_error', $this->l('invalid phone number'));
            return false;
        }

        if (isset($registerMtargetUser['data']->messages)) {
            foreach ($registerMtargetUser['data']->messages as $error) {
                if ($error == "This email already exists.") {
                    $this->AdminMtargetController->_setFlashMsg('flash_msg_error', $this->l("This email already exists."));
                    return false;
                }
                if ($error == "The mobile number already exists") {
                    $this->AdminMtargetController->_setFlashMsg('flash_msg_error', $this->l("The mobile number already exists"));
                    return false;
                }
                $this->AdminMtargetController->_setFlashMsg('flash_msg_error', $this->l($error));
                return false;
            }
        }
    }

    /**
     * check and save Api Keys
     */

    public function postProcessMtargetSettings($connected)
    {
        $api_key    = \Tools::getValue('MTARGET_API_KEY');
        $api_secret = \Tools::getValue('MTARGET_API_SECRET');
        $token      = \Tools::getValue('MTARGET_TOKEN');

        if (!$api_key || !$api_secret || !$token) {
            return $this->AdminMtargetController->_setFlashMsg('flash_msg_error', $this->l('You must fill in the required fields'));
        }

        $MtargetComponent = new MtargetComponent;

        $checkMtargetUser = $MtargetComponent->checkMtargetUser($api_key, $api_secret);

        if ($checkMtargetUser == false) {
            return $this->AdminMtargetController->_setFlashMsg('flash_msg_error', $this->l('Invalid parameters'));
        }

        /* save api keys */
        \Configuration::updateValue('MTARGET_API_KEY', $api_key);
        \Configuration::updateValue('MTARGET_API_SECRET', $api_secret);
        \Configuration::updateValue('MTARGET_TOKEN', $token);
        \Configuration::updateValue('MTARGET_LIVE_MODE', 1);

        /* check group template */
        $valid_group = (new MtargetApiSmsAlerting)->searchTemplateGroup();
        if ($valid_group['code'] != 200) {
            $email = $checkMtargetUser->email;
            $MtargetComponent->createGroupSms($email);
        }

        if ($connected == false) {
            return $this->AdminMtargetController->_setFlashMsg('flash_msg_success', $this->l('The module is ready for use!.'));
        }
        
        return $this->AdminMtargetController->_setFlashMsg('flash_msg_success', $this->l('Information was successfully saved.'));
    }

    /**
     * Enable or disable SMS
     */
    public function postProcessUpdateSmsStatus()
    {
        $tmpValues = \Tools::getAllValues();
        $values = array();
        $nbSms = 0;
        foreach ($tmpValues as $key => $val) {
            if (preg_match("/^active_(admin|customer)_\d+$/", $key)) {
                $exploded = explode('_', $key);
                $values[] = array(
                    'id_mtarget_sms' => $exploded[2],
                    'type'           => $exploded[1],
                    'active'         => $val
                );
                $nbSms++;
            }
        }

        for ($i = 0; $i < $nbSms; $i++) {
            $sms = new MtargetSMS($values[$i]['id_mtarget_sms']);
            if ($values[$i]['type'] === 'admin') {
                $sms->setActiveAdminValue($values[$i]['active']);
            } elseif ($values[$i]['type'] === 'customer') {
                $sms->setActiveCustomerValue($values[$i]['active']);
            }
        }

        return $this->AdminMtargetController->_setFlashMsg('flash_msg_success', $this->l('Automated SMS settings saved successfully.'));
    }

    /**
     * send SMS test
     */
    public function postProcessSmsTest()
    {
        /* create template sms test */
        $sms_alerting = new MtargetApiSmsAlerting;
        $response = $sms_alerting->createTemplate(array(
            'title'    => 'sms_test',
            'sender'   => \Configuration::get('MTARGET_SENDER'),
            'content'  => 'Prestashop SMS Test',
            'editable' => 1,
        ));

        if ($response['code'] == 200) {
            /* launch campaign */
            $response_launch = $sms_alerting->launchCampaign((int) $response['data']->id, array(
                'name'          => 'sms_test',
                'numbers'       => \Configuration::get('MTARGET_ADMIN_NUM'),
                'out_of_offers' => 'send',
                'send_now'      => 1,
            ));

            if ($response_launch['code'] == 200) {
                return $this->AdminMtargetController->_setFlashMsg('flash_msg_success', $this->l('SMS sent successfully. 1 or 2 minutes are required before it is received.'));
            }

            if ($response_launch['code'] == 404) {
                return $this->AdminMtargetController->_setFlashMsg('flash_msg_error', $this->l('A problem occurred when sending the SMS'));
            }

            $link = '<a href="' . $this->link_credit . '" target="_blank">' . $this->l('here') . '</a>';

            return $this->AdminMtargetController->_setFlashMsg('flash_msg_error', $this->l('You have no credit remaining! Please top up') . $link);
        }

        return $this->AdminMtargetController->_setFlashMsg('flash_msg_error', $this->l('A problem occurred when sending the SMS'));
    }

    /**
     * save admin num and sender
     */
    public function postProcessSmsSetting()
    {
        if (!\Tools::getValue('MTARGET_ADMIN_NUM')) {
            return $this->AdminMtargetController->_setFlashMsg('flash_msg_error', $this->l('You must fill in the required fields'));
        }

        if (!\Tools::getValue('MTARGET_SENDER')) {
            return $this->AdminMtargetController->_setFlashMsg('flash_msg_error', $this->l('You must fill in the required fields'));
        }

        $admin_field = \Tools::getValue('MTARGET_ADMIN_NUM');
        if (!\Validate::isPhoneNumber($admin_field)) {
            $number_array = preg_split("/ *, */", $admin_field);
            $numbers_are_valid = true;
            foreach ($number_array as $one_number) {
                $numbers_are_valid &= \Validate::isPhoneNumber($one_number);
            }
            if (!$numbers_are_valid) {
                return $this->AdminMtargetController->_setFlashMsg('flash_msg_error', $this->l('Invalid number'));
            }
        }

        \Configuration::updateValue('MTARGET_ADMIN_NUM', $admin_field);

        if ((new MtargetComponent)->isValidSender(\Tools::getValue('MTARGET_SENDER')) == false) {
            return $this->AdminMtargetController->_setFlashMsg('flash_msg_error', $this->l('You must enter 11 alphanumeric characters'));
        }

        \Configuration::updateValue('MTARGET_SENDER', \Tools::getValue('MTARGET_SENDER'));

        return $this->AdminMtargetController->_setFlashMsg('flash_msg_success', $this->l('The new parameters have been saved.'));
    }

    /**
     * Save content SMS for admin and customer
     */
    public function postProcessSmsText()
    {
        $id = \Tools::getValue('id_sms');
        if ($id === '0') {
                $sms_obj = new MtargetSMS();
        } else {
                $sms_obj = new MtargetSMS($id);
        }

        $sms_obj->active_admin = \Tools::getValue('active_admin');
        $sms_obj->active_customer = \Tools::getValue('active_customer');
        
        $sms_obj->content_admin[(int) \Tools::getValue('lang-select')] = \Tools::getValue('admin_text');
        $sms_obj->content_customer[(int) \Tools::getValue('lang-select')] = \Tools::getValue('customer_text');

        $sms_obj->time_limit = \Tools::getValue('time_limit');

        $sms_obj->is_order = \Tools::getValue('is_order');

        if (\Tools::getValue('spec-admin-num-on')) {
            $admin_field = \Tools::getValue('spec-admin-num');
            if (!\Validate::isPhoneNumber($admin_field)) {
                $number_array = preg_split("/ *, */", $admin_field);
                $numbers_are_valid = true;
                foreach ($number_array as $one_number) {
                    $numbers_are_valid &= \Validate::isPhoneNumber($one_number);
                }
                if (!$numbers_are_valid) {
                    return $this->AdminMtargetController->_setFlashMsg('flash_msg_error', $this->l('Invalid number'));
                }
            }
            $sms_obj->order_special_admin_num = \Tools::getValue('spec-admin-num');
        } else {
            $sms_obj->order_special_admin_num = "";
        }

        if ($sms_obj->is_order && $id === '0') {
            $sms_obj->id_order_state          = \Tools::getValue('select-order-state');
            $sms_obj->event                   = 'Statut de commande';
            $sms_obj->event_name              = 'order_state';
            $sms_obj->variable                = '(Variables : #firstname#, #lastname#, #num_order#, #url#, #status#)';
        }

        if (!$sms_obj->save()) {
            return $this->AdminMtargetController->_setFlashMsg('flash_msg_error', $this->l('SMS cannot be updated.'));
        }

        return $this->AdminMtargetController->_setFlashMsg('flash_msg_success', $this->l('SMS Changes saved successfully!'));
    }

    public function postProcessDeleteSms()
    {
        $id = \Tools::getValue('id_sms');
        $sms_obj = new MtargetSMS($id);
        if (!$sms_obj->delete()) {
            return $this->AdminMtargetController->_setFlashMsg('flash_msg_error', $this->l('SMS cannot be deleted.'));
        }

        return $this->AdminMtargetController->_setFlashMsg('flash_msg_success', $this->l('SMS deleted successfully!'));
    }

    /**
     * Add a new segment
     */
    public function ajaxProcessRequestNewSegment()
    {
        $name = \Tools::getValue('segment_name');
        /* get checkbox groups values */
        $groups = \Group::getGroups((int) $this->AdminMtargetController->context->language->id);

        $langs  = \LanguageCore::getLanguages();
        $segment_groups     = array();
        $segment_groups_ids = array();
        foreach ($groups as $group) {
            $group_obj = new \Group((int) $group['id_group']);
            if (\Tools::getValue('group_' . $group['id_group']) == 'true') {
                $segment_groups_ids[] = (int) $group_obj->id;
                foreach ($langs as $lang) {
                    $segment_groups[$lang['id_lang']][] = $group_obj->name[$lang['id_lang']];
                }
            }
        }
        if ($segment_groups) {
            foreach ($langs as $lang) {
                $segment_groups[$lang['id_lang']] = implode(', ', $segment_groups[$lang['id_lang']]);
            }
        }
        if ($segment_groups_ids) {
            $segment_groups_ids = implode(', ', $segment_groups_ids);
        }
        /* get checkbox languages values */
        $segment_langs = array();
        foreach ($langs as $lang) {
            if (\Tools::getValue('lang_' . $lang['id_lang']) == 'true') {
                $segment_langs[] = $lang['id_lang'];
            }
        }
        if ($segment_langs) {
            $segment_langs = implode(', ', $segment_langs);
        }
        /* add segment */
        $segment = new MtargetSegment();
        if ($segment_langs) {
            $segment->lang = $segment_langs;
        }
        if ($segment_groups) {
            foreach ($langs as $lang) {
                $segment->group[$lang['id_lang']] = $segment_groups[$lang['id_lang']];
            }
        }
        if ($segment_groups_ids) {
            $segment->group_ids = $segment_groups_ids;
        }

        $segment->optin     = (\Tools::getValue('optin') == "false") ? 0 : 1;
        $segment->has_order = (\Tools::getValue('order') == 0) ? 0 : 1;
        $segment->reference = \Tools::strtoupper(\Tools::passwdGen(9, 'NO_NUMERIC'));
        $segment->name      = $name;

        if (!$segment->add()) {
            $res = array('errors' => true, 'description' => $this->l('SMS cannot be added.'));
        } else {
            $res = array('errors' => false);
            $this->AdminMtargetController->_setFlashMsg('flash_msg_success', $this->l('Segment successfully added.'));
        }

        die(\Tools::jsonEncode($res));
    }

    public function postProcessDeleteSegment($id_segment)
    {
        $apiMarketing = new MtargetApiSmsMarketing;
        $segment      = new MtargetSegment((int) $id_segment);
        /* delete segment from DB */
        if ($segment->deleteSegment((int) $id_segment)) {
            $listContactsGroup = $apiMarketing->getContactGroups();
            /* delete api contacts group */
            if ($listContactsGroup['code'] == 200 && !empty($listContactsGroup['data'])) {
                foreach ($listContactsGroup['data'] as $group) {
                    if ($group->name == $segment->name . '_' . $segment->reference) {
                        $apiMarketing->deleteContactsGroup((int) $group->id);
                    }
                }
            }

            return $this->AdminMtargetController->_setFlashMsg('flash_msg_success', $this->l('Segment successfully removed'));
        }

        return $this->AdminMtargetController->_setFlashMsg('flash_msg_error', $this->l('Segment cannot be removed'));
    }

    public function ajaxProcessRequestUseSegment()
    {
        $contacts_list_segment = (new MtargetManage)->renderSegmentList((int) \Tools::getValue('id_segment'));
        die(\Tools::jsonEncode($contacts_list_segment));
    }

    /**
     * retourne le nombre de contacts appartenant au segment
     */
    public function ajaxProcessRequestCountSegment()
    {
        $MtargetSegment = new MtargetSegment;
        $id_segment = (int) \Tools::getValue('id_segment');
        $nb_contacts_list_segment = $MtargetSegment->countContactsList($id_segment);

        $segment = new MtargetSegment($id_segment);

        die(\Tools::jsonEncode(array(
            'segment_name' => $segment->name . '_' . $segment->reference,
            'nb_contacts' => $nb_contacts_list_segment,
            'mlb_group_id' => (new MtargetComponent)->checkContactsGroup($id_segment),
            'nb_pages' => ((int) $nb_contacts_list_segment / 10) + 1,
            // 'segment' => $segment
        )));
    }

    /**
     * envoie les contacts du segment vers le group MLB créé
     */
    public function ajaxProcessRequestSendSegment()
    {
        $smsMarketing = new MtargetApiSmsMarketing;
        $segment = new MtargetSegment();
        $id_segment = (int) \Tools::getValue('id_segment');
        $page = (int) \Tools::getValue('page');
        $contactsList = $segment->getContactsList((int) $id_segment, $page);

        if ($contactsList) {
            $id_contacts_group = \Tools::getValue('mlb_group_id');
            $createContactResponse = array();
            foreach ($contactsList as $contact) {
                $mobile = ($contact['phone_mobile'] == '') ? $contact['phone']
                                                           : $contact['phone_mobile'];
                if ($mobile != '') {
                    $country = (new Country((int) $contact['id_country']))->iso_code;
                    $civility = (!empty($contact['id_gender'])) ? (new Gender((int) $contact['id_gender']))->name[$contact['id_lang']]
                                                            : '';
                    $post_params = array(
                        'civility' => $civility,
                        'fname' => $contact['firstname'],
                        'lname' => $contact['lastname'],
                        'mobile' => $mobile,
                        'email' => $contact['email'],
                        'birthday' => $contact['birthday'],
                        'ind' => $country,
                        'is_blacklist' => 0,
                        'updated_field  ' => 'mob',
                    );
                    $createContactResponse[] = $smsMarketing->createContact($id_contacts_group, $post_params);
                }
            }
            die(\Tools::jsonEncode(
                array(
                    'errors' => false,
                    'contactsList' => $contactsList,
                    'page' => $page,
                    'sentSegmentContacts' => count($createContactResponse),
                    'id_contacts_group' => $id_contacts_group
                )
            ));
        }

        die(\Tools::jsonEncode(
            array(
                'errors' => true,
                'errorMessage' => $this->l('No contact in the selected segment'),
                'contactsList' => $contactsList,
            )
        ));
    }

    /*
     * Toggle l'état d'un message vers actif/inactif, pour client/admin
     */
    public function ajaxProcessChangeActiveState()
    {
        $sms = new MtargetSMS(\Tools::getValue('id_sms'));
        if (\Tools::getValue('type') === 'admin') {
            $res = $sms->setActiveAdminValue(\Tools::getValue('val'));
        } elseif (\Tools::getValue('type') === 'customer') {
            $res = $sms->setActiveCustomerValue(\Tools::getValue('val'));
        }

        die(\Tools::jsonEncode(
            array(
                'errors' => (!$res)
            )
        ));
    }

    /*
     * Envoie les données pour remplir le formulaire de changement de texte de message (pour un message déjà existant)
     */
    public function ajaxProcessGetMessageText()
    {
        $sms =  new MtargetSMS(\Tools::getValue('id_sms'));

        if (!isset($sms->content_customer[\Tools::getValue('lang')])
            || !isset($sms->content_admin[\Tools::getValue('lang')])
            || !isset($sms->event[\Tools::getValue('lang')])) {
            $default = (new Mtarget)->getDefaultMessage($sms->event_name);
            $iso = \Language::getIsoById(\Tools::getValue('lang'));
        }
        if (isset($sms->content_customer[\Tools::getValue('lang')])) {
            $content_customer = $sms->content_customer[\Tools::getValue('lang')];
        } else {
            if (isset($default['mtarget_sms_lang'][$iso])) {
                $content_customer = $default['mtarget_sms_lang'][$iso]['content_customer'];
            } else {
                $content_customer = $default['mtarget_sms_lang']['en']['content_customer'];
            }
        }

        if (isset($sms->content_admin[\Tools::getValue('lang')])) {
            $content_admin = $sms->content_admin[\Tools::getValue('lang')];
        } else {
            if (isset($default['mtarget_sms_lang'][$iso])) {
                $content_admin = $default['mtarget_sms_lang'][$iso]['content_admin'];
            } else {
                $content_admin = $default['mtarget_sms_lang']['en']['content_admin'];
            }
        }

        if (isset($sms->event[\Tools::getValue('lang')])) {
            $event = $sms->event[\Tools::getValue('lang')];
        } else {
            if (isset($default['mtarget_sms_lang'][$iso])) {
                $event = $default['mtarget_sms_lang'][$iso]['event'];
            } else {
                $event = $default['mtarget_sms_lang']['en']['event'];
            }
        }

        $order_state_name = '';
        if ($sms->is_order === '1') {
            $order_state_name = (new \OrderState($sms->id_order_state))->name[\Tools::getValue('lang')];
        }

        die(\Tools::jsonEncode(
            array(
                'content_customer'        => $content_customer,
                'content_admin'           => $content_admin,
                'active_customer'         => ($sms->active_customer),
                'active_admin'            => ($sms->active_admin),
                'variable'                => ($sms->variable),
                'event_name'              => ($sms->event_name),
                'time_limit'              => ($sms->time_limit),
                'event'                   => $event,
                'is_order'                => ($sms->is_order),
                'order_special_admin_num' => ($sms->order_special_admin_num),
                'order_state_name'        => $order_state_name,
            )
        ));
    }

    /*
     * Envoie les données pour la création d'un nouveau message lié à un statut de commande
     */
    public function ajaxProcessFetchCommandStates()
    {
        $query = 'SELECT id_order_state, name
                  FROM '._DB_PREFIX_.'order_state_lang
                  WHERE id_lang = '.(int)\Tools::getValue('lang').'
                  AND id_order_state NOT IN
                  (
                    SELECT id_order_state
                    FROM '._DB_PREFIX_.'mtarget_sms
                    WHERE is_order = 1
                  );';
        $orderStates = \Db::getInstance()->executeS($query);

        $template = (new Mtarget())->getDefaultMessage('order_state');

        $iso_code = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue((new \DbQuery)->select('iso_code')->from('lang')->where('id_lang = "'. (int)\Tools::getValue('lang') .'"'));
        if (!in_array($iso_code, array('fr', 'en', 'es', 'it'), true)) {
            $iso_code = 'en';
        }

        die(\Tools::jsonEncode(
            array(
                'variable'         => $template['mtarget_sms']['variable'],
                'event'            => $template['mtarget_sms_lang'][$iso_code]['event'],
                'content_admin'    => $template['mtarget_sms_lang'][$iso_code]['content_admin'],
                'content_customer' => $template['mtarget_sms_lang'][$iso_code]['content_customer'],
                'states'           => $orderStates
            )
        ));
    }
}

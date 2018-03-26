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
 *
 */
class MtargetComponent
{
    /**
     * get user information settings
     * @param string $api_key
     * @param string $api_secret
     */
    public function checkMtargetUser($api_key, $api_secret)
    {
        $MtargetApiConnect = new MtargetApiConnect();

        $check_user = $MtargetApiConnect->checkMtargetKeys($api_key, $api_secret);

        if (isset($check_user['data']) && $check_user['code'] == 200) {
            \Configuration::updateValue('MTARGET_CONNECTION_STATUS', 1);
            return $check_user['data'];
        }

        return false;

        // return (isset($check_user['data']) && $check_user['code'] == 200);
    }

    public function getMtargetBalance($api_key, $api_secret)
    {
        if (!!$this->checkMtargetUser($api_key, $api_secret)) {
            return (new MtargetApiConnect)->getMtargetBalance()['data'];
        }

        return false;
    }

    public function createGroupSms($smsGroupName)
    {
        $MtargetApiSmsAlerting = new MtargetApiSmsAlerting;
        /* check if user has a group template */
        $listTemplateGroups = $MtargetApiSmsAlerting->listTemplateGroups();
        if ($listTemplateGroups['code'] == 200) {
            if (isset($listTemplateGroups['data'][0]->id)) {
                return \Configuration::updateValue('MTARGET_TEMPLATE_GROUP', (int) $listTemplateGroups['data'][0]->id);
            }
            /* if user has not a group : create group */
            $group_response = $MtargetApiSmsAlerting->createTemplateGroup(array('name' => 'Prestashop Group '.$smsGroupName));
            if ($group_response['code'] == 200) {
                return \Configuration::updateValue('MTARGET_TEMPLATE_GROUP', (int) $group_response['data']->id);
            }
        }

        return false;
    }

    /**
     * Get SMS Statistics per month
     */
    public function getMtargetStatisticsByMonths()
    {
        $MtargetApiSmsMarketing = new MtargetApiSmsMarketing;
        $MtargetManage          = new MtargetManage;
        /* check if user has campaigns SMS */
        $campaigns_list = $MtargetApiSmsMarketing->getCampaignsList(array());

        $tab_stat = array();
        if ($campaigns_list['code'] == 200) {
            $tab_months = $MtargetManage->getLatestMonth(12);
            /* get statistics for each month */
            foreach ($tab_months as $key => $month) {
                $nbr_sms = 0;
                $campaigns_list_month = $MtargetApiSmsMarketing->getCampaignsList(array(
                    'from' => $month['year'].'-'.$month['month'].'-'.$month['firstDay'],
                    'to'   => $month['year'].'-'.$month['month'].'-'.$month['lastDay'],
                ));
                if ($campaigns_list_month['code'] == 200) {
                    $tab_stat[$key]['year']    = (int) $month['year'];
                    $tab_stat[$key]['month']   = (int) $month['month'] - 1;
                    if (!empty($campaigns_list_month['data'])) {
                        foreach ($campaigns_list_month['data'] as $campaign) {
                            $nbr_sms += $campaign->nbr_sms;
                        }
                    }
                    $tab_stat[$key]['sms'] = $nbr_sms;
                }
            }
        }

        return $tab_stat;
    }

    /**
     * Get Statistics by SMS types
     */
    public function getMtargetStatisticsByType()
    {
        $MtargetApiSmsMarketing = new MtargetApiSmsMarketing;
        $MtargetManage          = new MtargetManage;
        /* get last 12 months */
        $tab_months = $MtargetManage->getLatestMonth(12);
        /* get all campaigns sms */
        $campaigns_list = $MtargetApiSmsMarketing->getCampaignsList(array(
            'from' => $tab_months[11]['year'].'-'.$tab_months[11]['month'].'-'.$tab_months[11]['firstDay'],
            'to'   => $tab_months[0]['year'].'-'.$tab_months[0]['month'].'-'.$tab_months[0]['lastDay'],
        ));

        $total_sms = 0;
        /* initialize percent values */
        $percent_account        = 0;
        $percent_new_order      = 0;
        $percent_product_return = 0;
        $percent_order_statut   = 0;
        $percent_cart           = 0;
        $percent_birthday       = 0;

        if ($campaigns_list['code'] == 200) {
            /* initialize the list of counters */
            $counter_account        = 0;
            $counter_new_order      = 0;
            $counter_product_return = 0;
            $counter_cart           = 0;
            $counter_birthday       = 0;
            $counter_order_statut   = 0;

            /* sms list
             * first string is the one used currently, the others are legacy texts that we keep in order not to make SMS stats disappear on upgrade
             */
            $sms_account        = array('new_account',      'Nouveau compte',       'New account',      'Nuovo account',            'Nueva Cuenta'                  );
            $sms_order          = array('new_order',        'Nouvelle commande',    'New order',        'Nuovo ordine',             'Nueva pedida', 'Nueva Compra'  );
            $sms_order_return   = array('new_order_return', 'Retour produit',       'Product return',   'Restituzione prodotto',    'Reclamo'                       );
            $sms_cart           = array('cart',             'Relance panier',       'Abandoned Carts',  'Carrello abbandonato',     'Cesta abandonada'              );
            $sms_birthday       = array('birthday',         'Relance panier',       'Abandoned Carts',  'Carrello abbandonato',     'Cesta abandonada'              );
            $sms_order_statut   = array('order_state',      'Statut commande',      'Order status',     'Stato dell\'ordine',       'Estado del pedido'             );

            if (isset($campaigns_list['data'])) {
                foreach ($campaigns_list['data'] as $campaign) {
                    /* statistics new account */
                    if (@in_array($campaign->name, $sms_account)) {
                        $counter_account++;
                    }
                    /* statistics new order */
                    if (@in_array($campaign->name, $sms_order)) {
                        $counter_new_order++;
                    }
                    /* statistics product return */
                    if (@in_array($campaign->name, $sms_order_return)) {
                        $counter_product_return++;
                    }
                    /* statistics cart */
                    if (@in_array($campaign->name, $sms_cart)) {
                        $counter_cart++;
                    }
                    /* statistics birthday */
                    if (@in_array($campaign->name, $sms_birthday)) {
                        $counter_birthday++;
                    }
                    /* statistics order statut */
                    if (@in_array($campaign->name, $sms_order_statut)) {
                        $counter_order_statut++;
                    }
                }
            }
            /* calculate sms total */
            $total_sms = array_sum(array(
                (int) $counter_account,
                (int) $counter_new_order,
                (int) $counter_product_return,
                (int) $counter_order_statut,
                (int) $counter_cart,
                (int) $counter_birthday,
            ));

            if ($total_sms) {
                $percent_account        = ($counter_account * 100) / $total_sms;
                $percent_new_order      = ($counter_new_order * 100) / $total_sms;
                $percent_product_return = ($counter_product_return * 100) / $total_sms;
                $percent_order_statut   = ($counter_order_statut * 100) / $total_sms;
                $percent_cart           = ($counter_cart * 100) / $total_sms;
                $percent_birthday       = ($counter_birthday * 100) / $total_sms;
            }
        }

        return array(
            'empty_stat'             => ($total_sms > 0) ? 0 : 1,
            'percent_account'        => (float) number_format($percent_account, 2, '.', ' '),
            'percent_new_order'      => (float) number_format($percent_new_order, 2, '.', ' '),
            'percent_product_return' => (float) number_format($percent_product_return, 2, '.', ' '),
            'percent_order_statut'   => (float) number_format($percent_order_statut, 2, '.', ' '),
            'percent_cart'           => (float) number_format($percent_cart, 2, '.', ' '),
            'percent_birthday'       => (float) number_format($percent_birthday, 2, '.', ' '),
        );
    }

    private function _toPercent($value, $onTotal)
    {
        $value = ($value * 100) / $onTotal;
        return (float) number_format($value, 2, '.', ' ');
    }

    /**
     * check if user has a contacts group id in MTARGET
     * @param string $id_segment
     * @return int
     */
    public function checkContactsGroup($id_segment)
    {
        $smsMarketing = new MtargetApiSmsMarketing;
        $segment = new MtargetSegment((int) $id_segment);
        /* get list contacts group for user in Mtarget */
        $listContactsGroup = $smsMarketing->getContactGroups();
        if ($listContactsGroup['code'] == 200 && isset($listContactsGroup['data'])) {
            foreach ($listContactsGroup['data'] as $group) {
                /* if user has a contacts group :  return group id */
                if ($group->name == $segment->name . '_' . $segment->reference) {
                    return (int) $group->id;
                }
            }
            /* if group not exist : create group segment */
            $group_response = $smsMarketing->createContactsGroup(array(
                'name' => $segment->name . '_' . $segment->reference,
                'description' => 'Prestashop Contacts Group',
            ));
            if ($group_response['code'] == 200) {
                return (int) $group_response['data']->id;
            }
        }
    }

    /**
     * @param string $sender
     * @return bool
     */
    public function isValidSender($sender)
    {
        if (ctype_digit($sender) == true || \Tools::strlen($sender) > 11) {
            return false;
        }

        return true;
    }

    /**
     * Delete campaign for Abandoned Carts if order is validated
     * @param int id_cart
     */
    public function deleteCampaign($id_cart)
    {
        /* get id_campaign if exist */
        $dbQuery = new \DbQuery();
        $dbQuery->select('id_campaign_admin, id_campaign_customer');
        $dbQuery->from('mtarget_cart');
        $dbQuery->where('id_cart = ' . (int) $id_cart);
        $ids_campaigns = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbQuery);

        if ($ids_campaigns !== false) {
            $sms_alerting = new MtargetApiSmsAlerting;
            if ($ids_campaigns[0]['id_campaign_admin'] !== "0") {
                $response_admin = $sms_alerting->deleteCampaign((int) $ids_campaigns[0]['id_campaign_admin']);
            } else {
                //Si y'a rien à supprimer, on considère que ça a marché
                $response_admin = array();
                $response_admin['code'] = 200;
            }

            if ($ids_campaigns[0]['id_campaign_customer'] !== "0") {
                $response_customer = $sms_alerting->deleteCampaign((int) $ids_campaigns[0]['id_campaign_customer']);
            } else {
                //Si y'a rien à supprimer, on considère que ça a marché
                $response_customer = array();
                $response_customer['code'] = 200;
            }

            if ($response_admin['code'] === 200 && $response_customer['code'] === 200) {
                \Db::getInstance()->delete('mtarget_cart', 'id_cart = ' . (int) $id_cart);
            } elseif ($response_admin['code'] === 200 && $response_customer['code'] !== 200) {
                \Db::getInstance()->update('mtarget_cart', array('id_campaign_admin', '0'), 'id_cart = ' . (int) $id_cart);
                \Logger::addLog('MTG: Looks like customer\'s cart alert couldn\'t be deleted. Response from SMS gateway: ' . print_r($response_customer, true));
            } elseif ($response_admin['code'] !== 200 && $response_customer['code'] === 200) {
                \Db::getInstance()->update('mtarget_cart', array('id_campaign_customer', '0'), 'id_cart = ' . (int) $id_cart);
                \Logger::addLog('MTG: Looks like admin\'s cart alert couldn\'t be deleted. Response SMS gateway: ' . print_r($response_admin, true));
            } else {
                \Logger::addLog('MTG: Looks like customer\'s cart alert couldn\'t be deleted. Response from SMS gateway: ' . print_r($response_customer, true));
                \Logger::addLog('MTG: Looks like admin\'s cart alert couldn\'t be deleted. Response SMS gateway: ' . print_r($response_admin, true));
            }
        }
    }
}

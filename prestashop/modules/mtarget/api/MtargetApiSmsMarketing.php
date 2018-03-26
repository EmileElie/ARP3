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
class MtargetApiSmsMarketing
{
    public function __construct()
    {
        $this->api_key = \Configuration::get('MTARGET_API_KEY');
        $this->api_secret = \Configuration::get('MTARGET_API_SECRET');
        $this->group_contacts = \Configuration::get('MTARGET_GROUP_CONTACTS');
        $this->token = "824d4611aa08e8bde5148c5cb9b6b73f";
        $this->httpUser = new MtargetApi;
    }

    public function createContactsGroup($data = array())
    {
        return $this->httpUser->request(
            'groups',
            'POST',
            \Tools::jsonEncode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            array(
                'api_key'    => $this->api_key,
                'api_secret' => $this->api_secret,
            )
        );
    }

    public function deleteContactsGroup($id_group)
    {
        return $this->httpUser->request(
            "groups/".(int) $id_group,
            'DELETE',
            '',
            array(
                'api_key'    => $this->api_key,
                'api_secret' => $this->api_secret,
            )
        );
    }

    public function searchContactsGroup($id_contacts_group)
    {
        return $this->httpUser->request(
            "groups/".(int) $id_contacts_group,
            'GET',
            '',
            array(
                'api_key'    => $this->api_key,
                'api_secret' => $this->api_secret,
            )
        );
    }

    public function getContactGroups()
    {
        return $this->httpUser->request(
            "groups",
            'GET',
            '',
            array(
                'api_key'    => $this->api_key,
                'api_secret' => $this->api_secret,
            )
        );
    }

    public function createContact($id_contacts_group, $data = array())
    {
        return $this->httpUser->request(
            "groups/".(int) $id_contacts_group."/contacts",
            'POST',
            \Tools::jsonEncode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            array(
                'api_key'    => $this->api_key,
                'api_secret' => $this->api_secret,
            )
        );
    }

    public function getCampaignsList($data = array())
    {
        $campaigns_url = "campaigns/sms";
        $httpRequest = "GET";
        $params = array(
            'api_key'    => $this->api_key,
            'api_secret' => $this->api_secret,
        );
        if (!empty($data)) {
            $params['from'] = $data['from'];
            $params['to'] = $data['to'];
        }
        $response = $this->httpUser->request($campaigns_url, $httpRequest, '', $params);

        return $response;
    }
}

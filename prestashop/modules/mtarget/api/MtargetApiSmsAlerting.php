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
class MtargetApiSmsAlerting
{
    protected $api_key;

    protected $api_secret;

    protected $template_group;

    protected $token;

    protected $httpUser;

    public function __construct()
    {
        $this->api_key = \Configuration::get('MTARGET_API_KEY');
        $this->api_secret = \Configuration::get('MTARGET_API_SECRET');
        $this->template_group = \Configuration::get('MTARGET_TEMPLATE_GROUP');
        $this->token = "824d4611aa08e8bde5148c5cb9b6b73f";
        $this->httpUser = new MtargetApi;
    }

    public function createTemplateGroup($data = array())
    {
        return $this->httpUser->request(
            "objectifs/sms",
            "POST",
            \Tools::jsonEncode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            array(
                'api_key'    => $this->api_key,
                'api_secret' => $this->api_secret,
            )
        );
    }

    public function searchTemplateGroup()
    {
        return $this->httpUser->request(
            "objectifs/".\Configuration::get('MTARGET_TEMPLATE_GROUP'),
            'GET',
            '',
            array(
                'api_key'    => $this->api_key,
                'api_secret' => $this->api_secret,
            )
        );
    }

    public function listTemplateGroups()
    {
        return $this->httpUser->request(
            "objectifs/sms",
            'GET',
            '',
            array(
                'api_key'    => $this->api_key,
                'api_secret' => $this->api_secret,
            )
        );
    }

    public function createTemplate($data = array())
    {
        return $this->httpUser->request(
            "objectifs/".(int) $this->template_group."/models",
            'POST',
            \Tools::jsonEncode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            array(
                'api_key'    => $this->api_key,
                'api_secret' => $this->api_secret,
            )
        );
    }

    public function launchCampaign($model_id, $data = array())
    {
        return $this->httpUser->request(
            "campaigns/sms/".(int) $model_id,
            'POST',
            \Tools::jsonEncode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            array(
                'api_key'    => $this->api_key,
                'api_secret' => $this->api_secret,
            )
        );
    }

    public function deleteCampaign($campaign_id)
    {
        return $this->httpUser->request(
            "campaigns/".(int) $campaign_id,
            'DELETE',
            '',
            array(
                'api_key'    => $this->api_key,
                'api_secret' => $this->api_secret,
            )
        );
    }
}

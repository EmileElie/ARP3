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
 * class MtargetApiConnect
 */
class MtargetApiConnect
{
    public function __construct()
    {
        $this->api_key = \Configuration::get('MTARGET_API_KEY');
        $this->api_secret = \Configuration::get('MTARGET_API_SECRET');
        $this->token_connexion = \Configuration::get('MTARGET_TOKEN');
        $this->token = "824d4611aa08e8bde5148c5cb9b6b73f";
        $this->httpUser = new MtargetApi;
    }

    public function registerMtargetUser($data = array())
    {
        return $this->httpUser->request(
            "users",
            "POST",
            \Tools::jsonEncode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            array(
                'token' => $this->token,
            )
        );
    }

    public function checkMtargetKeys($api_key, $api_secret)
    {
        if (!empty($api_key)) {
            $this->api_key = $api_key;
        }
        if (!empty($api_secret)) {
            $this->api_secret = $api_secret;
        }

        return $this->httpUser->request(
            'check',
            'GET',
            '',
            array(
                'api_key'    => $this->api_key,
                'api_secret' => $this->api_secret,
            )
        );
    }

    public function getMtargetBalance()
    {
        $response = $this->httpUser->request(
            'balance',
            'GET',
            '',
            array(
                'api_key'    => $this->api_key,
                'api_secret' => $this->api_secret,
            )
        );

        return ($response['code'] == 200) ? $response['data'] : false;
    }
}

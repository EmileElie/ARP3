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
 * Class MtargetSegment
 */
class MtargetSegment extends \ObjectModel
{
    /**
     * @var
     */
    public $id_mtarget_segment;
    /**
     * @var
     */
    public $reference;
    /**
     * @var
     */
    public $name;
    /**
     * @var
     */
    public $lang;
    /**
     * @var
     */
    public $group_ids;
    /**
     * @var
     */
    public $optin;
    /**
     * @var
     */
    public $has_order;
    /**
     * @var
     */
    public $group;
    /**
     * @var array
     */
    public static $definition = array(
        'table' => 'mtarget_segment',
        'primary' => 'id_mtarget_segment',
        'multilang' => true,
        'fields' => array(
            'reference' => array(
                'type' => self::TYPE_STRING,
                'required' => true,
                'size' => 9,
            ),
            'name' => array(
                'type' => self::TYPE_STRING,
                'required' => false,
                'size' => 20,
            ),
            'lang' => array(
                'type' => self::TYPE_STRING,
                'required' => false,
                'size' => 80,
            ),
            'group_ids' => array(
                'type' => self::TYPE_STRING,
                'required' => false,
                'size' => 80,
            ),
            'optin' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ),
            'has_order' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ),
            'group' => array(
                'type' => self::TYPE_STRING,
                'required' => false,
                'size' => 80,
                'lang' => true,
            ),
        ),
    );

    /**
     * @return array
     */
    public static function getList()
    {
        $dbQuery = new \DbQueryCore;
        $dbQuery->select('s.*');
        $dbQuery->from('mtarget_segment', 's');
        $result = \Db::getInstance(_PS_USE_SQL_SLAVE_)
            ->executeS($dbQuery);
        $segmentList = array();
        foreach ($result as $segment) {
            $tmp = new MtargetSegment((int) $segment['id_mtarget_segment']);
            $row_langs = '';
            if ($tmp->lang) {
                $langs = explode(",", $tmp->lang);
                foreach ($langs as $lang) {
                    $row_lang = new \LanguageCore((int) $lang);
                    $row_langs[] = $row_lang->iso_code;
                }
                $row_langs = implode(', ', $row_langs);
            }
            $tmp->lang = $row_langs;
            $segmentList[] = $tmp;
        }

        return $segmentList;
    }

    /**
     * @return bool
     */
    public static function deleteSegment($id_segment)
    {
        if (\Db::getInstance()->delete('mtarget_segment', 'id_mtarget_segment = ' . (int) $id_segment) &&
            \Db::getInstance()->delete('mtarget_segment_lang', 'id_mtarget_segment = ' . (int) $id_segment)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return array
     */
    public static function getContactsList($id_segment, $page = 1, $nb_rows_per_page = 10)
    {
        $segment = new MtargetSegment((int) $id_segment);
        $dbQuery = new \DbQueryCore;
        $dbQuery->select('c.id_customer, c.id_gender, c.id_lang, c.firstname, c.lastname, c.email, c.birthday, c.optin, addr.phone_mobile, addr.phone, addr.id_country');
        $dbQuery->from('customer', 'c');
        if ($segment->has_order == 1) {
            $dbQuery->leftJoin('orders', 'o', 'o.`id_customer` = c.`id_customer`');
        }
        if ($segment->group_ids) {
            $dbQuery->leftJoin('customer_group', 'cg', 'cg.`id_customer` = c.`id_customer`');
        }
        $dbQuery->innerJoin('address', 'addr', 'addr.`id_customer` = c.`id_customer`');

        if ($segment->lang) {
            $langs = explode(",", $segment->lang);
            $clause = '';
            foreach ($langs as $key => $lang) {
                if ($key > 0) {
                    $clause .= '|| c.id_lang = ' . (int) $lang;
                }
            }
            $dbQuery->where('c.id_lang = ' . (int) $langs[0] . $clause);
        }
        $dbQuery->where('c.optin =' . (int) $segment->optin);
        if ($segment->has_order == 1) {
            $dbQuery->where('o.`id_customer` = c.`id_customer`');
        }

        if ($segment->group_ids) {
            $dbQuery->where(
                '(cg.id_group IN('.implode(",", array_map('intval', explode(",", $segment->group_ids))).')
                OR c.id_default_group IN('.implode(",", array_map('intval', explode(",", $segment->group_ids))).')) '
            );
        }
        $dbQuery->groupBy('c.id_customer');

        $queryOffset = ($page - 1) * $nb_rows_per_page;
        $dbQuery->limit((int) $nb_rows_per_page, (int) $queryOffset);

        $result = \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($dbQuery);

        return $result;
    }

    /**
     * @return array
     */
    public static function countContactsList($id_segment)
    {
        $segment = new MtargetSegment((int) $id_segment);
        $dbQuery = new \DbQueryCore();

        $dbQuery->select('COUNT(c.id_customer) nb');
        $dbQuery->from('customer', 'c');
        if ($segment->has_order == 1) {
            $dbQuery->leftJoin('orders', 'o', 'o.`id_customer` = c.`id_customer`');
        }
        if ($segment->group_ids) {
            $dbQuery->leftJoin('customer_group', 'cg', 'cg.`id_customer` = c.`id_customer`');
        }
        $dbQuery->innerJoin('address', 'addr', 'addr.`id_customer` = c.`id_customer`');

        if ($segment->lang) {
            $langs = explode(",", $segment->lang);
            $clause = '';
            foreach ($langs as $key => $lang) {
                if ($key > 0) {
                    $clause .= '|| c.id_lang = ' . (int) $lang;
                }
            }
            $dbQuery->where('c.id_lang = ' . (int) $langs[0] . $clause);
        }
        $dbQuery->where('c.optin =' . (int) $segment->optin);
        if ($segment->has_order == 1) {
            $dbQuery->where('o.`id_customer` = c.`id_customer`');
        }

        if ($segment->group_ids) {
            $dbQuery->where(
                '(cg.id_group IN (' . implode(",", array_map('intval', explode(",", $segment->group_ids))) . ')
                OR c.id_default_group IN (' . implode(",", array_map('intval', explode(",", $segment->group_ids))) . ')) '
            );
        }

        $dbQuery->groupBy('c.id_customer');

        \Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($dbQuery);

        return \Db::getInstance(_PS_USE_SQL_SLAVE_)->numRows();
    }
}

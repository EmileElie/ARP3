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

//GSM 7 - caractère standard (et chaine vide) (avec du double escapage pour que ce soit correctement escapé une fois utilisé en regex)
var gsmCharset = "A-Za-z0-9æäàåèéùìñòöøüÅÄÇØÖÑÜÉÆΔΦΓΛΩΠΨΣΘΞß!¡?¿'¥$£§/\\r\\n \"()@_#¤%&*+\\-=<>:;.,";

//GSM 7 - caractères doubles (avec du double escapage pour que ce soit correctement escapé une fois utilisé en regex)
var gsmDoubleCharset = "{}[\\]€~|^\\\\";

//Caractères modifiés par Ludo pour prise en compte
var replacedParLudoCharset = "’«»ËÊÈêëãáâÃÁÂÀíîïÍÎÏÌúûÚÛÙõóôÕÓÒÔçćĆŁłŃńŚśŹŻźż" + String.fromCharCode(160);
var replacedParLudoArray = [
	["’",                       "'"],
	["«",                       "\""],
	["»",                       "\""],
	["ËÊÈ",                     "E"],
	["êë",                      "e"],
	["ãáâ",                     "a"],
	["ÃÁÂÀ",                    "A"],
	["íîï",                     "i"],
	["ÍÎÏÌ",                    "I"],
	["úû",                      "u"],
	["ÚÛÙ",                     "U"],
	["õóô",                     "o"],
	["ÕÓÒÔ",                    "O"],
	["ç",                       "Ç"],
	[String.fromCharCode(160),  " "], //espace insécable devient un espace normal.
	["ć",                       "c"],
	["Ć",                       "C"],
	["Ł",                       "L"],
	["ł",                       "l"],
	["Ń",                       "N"],
	["ń",                       "n"],
	["Ś",                       "S"],
	["ś",                       "s"],
	["ŹŻ",                      "Z"],
	["źż",                      "z"]
];

// Retourne true si tout les chars sont dans l'alphabet GSM (ou chaine vide) - permet de ne pas chercher plus loin si tout est valide.
function isGsmValid(str){
	return new RegExp("^["+gsmCharset+"]+$|^$").test(str);
}

/*
	Les trois fonctions suivantes retournent les caractères au lieu de booléens, pour pouvoir compter/afficher/remplacer/etc

	Avant de les afficher, les passer dans un truc pour dédoublonner (ex: String.prototype.concat(...new Set(string)))
	pour éviter d'avoir écrit "Compte double: €€€€€€€€" si il y a 8 symboles euro dans le message
*/

// Retourne les caractères qui sont dans l'alphabet GSM étendu (comptent double) - (sans les tables nationales)
function getGsmDoubleChars(str){
	var regexp = new RegExp("[^"+gsmDoubleCharset+"]", "g");
	return str.replace(regexp, '');
}

// Retourne les caractères qui sont remplacés par d'autres
function getReplacedByLudoChars(str){
	var regexp = new RegExp("[^"+replacedParLudoCharset+"]", "g");
	return str.replace(regexp, '');
}

// Retourne les caractères non supportés
function getInvalidChars(str){
	var gsmCharsRegex = new RegExp("["+gsmCharset+"]", "g");
	var gsmDoubleRegex = new RegExp("["+gsmDoubleCharset+"]", "g");
	var replacedRegex = new RegExp("["+replacedParLudoCharset+"]", "g");
	return str.replace(gsmCharsRegex, '').replace(gsmDoubleRegex, '').replace(replacedRegex, '');
}

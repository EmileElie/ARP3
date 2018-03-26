<?php /*%%SmartyHeaderCode:147875ab6d556320e37-80154717%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'bcf8c458e1fa4e87e9f25198a69e28709f83a340' => 
    array (
      0 => 'C:\\wamp64\\www\\prestashop\\themes\\default-bootstrap\\modules\\blockmyaccountfooter\\blockmyaccountfooter.tpl',
      1 => 1517242832,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '147875ab6d556320e37-80154717',
  'variables' => 
  array (
    'link' => 0,
    'returnAllowed' => 0,
    'voucherAllowed' => 0,
    'HOOK_BLOCK_MY_ACCOUNT' => 0,
    'is_logged' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_5ab6d556461570_43668094',
  'cache_lifetime' => 31536000,
),true); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5ab6d556461570_43668094')) {function content_5ab6d556461570_43668094($_smarty_tpl) {?>
<!-- Block myaccount module -->
<section class="footer-block col-xs-12 col-sm-4">
	<h4><a href="http://localhost/prestashop/mon-compte" title="Gérer mon compte client" rel="nofollow">Mon compte</a></h4>
	<div class="block_content toggle-footer">
		<ul class="bullet">
			<li><a href="http://localhost/prestashop/historique-commandes" title="Mes commandes" rel="nofollow">Mes commandes</a></li>
						<li><a href="http://localhost/prestashop/avoirs" title="Mes avoirs" rel="nofollow">Mes avoirs</a></li>
			<li><a href="http://localhost/prestashop/adresses" title="Mes adresses" rel="nofollow">Mes adresses</a></li>
			<li><a href="http://localhost/prestashop/identite" title="Gérer mes informations personnelles" rel="nofollow">Mes informations personnelles</a></li>
			<li><a href="http://localhost/prestashop/reduction" title="Mes bons de réduction" rel="nofollow">Mes bons de réduction</a></li>			
            <li><a href="http://localhost/prestashop/?mylogout" title="Déconnexion" rel="nofollow">Déconnexion</a></li>		</ul>
	</div>
</section>
<!-- /Block myaccount module -->
<?php }} ?>

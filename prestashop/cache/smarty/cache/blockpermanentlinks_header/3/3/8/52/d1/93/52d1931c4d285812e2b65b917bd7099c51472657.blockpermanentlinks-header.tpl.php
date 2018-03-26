<?php /*%%SmartyHeaderCode:320555ab6d55492eeb5-58621041%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '52d1931c4d285812e2b65b917bd7099c51472657' => 
    array (
      0 => 'C:\\wamp64\\www\\prestashop\\modules\\blockpermanentlinks\\blockpermanentlinks-header.tpl',
      1 => 1521818565,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '320555ab6d55492eeb5-58621041',
  'variables' => 
  array (
    'link' => 0,
    'come_from' => 0,
    'meta_title' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.19',
  'unifunc' => 'content_5ab6d5549a2857_37738092',
  'cache_lifetime' => 31536000,
),true); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5ab6d5549a2857_37738092')) {function content_5ab6d5549a2857_37738092($_smarty_tpl) {?>
<!-- Block permanent links module HEADER -->
<ul id="header_links">
	<li id="header_link_contact"><a href="http://localhost/prestashop/nous-contacter" title="contact">contact</a></li>
	<li id="header_link_sitemap"><a href="http://localhost/prestashop/plan-site" title="plan du site">plan du site</a></li>
	<li id="header_link_bookmark">
		<script type="text/javascript">writeBookmarkLink('http://localhost/prestashop/', 'Arturito', 'favoris');</script>
	</li>
</ul>
<!-- /Block permanent links module HEADER -->
<?php }} ?>

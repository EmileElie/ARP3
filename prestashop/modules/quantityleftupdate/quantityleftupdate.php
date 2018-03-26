<?php 

if (!defined('_PS_VERSION_')) 
{
	exit;
}


class QuantityLeftUpdate extends Module
{
	public function __construct()
	{
		$this->name = 'QuantityLeftUpdate';
		$this->tab = 'emailing';
		$this->version = '1.0';
		$this->author = 'Emile Youssef';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array(
			'min' => '1.6',
			'max' => '1.6.99.99'
		);
		$this->bootstrap = false;

		parent::__construct();

		$this->displayName = 'Quantity Warner';
		$this->description = 'Send amount left of a product to the owner each time this item get modified.';

		$this->confirmUninstall = 'Are you sure you want to uninstall?';

		if (!Configuration::get('QUANTITYLEFTUPDATE')) 
		{
			$this->warning = 'No name provided';		
		}
	}

	public function install()
	{
		$success = parent::install()
			&& Configuration::updateValue('QUANTITYLEFTUPDATE_EMAIL', '')
			&& $this->registerHook('actionUpdateQuantity');

		return $success;
	}

	public function uninstall()
	{
		return Configuration::deleteByName('QUANTITYLEFTUPDATE_EMAIL') && parent::uninstall();
	}

	public function hookActionUpdateQuantity($params)
	{
		$id_product = (int)$params['id_product'];
		$id_product_attribute = (int)$params['id_product_attribute'];
		$quantity = (int)$params['quantity'];
		$context = Context::getContext();
		$id_lang = (int)$context->language->id;
		$product_name = Product::getProductName($id_product, $id_product_attribute, $id_lang);
		$template_vars = array(
			'productname' => $product_name,
			'quantity' => $quantity
		);	
		$email = Configuration::get('QUANTITYLEFTUPDATE_EMAIL');

		if(defined('QUANTITYLEFTUPDATE_EMAIL'))
		{
			Mail::Send(
				$id_lang,
				'quantity_left_update',
				Mail::l('Modification du stock', $id_lang),
				$template_vars;
				$email,
				null,
				null,
				null,
				null,
				null,
				dirname(__FILE__).'/mails/'
			);
		}
	}

	public function renderForm()
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => 'Paramètres',
					'icon' => 'icon-cogs'
				),
				'input' => array(
					'type' => 'text',
					'label' => 'Email',
					'name' => 'QUANTITYLEFTUPDATE_EMAIL',
					'desc' => 'Enter the email of the owner'
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->module = $this;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitMailAlertConfiguration';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
			.'&configure='.$this->name
			.'&tab_module='.$this->tab
			.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);
		return $helper->generateForm(array($fields_form);
	}

	public function getConfigFieldsValues()
	{
		return array(
			'QUANTITYLEFTUPDATE_EMAIL' => Tools::getValue('QUANTITYLEFTUPDATE_EMAIL', Configuration::get('QUANTITYLEFTUPDATE_EMAIL')),
		);
	}
}


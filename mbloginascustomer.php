<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class MBLoginAsCustomer extends Module
{

    public function __construct()
    {
        $this->name = 'mbloginascustomer';
        $this->tab = 'others';
        $this->version = '1.0.0';
        $this->author = 'Mateusz Bartocha bestcoding.net';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Login As Customer');
        $this->description = $this->l('Module allows administrators to login as customer using button on the customer preview page.');
    }

    public function install()
    {
        return (
            !parent::install()
            || !$this->registerHook('displayAdminCustomersView')
            || !$this->registerHook('displayAdminCustomers')
            || !$this->installTab()
        );
    }

    protected function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'LoginAsCustomer';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $tab->name[$lang['id_lang']] = 'LoginAsCustomer';
        $tab->id_parent = 99999;
        $tab->module = $this->name;
        return $tab->add();
    }

    public function hookDisplayAdminCustomersView(): string
    {
        return $this->renderLoginAsButton((int)Tools::getValue('id_customer'));
    }

    public function hookDisplayAdminCustomers(array $params): string
    {
        return $this->renderLoginAsButton((int)$params['id_customer']);
    }

    protected function renderLoginAsButton(int $customerId): string
    {
        $customer = new Customer($customerId);
        if (Validate::isLoadedObject($customer)) {
            $this->context->smarty->assign('customerId', $customer->id);
            $this->context->smarty->assign('customerName', $customer->firstname . ' ' . $customer->lastname);
            return $this->display(__FILE__, 'views/templates/admin/login_as_customer_btn.tpl');
        }
        return '';
    }

}

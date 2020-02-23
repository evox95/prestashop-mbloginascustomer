<?php

class LoginAsCustomerController extends ModuleAdminController
{

    /**
     * @var Cookie
     */
    private $cookie;

    public function initContent()
    {
        $customer = $this->getCustomer();
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminCustomers'));
        }
        $this->prepareCookie();
        $this->loginAsCustomer($customer);
        Tools::redirectLink(__PS_BASE_URI__ . '?' . time());
    }

    protected function getCustomer()
    {
        try {
            $idCustomer = (int)Tools::getValue('id_customer');
            return new Customer($idCustomer);
        } catch (PrestaShopException $e) {
            return new Customer();
        }
    }

    protected function prepareCookie()
    {
        $cookie_lifetime = (int)Configuration::get('PS_COOKIE_LIFETIME_FO');
        if ($cookie_lifetime > 0) {
            $cookie_lifetime = time() + (max($cookie_lifetime, 1) * 3600);
        }

        $force_ssl = Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE');
        if ($this->context->shop->getGroup()->share_order) {
            $this->cookie = new Cookie(
                'ps-sg' . $this->context->shop->getGroup()->id,
                '',
                $cookie_lifetime,
                $this->context->shop->getUrlsSharedCart(),
                false,
                $force_ssl
            );
        } else {
            $domains = null;
            if ($this->context->shop->domain != $this->context->shop->domain_ssl) {
                $domains = array($this->context->shop->domain_ssl, $this->context->shop->domain);
            }
            $this->cookie = new Cookie('ps-s' . $this->context->shop->id, '', $cookie_lifetime, $domains, false, $force_ssl);
        }
    }

    protected function loginAsCustomer(Customer $customer)
    {
        $this->cookie->id_customer = (int)$customer->id;
        $this->cookie->customer_lastname = $customer->lastname;
        $this->cookie->customer_firstname = $customer->firstname;
        $this->cookie->passwd = $customer->passwd;
        $this->cookie->logged = 1;
        $this->cookie->email = $customer->email;
        $this->cookie->is_guest = $customer->isGuest();
        $this->cookie->id_cart = (int)Cart::lastNoneOrderedCart($customer->id);
        $this->cookie->temp = time();
        $this->cookie->write();

        if (!$customer->logged) {
            $customer->logged = 1;
            $customer->save();
        }
    }

}

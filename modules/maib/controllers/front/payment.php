<?php

/**
 * @since 1.0.0
 *
 * @property Maib $module
 */
class MaibPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;
        if (!$this->module->checkCurrency($cart)) {
            Tools::redirect('index.php?controller=order');
        }

        $total = sprintf(
            $this->context->getCurrentLocale()->formatPrice($cart->getOrderTotal(true, Cart::BOTH), $this->context->currency->iso_code)
        );

        $this->context->smarty->assign([
            'back_url' => $this->context->link->getPageLink('order', true, null, 'step=3'),
            'confirm_url' => $this->context->link->getModuleLink('maib', 'validation', [], true),
            'image_url' => $this->module->getPathUri() . 'maib.jpg',
            'cust_currency' => $cart->id_currency,
            'currencies' => $this->module->getCurrency((int) $cart->id_currency),
            'total' => $total,
            'this_path' => $this->module->getPathUri(),
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/',
        ]);

        $this->setTemplate('payment_execution.tpl');
    }
}
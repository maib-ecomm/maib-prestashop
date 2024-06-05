<?php

require_once __DIR__ . '/../../src/MaibApiRequest.php';

use MaibEcomm\MaibSdk\MaibApiRequest;

/**
 * @since 1.0.0
 *
 * @property Maib $module
 */
class MaibValidationModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'maib') {
                $authorized = true;
                break;
            }
        }
        if (!$authorized) {
            exit($this->module->getTranslator()->trans('This payment method is not available.', [], 'Modules.Maib.Shop'));
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $currency = $this->context->currency->iso_code;
        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);

        $lang = $this->context->language->iso_code;

        $ok_url = $this->context->link->getModuleLink('maib', 'ok', [], true);
        $fail_url = $this->context->link->getModuleLink('maib', 'fail', [], true);
        $callback_url = $this->context->link->getModuleLink('maib', 'callback', [], true);

        $description = [];
        $product_items = [];

        foreach ($cart->getProducts(false, false) as $cart_product) {
            $description[] = $cart_product["quantity"] . " x " . $cart_product["name"];

            $product_items[] = [
                "id" => (string) $cart_product["id_product"],
                "name" => $cart_product["name"],
                "price" => $cart_product["price"],
                "quantity" => (float) number_format(
                    $cart_product["quantity"],
                    1,
                    ".",
                    ""
                ),
            ];
        }

        $client_name = $customer->firstname . " " . $customer->lastname;
        $email = $customer->email;
        $phone = $customer->getSimpleAddresses($this->context->language->id)[$cart->id_address_delivery]['phone'];
        $client_ip = $this->getClientIp();

        $delivery = (float) number_format($cart->getTotalShippingCost(null, false), 2, ".", "");

        $order_id = (string) $this->getOrderLastIncrementId();

        $params = [
            "amount" => $total,
            "currency" => $currency,
            "clientIp" => $client_ip,
            "language" => $lang,
            "description" => substr(implode(", ", $description), 0, 124),
            "orderId" => $order_id,
            "clientName" => $client_name,
            "email" => $email,
            "phone" => substr($phone, 0, 40),
            "delivery" => $delivery,
            "okUrl" => $ok_url,
            "failUrl" => $fail_url,
            "callbackUrl" => $callback_url,
            "items" => $product_items,
        ];

        try {
            // Initiate Direct Payment Request to maib API
            $response = MaibApiRequest::create()->pay(
                $params,
                $this->module->getAccessToken()
            );

            if (!isset($response->payId)) {
                PrestaShopLogger::addLog(
                    'No valid response from maib API, order_id: ' . $order_id,
                    3
                );

                Tools::redirect('index.php?controller=order&step=1');
            } else {
                PrestaShopLogger::addLog(
                    'Pay endpoint response: ' . json_encode($response, JSON_PRETTY_PRINT) . ', order_id: ' . $order_id,
                    1
                );

                $order_status_id = Configuration::get('PAYMENT_MAIB_ORDER_PENDING_STATUS_ID');

                $paymentModule = Module::getInstanceByName($this->module->name);
                if ($paymentModule instanceof PaymentModule) {
                    $paymentModule->validateOrder($cart->id, (int) $order_status_id, $total, $this->module->displayName, null, [], (int) $cart->id_currency, false, $customer->secure_key);
                }

                Tools::redirect($response->payUrl);
            }
        } catch (Exception $ex) {
            PrestaShopLogger::addLog(
                'Payment error: ' . $ex->getMessage(),
                3
            );

            Tools::redirect('index.php?controller=order&step=1');
        }
    }

    public function getClientIp()
    {
        $ipAddress = '';

        if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED']) && !empty($_SERVER['HTTP_X_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR']) && !empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED']) && !empty($_SERVER['HTTP_FORWARDED'])) {
            $ipAddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipAddress = '127.0.0.1';
        }

        return $ipAddress;
    }

    public function getOrderLastIncrementId()
    {
        $query = new DbQuery();
        $query->select('MAX(`id_order`)');
        $query->from('orders');

        $order_id = Db::getInstance()->getValue($query);

        return $order_id + 1;
    }
}
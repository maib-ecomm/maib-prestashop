<?php

class MaibOkModuleFrontController extends ModuleFrontController {
    public function postProcess()
    {
        if (isset($_GET["payId"]) && isset($_GET["orderId"])) {
            $payId = $_GET["payId"];
            $orderId = (int) $_GET["orderId"];

            PrestaShopLogger::addLog(
                'Return to Ok URL. Pay ID: ' . $payId . ', Order ID: ' . $orderId,
                1
            );

            $cart = $this->context->cart;
            $customer = new Customer($cart->id_customer);
            $order_info = new Order($orderId);
            $total = (float) $cart->getOrderTotal(true, Cart::BOTH);

            if ($order_info) {
                $paymentModule = Module::getInstanceByName($this->module->name);
                if ($paymentModule instanceof PaymentModule) {
                    $paymentModule->validateOrder($cart->id, (int) Configuration::get('PAYMENT_MAIB_ORDER_PENDING_STATUS_ID'), $total, $this->module->displayName, null, [], (int) $cart->id_currency, false, $customer->secure_key);
                }

                Tools::redirect('index.php?controller=order-confirmation&id_cart=' . (int) $cart->id . '&id_module=' . (int) $this->module->id . '&id_order=' . $orderId . '&key=' . $customer->secure_key);
            } else {
                PrestaShopLogger::addLog(
                    'Ok URL: Order not found.',
                    3
                );

                $this->context->controller->success[] = $this->getTranslator()->trans(
                    'Error no payment',
                    [],
                    'Modules.Maib.Shop'
                );

                $this->context->controller->redirectWithNotifications(
                    'index.php?controller=order&step=1'
                );
            }
        } else {
            PrestaShopLogger::addLog(
                'Ok URL: Invalid or missing payId/orderId.',
                3
            );

            $this->context->controller->errors[] = $this->getTranslator()->trans(
                'Error no payment',
                [],
                'Modules.Maib.Shop'
            );

            $this->context->controller->redirectWithNotifications(
                'index.php?controller=order&step=1'
            );
        }
    }
}
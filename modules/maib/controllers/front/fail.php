<?php

class MaibFailModuleFrontController extends ModuleFrontController {
    public function postProcess()
    {
        if (isset($_GET["payId"]) && isset($_GET["orderId"])) {
            $payId = $_GET["payId"];
            $orderId = (int) $_GET["orderId"];

            PrestaShopLogger::addLog(
                'Return to Fail URL. Pay ID: ' . $payId . ', Order ID: ' . $orderId,
                1
            );

            $order_info = new Order($orderId);

            if ($order_info) {
                Tools::redirect('index.php?controller=order&step=1');
            } else {
                PrestaShopLogger::addLog(
                    'Fail URL: Order not found.',
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
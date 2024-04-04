<?php

require_once __DIR__ . "/../../src/MaibAuthRequest.php";
require_once __DIR__ . "/../../src/MaibApiRequest.php";
require_once __DIR__ . "/../../src/MaibSdk.php";

class_alias("MaibEcomm\MaibSdk\MaibAuthRequest", "MaibAuthRequest");
class_alias("MaibEcomm\MaibSdk\MaibApiRequest", "MaibApiRequest");

class MaibCallbackModuleFrontController extends ModuleFrontController {
    public function postProcess()
    {
        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $this->context->controller->errors[] = $this->getTranslator()->trans(
                'Error callback URL',
                [],
                'Modules.Maib.Shop'
            );

            $this->context->controller->redirectWithNotifications(
                Context::getContext()->link->getPageLink('cart&action=show')
            );
        }

        $json = file_get_contents("php://input");
        $data = json_decode($json, true);

        if (!isset($data["signature"]) || !isset($data["result"])) {
            PrestaShopLogger::addLog(
                'Callback URL - Signature or Payment data not found in notification.',
                3
            );

            exit();
        }

        PrestaShopLogger::addLog(
            'Notification on Callback URL: ' . json_encode($data, JSON_PRETTY_PRINT),
            1
        );

        $data_result = $data["result"]; // Data from "result" object
        $sortedDataByKeys = $this->sortByKeyRecursive($data_result); // Sort an array by key recursively
        $key = Configuration::get('PAYMENT_MAIB_SIGNATURE_KEY'); // Signature Key from Project settings
        $sortedDataByKeys[] = $key; // Add checkout Secret Key to the end of data array
        $signString = implode(":", $sortedDataByKeys); // Implode array recursively
        $sign = base64_encode(hash("sha256", $signString, true)); // Result Hash

        $pay_id = isset($data_result["payId"]) ? $data_result["payId"] : false;
        $order_id = isset($data_result["orderId"])
            ? (int) $data_result["orderId"]
            : false;
        $status = isset($data_result["status"])
            ? $data_result["status"]
            : false;

        if ($sign !== $data["signature"]) {
            echo "ERROR";

            PrestaShopLogger::addLog(
                'Signature is invalid: ' . $sign,
                3
            );

            exit();
        }

        echo "OK";

        PrestaShopLogger::addLog(
            'Signature is valid: ' . $sign,
            1
        );

        if (!$order_id || !$status) {
            PrestaShopLogger::addLog(
                'Callback URL - Order ID or Status not found in notification.',
                3
            );

            exit();
        }

        $order_info = new Order($order_id);

        if (!$order_info) {
            PrestaShopLogger::addLog(
                'Callback URL - Order ID not found in PrestaShop Orders.',
                3
            );

            exit();
        }

        if ($status === "OK") {
            // Payment success logic
            $order_status_id = Configuration::get('PAYMENT_MAIB_ORDER_SUCCESS_STATUS_ID');

            $order_note = sprintf(
                "Payment_Info: %s",
                json_encode($data_result, JSON_PRETTY_PRINT)
            );

            PrestaShopLogger::addLog(
                $order_note,
                1
            );

            $history = new OrderHistory();
            $history->id_order = (int)$order_id;
            $history->changeIdOrderState($order_status_id, (int)$order_id);
            $history->addWithemail();
        } else {
            // Payment failure logic
            $order_status_id = Configuration::get('PAYMENT_MAIB_ORDER_FAIL_STATUS_ID');

            $order_note = sprintf(
                "Payment_Info: %s",
                json_encode($data_result, JSON_PRETTY_PRINT)
            );

            PrestaShopLogger::addLog(
                $order_note,
                1
            );

            $history = new OrderHistory();
            $history->id_order = (int)$order_id;
            $history->changeIdOrderState($order_status_id, (int)$order_id);
            $history->addWithemail();
        }

        exit();
    }

    // Helper function: Sort an array by key recursively
    private function sortByKeyRecursive(array $array)
    {
        ksort($array, SORT_STRING);
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->sortByKeyRecursive($value);
            }
        }
        return $array;
    }
}
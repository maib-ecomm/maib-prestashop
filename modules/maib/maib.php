<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/src/MaibApiRequest.php';
require_once __DIR__ . '/src/MaibAuthRequest.php';

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use MaibEcomm\MaibSdk\MaibApiRequest;
use MaibEcomm\MaibSdk\MaibAuthRequest;

class Maib extends PaymentModule
{
    private $_html = '';
    private $_postErrors = [];
    public $is_eu_compatible;
    public $configKeys = [];

    public function __construct()
    {
        $this->name = 'maib';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => '8.99.99'
        ];
        $this->bootstrap = true;
        $this->author = 'BC Maib SA';
        $this->controllers = ['validation'];
        $this->is_eu_compatible = 1;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Maib Payment Gateway Module', [], 'Modules.Maib.Admin');
        $this->description = $this->trans('Accept Visa / Mastercard / Apple Pay / Google Pay on your store with the Maib Payment Gateway Module', [], 'Modules.Maib.Admin');
        $this->confirmUninstall = $this->trans('Are you sure about removing these details?', [], 'Modules.Maib.Admin');

        $this->configKeys = [
            'PAYMENT_MAIB_PROJECT_ID',
            'PAYMENT_MAIB_PROJECT_SECRET',
            'PAYMENT_MAIB_SIGNATURE_KEY',
            'PAYMENT_MAIB_ORDER_PENDING_STATUS_ID',
            'PAYMENT_MAIB_ORDER_SUCCESS_STATUS_ID',
            'PAYMENT_MAIB_ORDER_FAIL_STATUS_ID',
            'PAYMENT_MAIB_ORDER_REFUND_STATUS_ID',
        ];
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('paymentOptions')
            || !$this->registerHook('actionOrderStatusUpdate')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        return (
            parent::uninstall() 
            && Configuration::deleteByName('maib')
        );
    }

    private function _postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            if (!Tools::getValue('PAYMENT_MAIB_PROJECT_ID')) {
                $this->_postErrors[] = $this->trans('The "Project ID" field is required.', [], 'Modules.Maib.Admin');
            } elseif (!Tools::getValue('PAYMENT_MAIB_PROJECT_SECRET')) {
                $this->_postErrors[] = $this->trans('The "Project Secret" field is required.', [], 'Modules.Maib.Admin');
            } elseif (!Tools::getValue('PAYMENT_MAIB_SIGNATURE_KEY')) {
                $this->_postErrors[] = $this->trans('The "Signature Key" field is required.', [], 'Modules.Maib.Admin');
            }
        }
    }

    private function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            foreach ($this->configKeys as $key) {
                Configuration::updateValue($key, Tools::getValue($key));
            }
        }

        $this->_html .= $this->displayConfirmation($this->trans('Settings updated', [], 'Admin.Notifications.Success'));
    }

    public function getContent()
    {
        $this->_html = '';

        if (Tools::isSubmit('btnSubmit')) {
            $this->_postValidation();
            if (!count($this->_postErrors)) {
                $this->_postProcess();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }
        }

        $this->_html .= $this->renderForm();

        return $this->_html;
    }

    public function renderForm()
    {
        $fields_form_maib_merchants = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Configuration Maib Merchants', [], 'Modules.Maib.Admin'),
                    'icon' => 'icon-credit-card',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->trans('Project ID', [], 'Modules.Maib.Admin'),
                        'desc' => $this->trans('Project ID from maibmerchants.md', [], 'Modules.Maib.Admin'),
                        'name' => 'PAYMENT_MAIB_PROJECT_ID',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Project Secret', [], 'Modules.Maib.Admin'),
                        'desc' => $this->trans('Project Secret from maibmerchants.md. It is available after project activation.', [], 'Modules.Maib.Admin'),
                        'name' => 'PAYMENT_MAIB_PROJECT_SECRET',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Signature Key', [], 'Modules.Maib.Admin'),
                        'desc' => $this->trans('Signature Key for validating notifications на Callback URL. It is available after project activation.', [], 'Modules.Maib.Admin'),
                        'name' => 'PAYMENT_MAIB_SIGNATURE_KEY',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('OK URL', [], 'Modules.Maib.Admin'),
                        'desc' => $this->trans('OK URL Description', [], 'Modules.Maib.Admin'),
                        'name' => 'PAYMENT_MAIB_OK_URL',
                        'disabled' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('FAIL URL', [], 'Modules.Maib.Admin'),
                        'desc' => $this->trans('FAIL URL Description', [], 'Modules.Maib.Admin'),
                        'name' => 'PAYMENT_MAIB_FAIL_URL',
                        'disabled' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Callback URL', [], 'Modules.Maib.Admin'),
                        'desc' => $this->trans('Callback URL Description', [], 'Modules.Maib.Admin'),
                        'name' => 'PAYMENT_MAIB_CALLBACK_URL',
                        'disabled' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
            ],
        ];

        $fields_form_order_status = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Configuration Order Status', [], 'Modules.Maib.Admin'),
                    'icon' => 'icon-shopping-cart',
                ],
                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->trans('Pending payment', [], 'Modules.Maib.Admin'),
                        'name' => 'PAYMENT_MAIB_ORDER_PENDING_STATUS_ID',
                        'options' => [
                            'query' => $this->getOrderStatuses(),
                            'id' => 'id_order_state',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->trans('Completed payment', [], 'Modules.Maib.Admin'),
                        'name' => 'PAYMENT_MAIB_ORDER_SUCCESS_STATUS_ID',
                        'options' => [
                            'query' => $this->getOrderStatuses(),
                            'id' => 'id_order_state',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->trans('Failed payment', [], 'Modules.Maib.Admin'),
                        'name' => 'PAYMENT_MAIB_ORDER_FAIL_STATUS_ID',
                        'options' => [
                            'query' => $this->getOrderStatuses(),
                            'id' => 'id_order_state',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->trans('Refunded payment', [], 'Modules.Maib.Admin'),
                        'desc' => $this->trans('For payment refund, update the order status to the selected status. The funds will be returned to the customer card.', [], 'Modules.Maib.Admin'),
                        'name' => 'PAYMENT_MAIB_ORDER_REFUND_STATUS_ID',
                        'options' => [
                            'query' => $this->getOrderStatuses(),
                            'id' => 'id_order_state',
                            'name' => 'name',
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
            ],
        ];
 
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->id = (int) Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
        ];
        
        $helper->tpl_vars['fields_value']['PAYMENT_MAIB_OK_URL'] = $this->context->link->getModuleLink($this->name, 'ok', [], true);
        $helper->tpl_vars['fields_value']['PAYMENT_MAIB_FAIL_URL'] = $this->context->link->getModuleLink($this->name, 'fail', [], true);
        $helper->tpl_vars['fields_value']['PAYMENT_MAIB_CALLBACK_URL'] = $this->context->link->getModuleLink($this->name, 'callback', [], true);

        return $helper->generateForm([$fields_form_maib_merchants, $fields_form_order_status]);
    }

    public function getConfigFieldsValues()
    {
        $configValues = [];
        foreach ($this->configKeys as $key) {
            $configValues[$key] = Tools::getValue($key, Configuration::get($key));
        }

        return $configValues;
    }

    public function getOrderStatuses()
    {
        $query = new DbQuery();
        $query->select('os.id_order_state, osl.name');
        $query->from('order_state', 'os');
        $query->innerJoin('order_state_lang', 'osl', 'osl.id_order_state = os.id_order_state');
        $query->orderBy('os.id_order_state ASC');

        $statuses = Db::getInstance()->executeS($query);

        return $statuses;
    }

    public function hookPaymentOptions($params)
    {
        $newOption = new PaymentOption();
        $newOption->setModuleName($this->name)
                ->setCallToActionText($this->trans('Maib Payment Gateway Module', [], 'Modules.Maib.Admin'))
                ->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true));

        return [
            $newOption,
        ];
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    // Get Access Token
    public function getAccessToken()
    {
        $project_id = Configuration::get("PAYMENT_MAIB_PROJECT_ID");
        $project_secret = Configuration::get("PAYMENT_MAIB_PROJECT_SECRET");
        $signature_key = Configuration::get("PAYMENT_MAIB_SIGNATURE_KEY");

        // Check if access token exists in cache and is not expired
        if (
            Cache::retrieve("access_token") &&
            Cache::retrieve("access_token_expires") > time()
        ) {
            $access_token = Cache::retrieve("access_token");

            PrestaShopLogger::addLog(
                'Succesful received Access Token from cache.',
                1
            );

            return $access_token;
        }

        try {
            // Initiate Get Access Token Request to maib API
            $response = MaibAuthRequest::create()->generateToken(
                $project_id,
                $project_secret
            );

            PrestaShopLogger::addLog(
                'Succesful received Access Token from maib API',
                1
            );

            $access_token = $response->accessToken;

            // Store the access token and its expiration time in cache
            Cache::store("access_token", $access_token);
            Cache::store(
                "access_token_expires",
                time() + $response->expiresIn
            );
        } catch (Exception $ex) {
            PrestaShopLogger::addLog(
                'Access token error: ' . $ex->getMessage(),
                3
            );

            Tools::redirect('index.php?controller=order&step=1');
        }

        return $access_token;
    }

    public function hookActionOrderStatusUpdate($params)
    {
        if (!isset($params['id_order']) || !isset($params['newOrderStatus']) || !isset($params['oldOrderStatus'])) {
            return;
        }

        $oldOrderStatusId = $params['oldOrderStatus']->id;
        $newOrderStatusId = $params['newOrderStatus']->id;

        $orderInfo = new Order($oldOrderStatusId);

        $orderId = $params['id_order'];

        if (!$orderInfo || $newOrderStatusId != Configuration::get('PAYMENT_MAIB_ORDER_REFUND_STATUS_ID')) {
            return;
        }

        $payId = $this->getTransactionId($orderId);

        PrestaShopLogger::addLog(
            'Initiate Refund Payment Request to maib API, pay_id: ' . $payId . ', order_id: ' . $orderId,
            1
        );

        $params = ['payId' => strval($payId)];

        // Initiate Refund Payment Request to maib API
        $response = MaibApiRequest::create()->refund(
            $params,
            $this->getAccessToken()
        );

        PrestaShopLogger::addLog(
            'Response from refund endpoint: ' . json_encode($response, JSON_PRETTY_PRINT) . ', order_id: ' . $orderId,
            1
        );

        if ($response && $response->status === "OK") {
            PrestaShopLogger::addLog(
                'Full refunded payment ' . $payId . ' for order ' . $orderId,
                1
            );
            
            $history = new OrderHistory();
            $history->id_order = (int)$orderId;
            $history->changeIdOrderState($newOrderStatusId, (int)$orderId);
            $history->addWithemail();
        } else if ($response && $response->status === "REVERSED") {
            PrestaShopLogger::addLog(
                'Already refunded payment ' . $payId . ' for order ' . $orderId,
                1
            );
        } else {
            PrestaShopLogger::addLog(
                'Failed refund payment ' . $payId . ' for order ' . $orderId,
                3
            );
        }
    }

    public function getTransactionId(int $reference)
    {
        if ($reference === 0) {
            return;
        }

        $query = new DbQuery();
        $query->select('transaction_id');
        $query->from('order_payment');
        $query->where('order_reference = "' . $reference . '"');

        $transactionId = Db::getInstance()->getValue($query);

        return $transactionId;
    }
}
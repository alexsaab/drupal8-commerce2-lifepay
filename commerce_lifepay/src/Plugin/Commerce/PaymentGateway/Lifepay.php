<?php

namespace Drupal\commerce_lifepay\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\commerce_order\Entity\Order;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides the Lifepay payment gateway.
 * Class Lifepay
 *
 * @CommercePaymentGateway(
 *   id = "lafipay",
 *   label = @Translation("Lifepay"),
 *   display_label = @Translation("Lifepay"),
 *   forms = {
 *     "offsite-payment" = "Drupal\commerce_lifepay\PluginForm\OffsiteRedirect\LifepayForm",
 *   },
 *   payment_method_types = {"credit_card"},
 *   credit_card_types = {
 *     "maestro", "mastercard", "visa", "mir",
 *   },
 * )
 * @package Drupal\commerce_lifepay\Plugin\Commerce\PaymentGateway
 */
class Lifepay extends OffsitePaymentGatewayBase
{
    /**
     * Return default module settengs
     * @return array
     */
    public function defaultConfiguration(): array
    {

        $returned = [
                'service_id' => '',
                'key' => '',
                'skey' => '',
                'shop_hostname' => 'Store www...., order #',
                'api_version' => '1.0',
                'payment_method' => 'full_prepayment',
                'vat_products' => 'none',
                'vat_delivery' => 'none',
                'unit_products' => 'piece',
                'unit_delivery' => 'service',
                'object_products' => 'commodity',
                'object_delivery' => 'service',
                'send_email' => true,
            ] + parent::defaultConfiguration();

        return $returned;
    }

    /**
     * Setup configuration (settings)
     * {@inheritdoc}
     */
    public function buildConfigurationForm(array $form, FormStateInterface $form_state)
    {
        $form = parent::buildConfigurationForm($form, $form_state);

        $form['service_id'] = [
            '#type' => 'textfield',
            '#title' => $this->t("Service ID"),
            '#description' => $this->t("Visit merchant interface in Lifepay site https://home.life-pay.ru/alba/index/ and copy Service ID field"),
            '#default_value' => $this->configuration['service_id'],
            '#required' => true,
        ];

        $form['key'] = [
            '#type' => 'textfield',
            '#title' => $this->t("Service key"),
            '#description' => $this->t("Input service key here"),
            '#default_value' => $this->configuration['key'],
            '#required' => true,
        ];

        $form['skey'] = [
            '#type' => 'textfield',
            '#title' => $this->t("Service key"),
            '#description' => $this->t("Input secret key here"),
            '#default_value' => $this->configuration['skey'],
            '#required' => true,
        ];

        $form['shop_hostname'] = [
            '#type' => 'textfield',
            '#title' => $this->t("Hostname and order description"),
            '#description' => $this->t("Order description with host name"),
            '#default_value' => $this->configuration['shop_hostname'],
            '#required' => false,
        ];

        $form['api_version'] = [
            '#type' => 'select',
            '#title' => $this->t("API version"),
            '#description' => $this->t("See you API version in partner (merchant) interface"),
            '#options' => self::getApiVersionOptions(),
            '#default_value' => $this->configuration['api_version'],
            '#required' => true,
        ];

        $form['payment_method'] = [
            '#type' => 'select',
            '#title' => $this->t("Payment method"),
            '#description' => $this->t("Select payment method usually full_prepayment"),
            '#options' => self::getPaymentMethodOptions(),
            '#default_value' => $this->configuration['payment_method'],
            '#required' => true,
        ];

        $form['vat_products'] = [
            '#type' => 'select',
            '#title' => $this->t("VAT for products"),
            '#description' => $this->t("Select VAT for products"),
            '#options' => self::getVatOptions(),
            '#default_value' => $this->configuration['vat_products'],
            '#required' => true,
        ];

        $form['vat_delivery'] = [
            '#type' => 'select',
            '#title' => $this->t("VAT for delivery"),
            '#description' => $this->t("Select VAT for delivery"),
            '#options' => self::getVatOptions(),
            '#default_value' => $this->configuration['vat_delivery'],
            '#required' => true,
        ];

        $form['unit_products'] = [
            '#type' => 'select',
            '#title' => $this->t("Object for products"),
            '#description' => $this->t("Select units for products"),
            '#options' => self::getVatOptions(),
            '#default_value' => $this->configuration['unit_products'],
            '#required' => true,
        ];

        $form['unit_delivery'] = [
            '#type' => 'select',
            '#title' => $this->t("Units for delivery"),
            '#description' => $this->t("Select units for delivery"),
            '#options' => self::getVatOptions(),
            '#default_value' => $this->configuration['unit_delivery'],
            '#required' => true,
        ];

        $form['object_products'] = [
            '#type' => 'select',
            '#title' => $this->t("Object for products"),
            '#description' => $this->t("Select objects for products"),
            '#options' => self::getPaymentObjectOptions(),
            '#default_value' => $this->configuration['object_products'],
            '#required' => true,
        ];

        $form['object_delivery'] = [
            '#type' => 'select',
            '#title' => $this->t("Object for delivery"),
            '#description' => $this->t("Select objects for delivery"),
            '#options' => self::getPaymentObjectOptions(),
            '#default_value' => $this->configuration['object_delivery'],
            '#required' => true,
        ];

        $form['send_email'] = [
            '#type' => 'checkbox',
            '#title' => $this->t("Attach email in order"),
            '#description' => $this->t("Attach email in order or not"),
            '#value' => true,
            '#false_values' => [false],
            '#default_value' => $this->configuration['send_email'],
            '#required' => false,
        ];

        return $form;
    }

    /**
     * Validation of form
     * {@inheritdoc}
     */
    public function validateConfigurationForm(array &$form, FormStateInterface $form_state)
    {
        $values = $form_state->getValue($form['#parents']);
    }

    /**
     * Form submit
     * {@inheritdoc}
     */
    public function submitConfigurationForm(array &$form, FormStateInterface $form_state)
    {
        // Parent method will reset configuration array and further condition will
        // fail.
        parent::submitConfigurationForm($form, $form_state);
        if (!$form_state->getErrors()) {
            $values = $form_state->getValue($form['#parents']);
            $this->configuration['service_id'] = $values['service_id'];
            $this->configuration['key'] = $values['key'];
            $this->configuration['skey'] = $values['skey'];
            $this->configuration['shop_hostname'] = $values['shop_hostname'];
            $this->configuration['api_version'] = $values['api_version'];
            $this->configuration['payment_method'] = $values['payment_method'];
            $this->configuration['vat_products'] = $values['vat_products'];
            $this->configuration['vat_delivery'] = $values['vat_delivery'];
            $this->configuration['unit_products'] = $values['unit_products'];
            $this->configuration['unit_delivery'] = $values['unit_delivery'];
            $this->configuration['object_products'] = $values['object_products'];
            $this->configuration['object_delivery'] = $values['object_delivery'];
            $this->configuration['send_email'] = $values['send_email'];
        }
    }

    /**
     * Notity payment callback
     * @param Request $request
     * @return void
     */
    public function onNotify(Request $request)
    {

        $posted = \Drupal::request()->request->get();

        if (empty($posted)) {
            $posted = \Drupal::request()->query->get();
        }

        $orderId = self::getRequest('order_id');

        if (!isset($orderId)) {
            \Drupal::messenger()->addMessage($this->t('Site can not get info from you transaction. Please return to store and perform the order'),
                'success');
            $response = new RedirectResponse('/', 302);
            $response->send();
            return;
        }

        $order = Order::load($orderId);
        $transactionId = self::getRequest('tid');
        $paymentStorage = \Drupal::entityTypeManager()->getStorage('commerce_payment')->loadByProperties(['order_id' => [$orderId]]);
        $payment = end($paymentStorage);
        $paymentStorage = $this->entityTypeManager->getStorage('commerce_payment');

        if ($payment->state->value != 'complete') {
            if ($this->checkIpnRequestIsValid()) {
                $payment = $paymentStorage->create([
                    'state' => 'complete',
                    'amount' => $order->getTotalPrice(),
                    'payment_gateway' => $this->entityId,
                    'order_id' => $orderId,
                    'remote_id' => $transactionId,
                    'remote_state' => 'complete',
                    'state' => 'complete',
                ]);
                $payment->save();
                // Change order statuses to remove from busket
                $order->set('order_number', $orderId);
                $order->set('cart', 0);
                $order->set('state', 'validation');
                $order->set('placed', time());
                $order->save();
            } else {
                $this->onCancel($order, $request);
                return;
            }
        } else {
            \Drupal::messenger()->addMessage($this->t('Order complete! Thank you for payment'), 'success');
            $this->onReturn($order, $request);
            return;
        }
    }

    /**
     * Get post or get method
     * @param null $param
     */
    public static function getRequest($param = null)
    {
        try {
            $post = \Drupal::request()->request->get($param);
            $get = \Drupal::request()->query->get($param);
            if ($post) {
                return $post;
            }
            if ($get) {
                return $get;
            } else {
                return null;
            }
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * Get order amount
     * @param \Drupal\commerce_price\Price $price
     * @return string
     */
    public static function getOrderTotalAmount(\Drupal\commerce_price\Price $price)
    {
        return number_format($price->getNumber(), 2, '.', '');
    }

    /**
     * Get order currency
     * @param \Drupal\commerce_price\Price $price
     * @return string
     */
    public static function getOrderCurrencyCode(\Drupal\commerce_price\Price $price)
    {
        return $price->getCurrencyCode();
    }

    /**
     * Callback order success proceed
     * {@inheritdoc}
     */
    public function onReturn(OrderInterface $order, Request $request)
    {
        \Drupal::messenger()->addMessage($this->t('Order complete! Thank you for payment'), 'success');
        $orderId = self::getRequest('order_id');
        if ($user = \Drupal::currentUser()) {
            if ($userId = $user->id()) {
                $url = '/user/'.$userId.'/orders';
            } else {
                if (isset($orderId)) {
                    $url = '/checkout/'.$orderId.'/review';
                } else {
                    $url = '/';
                }
            }
        } else {
            $url = '/';
        }
        $response = new RedirectResponse($url, 302);
        $response->send();
        return;
    }


    /**
     * Callback order fail proceed
     * @param OrderInterface $order
     * @param Request $request
     */
    public function onCancel(OrderInterface $order, Request $request)
    {
        \Drupal::messenger()->addMessage($this->t('You have canceled checkout at @gateway but may resume the checkout process here when you are ready.',
            [
                '@gateway' => $this->getDisplayLabel(),
            ]), 'error');
        $orderId = self::getRequest('order_id');
        $url = '/checkout/'.$orderId.'/review';
        $response = new RedirectResponse($url, 302);
        $response->send();
    }

    /**
     * Get order product items
     * @param $order
     * @param $config array
     * @return array
     */
    public static function getOrderItems($order, array $config)
    {
        $itemsArray = [];

        foreach ($order->getItems() as $key => $item) {
            $name = $item->getTitle();
            $product = $item->getPurchasedEntity();
            $sku = $product->getSku();
            $price = (float) number_format($item->getUnitPrice()->getNumber(), 2, '.', '');
            $qty = (int) number_format($item->getQuantity(), 0, '.', '');
            $total = $price * $qty;
            $itemsArray[] = [
                'code' => $sku,
                'name' => $name,
                'price' => $price,
                'unit' => $config['unit_products'],
                'payment_object' => $config['object_products'],
                'payment_method' => $config['payment_method'],
                'quantity' => $qty,
                'sum' => $total,
                'vat_mode' => $config['vat_products'],
            ];
        }

        return $itemsArray;
    }

    /**
     * Get order Adjastment (Shipping, fee and etc.)
     * @param $order
     * @param $config array
     * @return array
     */
    public static function getOrderAdjustments($order, array $config): array
    {
        $itemsArray = [];
        foreach ($order->getAdjustments() as $adjustment) {
            $price = (float) number_format($adjustment->getAmount()->getNumber(), 2, '.', '');
            $qty = 1;
            $total = $price * $qty;
            $itemsArray[] = [
                'code' => $adjustment->getType(),
                'name' => substr($adjustment->getLabel(), 0, 100),
                'price' => $price,
                'unit' => $config['unit_delivery'],
                'payment_object' => $config['object_delivery'],
                'payment_method' => $config['payment_method'],
                'quantity' => $qty,
                'sum' => $total,
                'vat_mode' => $config['vat_delivery'],
            ];
        }
        return $itemsArray;
    }

    /**
     * Get formatted order items
     * @param $order
     * @param $configs
     * @return array
     */
    public static function getFormattedOrderItems($order, $configs): array
    {
        $items = array_merge(self::getOrderItems($order, $configs), self::getOrderAdjustments($order, $configs));
        return $items;
    }

    /**
     * Check LIFE PAY IPN validity
     * @param $posted
     * @return bool
     */
    private function checkIpnRequestIsValid($posted): bool
    {
        $url = \Drupal::request()->getHost() . $_SERVER['REQUEST_URI'];
        $check = $posted['check'];
        unset($posted['check']);

        if ($this->configuration['api_version'] === '2.0') {
            $signature = $this->getSign2("POST", $url, $posted, $this->configuration['skey']);
        } elseif ($this->configuration['api_version'] === '1.0') {
            $signature = $this->getSign1($posted, $this->configuration['skey']);
        }

        if ($signature === $check) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Part of sign generator
     * @param $queryData
     * @param string $argSeparator
     * @return string
     */
    private function httpBuildQueryRfc3986($queryData, string $argSeparator = '&'): string
    {
        $r = '';
        $queryData = (array)$queryData;
        if (!empty($queryData)) {
            foreach ($queryData as $k => $queryVar) {
                $r .= $argSeparator . $k . '=' . rawurlencode($queryVar);
            }
        }
        return trim($r, $argSeparator);
    }

    /**
     * Sign generator
     * @param $method
     * @param $url
     * @param $params
     * @param $secretKey
     * @param false $skipPort
     * @return string
     */
    private function getSign2($method, $url, $params, $secretKey, bool $skipPort = false)
    {
        ksort($params, SORT_LOCALE_STRING);

        $urlParsed = parse_url($url);
        $path = $urlParsed['path'];
        $host = isset($urlParsed['host']) ? $urlParsed['host'] : "";
        if (isset($urlParsed['port']) && $urlParsed['port'] != 80) {
            if (!$skipPort) {
                $host .= ":{$urlParsed['port']}";
            }
        }

        $method = strtoupper($method) == 'POST' ? 'POST' : 'GET';

        $data = implode("\n",
            array(
                $method,
                $host,
                $path,
                $this->httpBuildQueryRfc3986($params)
            )
        );

        $signature = base64_encode(
            hash_hmac("sha256",
                "{$data}",
                "{$secretKey}",
                TRUE
            )
        );

        return $signature;
    }

    /**
     * Add sign number two version
     * @param $posted
     * @param $key
     * @return string
     */
    private function getSign1($posted, $key): string
    {
        return rawurlencode(md5($posted['tid'] . $posted['name'] . $posted['comment'] . $posted['partner_id'] .
            $posted['service_id'] . $posted['order_id'] . $posted['type'] . $posted['cost'] . $posted['income_total'] .
            $posted['income'] . $posted['partner_income'] . $posted['system_income'] . $posted['command'] .
            $posted['phone_number'] . $posted['email'] . $posted['resultStr'] .
            $posted['date_created'] . $posted['version'] . $key));
    }

    /**
     * Get VAT options
     * @return string[]
     */
    public static function getVatOptions(): array
    {
        return [
            'none' => 'НДС не облагается',
            'vat10' => '10%, включая',
            'vat110' => '10%, поверх',
            'vat18' => '18%, включая',
            'vat118' => '18%, поверх',
            'vat20' => '20%, включая',
            'vat120' => '20%, поверх',
        ];
    }

    /**
     * Get API version options
     * @return string[]
     */
    private static function getApiVersionOptions(): array
    {
        return [
            '1.0' => '1.0',
            '2.0' => '2.0',
        ];
    }

    /**
     * Get unit options
     * @return string[]
     */
    private function getUnitOptions(): array
    {
        return [
            'piece' => 'штука',
            'service' => 'услуга',
            'package' => 'комплект',
            'g' => 'грамм',
            'kg' => 'килограмм',
            't' => 'тонна',
            'ml' => 'миллилитр',
            'm3' => 'кубометр',
            'hr' => 'час',
            'm' => 'метр',
            'km' => 'километр',
        ];
    }

    /**
     * Get payment method options
     * @return string []
     * @since
     */
    public static function getPaymentMethodOptions(): array
    {
        return [
            'full_prepayment' => 'Предоплата 100%',
            'prepayment' => 'Предоплата',
            'advance' => 'Аванс',
            'full_payment' => 'Полный расчёт',
            'partial_payment' => 'Частичный расчёт',
            'credit' => 'Передача в кредит',
            'credit_payment' => 'Оплата кредита',
        ];
    }

    /**
     * Get payment object options
     * @return string[]
     * @since
     */
    public static function getPaymentObjectOptions(): array
    {
        return [
            'commodity' => 'Товар (Значение по умолчанию. Передается, в том числе, при отсутствии параметра)',
            'excise' => 'Подакциозный товар',
            'job' => 'Работа',
            'service' => 'Услуга',
            'gambling_bet' => 'Ставка азартной игры',
            'gambling_prize' => 'Выигрыш азартной игры',
            'lottery' => 'Лотерейный билет',
            'lottery_prize' => 'Выигрыш лотереи',
            'intellectual_activity' => 'Предоставление результатов интеллектуальной деятельности',
            'payment' => 'Платёж',
            'agent_commission' => 'Агентское вознаграждение',
            'composite' => 'Составной предмет расчёта',
            'another' => 'Другое',
        ];
    }
}

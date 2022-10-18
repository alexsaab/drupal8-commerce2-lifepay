<?php

namespace Drupal\commerce_lifepay\PluginForm\OffsiteRedirect;

use Drupal\commerce_payment\PluginForm\PaymentOffsiteForm as BasePaymentOffsiteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_lifepay\Plugin\Commerce\PaymentGateway\Lifepay;


/**
 * Order registration and redirection to payment URL.
 */
class LifepayForm extends BasePaymentOffsiteForm
{


    /** @var string payment url for redirect on partner.life-pay.ru */
    public $payment_url = 'https://partner.life-pay.ru/alba/input/';

    /**
     * Return form for checkout
     * @param array $form
     * @param FormStateInterface $form_state
     * @return mixed
     */
    public function buildConfigurationForm(array $form, FormStateInterface $form_state)
    {

        $form = parent::buildConfigurationForm($form, $form_state);
        /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
        $payment = $this->entity;
        /** @var \Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OffsitePaymentGatewayInterface $payment_gateway_plugin */
        $payment_gateway_plugin = $payment->getPaymentGateway()->getPlugin();
        $configs = $payment_gateway_plugin->getConfiguration();

        $order = $payment->getOrder();
        $totalPrice = $order->getTotalPrice();
        $totalPriceNumber = ($totalPrice->getNumber()) ?
                number_format($totalPrice->getNumber(), 2, '.', '') : 0.00;

        $orderId = $payment->getOrderId();
        $customerEmail = $order->getEmail();
        $items = [];
        $items['items'] = Lifepay::getFormattedOrderItems($order, $configs);

        $data = array(
            'key' => $configs['key'],
            'cost' => $totalPriceNumber,
            'order_id' => $orderId,
            'name' =>  $configs['shop_hostname'] . $orderId,
            'invoice_data' => json_encode($items),
        );

        if ($configs['send_email']) {
            $data['email'] = $customerEmail;
        }

        if ($configs['api_version'] === '2.0') {
            unset($data['key']);
            $data['version'] = $configs['api_version'];
            $data['service_id'] = $configs['service_id'];
            $data['check'] = $this->getSign2('POST', $this->liveurl, $data, $configs['skey']);
        }

        return $this->buildRedirectForm($form, $form_state, $this->payment_url, $data, 'post');
    }

    /**
     * Return notify url
     * {@inheritdoc}
     */
    public function getNotifyUrl($paymentName): string
    {
        $url = \Drupal::request()->getSchemeAndHttpHost().'/payment/notify/'.$paymentName;
        return $url;
    }

}

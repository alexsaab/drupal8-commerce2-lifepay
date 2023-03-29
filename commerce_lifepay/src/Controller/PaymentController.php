<?php

namespace Drupal\commerce_lifepay\Controller;

use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_price\RounderInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Manage Lifepay Server Calls.
 */
final class PaymentController extends ControllerBase
{

    /**
     * The Commerce Payment Storage.
     *
     * @var \Drupal\Core\Entity\EntityStorageInterface
     */
    protected $paymentStorage;

    /**
     * The Commerce Order Type Storage.
     *
     * @var \Drupal\Core\Entity\EntityStorageInterface
     */
    protected $orderTypeStorage;

    /**
     * Price rounder service.
     *
     * @var \Drupal\commerce_price\RounderInterface
     */
    protected $priceRounder;

    /**
     * PaymentController constructor.
     */
    public function __construct(RounderInterface $priceRounder)
    {
        $this->paymentStorage = $this->entityTypeManager()->getStorage('commerce_payment');
        $this->orderTypeStorage = $this->entityTypeManager()->getStorage('commerce_order_type');
        $this->priceRounder = $priceRounder;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('commerce_price.rounder'),
        );
    }

    /**
     * Notify callback
     *
     * @param  Request  $request
     * @return void
     */
    public function notify(Request $request) {
        var_dump($this->paymentStorage); die;
        return $payment->onNotify($request);
    }

    /**
     * AddPaymentReturn documentation.
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     *   Request.
     */
    public function success(Request $request) {
        $orderId = \Drupal::request()->query->get('order_id');
        if (!$orderId) {
            die("No order ID!");
        }
        \Drupal::messenger()->addMessage($this->t('Order complete! Thank you for payment'), 'success');
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
        return $response->send();
    }

    /**
     * AddPaymentCancel documentation.
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     *   Request.
     */
    public function fail(
        PaymentInterface $commerce_payment,
        Request $request
    ) {
        $orderId = \Drupal::request()->query->get('order_id');
        if (!$orderId) {
            die("No order ID!");
        }
        \Drupal::messenger()->addMessage($this->t('You have canceled checkout at @gateway but may resume the checkout process here when you are ready.',
            [
                '@gateway' => 'Lifepay',
            ]), 'error');
        $url = '/checkout/'.$orderId.'/review';
        $response = new RedirectResponse($url, 302);
        return $response->send();
    }
}

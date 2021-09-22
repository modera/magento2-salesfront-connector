<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Modera\SalesfrontConnector\Model\SalesFront\Request as SalesFrontRequest;
use Modera\SalesfrontConnector\Setup\Product\Creator;

class VehicleOrderObserver implements ObserverInterface
{
    /**
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * @var SalesFrontRequest
     */
    private $salesFrontRequest;

    /**
     * @param PricingHelper $pricingHelper
     * @param SalesFrontRequest $salesFrontRequest
     */
    public function __construct(
        PricingHelper $pricingHelper,
        SalesFrontRequest $salesFrontRequest
    ) {
        $this->pricingHelper = $pricingHelper;
        $this->salesFrontRequest = $salesFrontRequest;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $items = $order->getAllItems();

        $phones = [];
        foreach ($order->getAddresses() as $address) {
            $phone = $address->getTelephone();
            if (!in_array($phone, $phones)) {
                $phones[] = $phone;
            }
        }

        $data = [
            'full_name' => $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname(),
            'emails' => [['address' => $order->getCustomerEmail(), 'type' => 'work']],
        ];

        foreach ($phones as $phone) {
            $data['phones'][] = ['code' => '', 'number' => $phone, 'type' => 'mobile'];
        }

        $customerData = $this->salesFrontRequest->createCustomer($data);
        if (isset($customerData['id'])) {
            $data = array_merge_recursive(
                [
                'extra' => '',
                'product_description' => '',
                'trade_in' => '',
                'financing' => '',
                'sf_customer_id' => $customerData['id'],
                'order_id' => $order->getIncrementId(),
                'total_amount' => $order->getGrandTotal(),
                'tax_amount' => $order->getTaxAmount(),
                'shipping_amount' => $order->getShippingAmount(),
                'payment_method' => $order->getPayment()->getAdditionalInformation()['method_title'],
                'order_status' => $order->getStatus(),
                ],
                $data
            );

            $this->prepareOrderData($items, $data);
            $this->salesFrontRequest->createLead($data);
            $this->salesFrontRequest->createWebRequestActivity($data);
        }
    }

    /**
     * Prepare order items data
     *
     * @param array $orderItems
     * @param array $data
     * @return void
     */
    private function prepareOrderData(array $orderItems, array &$data): void
    {
        foreach ($orderItems as $item) {
            if ($item->getProduct()->getData('vh_identification_make')) {
                $data['product_description'] .= $item->getName() . ' (' . $item->getSku() . ') ';
            } elseif ($item->getSku() == Creator::TRADE_IN_SKU || $item->getSku() == Creator::FINANCING_SKU) {
                $productOptions = [];
                foreach ($item->getProductOptions()['options'] as $option) {
                    $productOptions[str_replace(' ', '_', $option['label'])] = $option['value'];
                }

                if ($item->getSku() == Creator::TRADE_IN_SKU) {
                    $data['trade_in'] .= (string)($productOptions['make'] ?? '') . ' '
                        . (string)($productOptions['model'] ?? '') . ' '
                        . (string)($productOptions['year'] ?? '') . ' ('
                        . (string)($productOptions['register_number'] ?? '') . ') '
                        . (string)($productOptions['gearbox'] ?? '') . '; '
                        . (string)($productOptions['mileage'] ?? '') . '; '
                        . (string)($productOptions['suggested_price'] ?? '');
                }

                if ($item->getSku() == Creator::FINANCING_SKU) {
                    $data['financing'] .=
                        (string)(isset($productOptions['deposit']) ? $productOptions['deposit'] . '; ' : '')
                        . (string)(isset($productOptions['downpayment']) ? $productOptions['downpayment'] . '; ' : '')
                        . (string)(isset($productOptions['period']) ? $productOptions['period'] . '; ' : '')
                        . (string)(isset($productOptions['interest']) ? $productOptions['interest'] . '; ' : '')
                        . (string)($productOptions['residual'] ?? '');
                }
            } else {
                $data['extra'] .= $item->getName() . ' (' . $item->getSku() . ') '
                    . $this->pricingHelper->currency($item->getPrice(), true, false) . PHP_EOL;
            }
        }
    }
}

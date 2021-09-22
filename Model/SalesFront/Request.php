<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Model\SalesFront;

use Magento\Framework\HTTP\Client\Curl;
use Modera\SalesfrontConnector\Api\SalesFrontRequestInterface;
use Modera\SalesfrontConnector\Helper\Data as VehicleConfigHelper;

class Request implements SalesFrontRequestInterface
{
    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var VehicleConfigHelper
     */
    private $configHelper;

    /**
     * @var string|null
     */
    private $configurationXApiKey;

    /**
     * @param Curl $curl
     * @param VehicleConfigHelper $configHelper
     */
    public function __construct(Curl $curl, VehicleConfigHelper $configHelper)
    {
        $this->curl = $curl;
        $this->configHelper = $configHelper;
        $this->configurationXApiKey = $this->configHelper->getGeneralConfig('x_api_key');
        $this->curl->addHeader("X-Api-Key", $this->configurationXApiKey);
        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
    }

    /**
     * @inheritDoc
     */
    public function createCustomer(array $data): ?array
    {
        $requestData = json_encode($data);
        $createCustomerSFUrl = $this->configHelper->getGeneralConfig('salesfront_url') . '/customers.json';
        $this->curl->addHeader("Content-Length", strlen($requestData));
        $this->curl->post($createCustomerSFUrl, $requestData);

        return json_decode($this->curl->getBody(), true);
    }

    /**
     * @inheritDoc
     */
    public function createLead(array $data): void
    {
        $createLeadSFUrl = $this->configHelper->getGeneralConfig('salesfront_url') . '/customers/'
            . $data['sf_customer_id'] . '/lead.json';
        $requestData = json_encode(
            [
            'description' => 'ORDER - ' . $data['product_description'],
            "source" => "web",
            "occur_date" => date("d.m.Y h:i"),
            "heat_level" => 100
            ]
        );
        $this->curl->addHeader("Content-Length", strlen($requestData));
        $this->curl->post($createLeadSFUrl, $requestData);
    }

    /**
     * @inheritDoc
     */
    public function createWebRequestActivity(array $data): void
    {
        $createWebRequestActivityUrl = $this->configHelper->getGeneralConfig('salesfront_url') . '/customers/'
            . $data['sf_customer_id'] . '/activities.json';

        $requestData = json_encode(
            [
            'meta' => [
                "request_reason" => "place order",
                "request_source" => "magento web store",
                "fullFormData" => [
                    "Order ID:" => $data['order_id'],
                    "Vehicle" => $data['product_description'],
                    "Trade-in" => $data['trade_in'],
                    "Financing" => $data['financing'],
                    "Extra" => $data['extra'],
                    "Tax amount" => $data['tax_amount'],
                    "Total amount" => $data['total_amount'],
                    "Shipping amount" => $data['shipping_amount'],
                    "Payment method" => $data['payment_method'],
                    "Status" => $data['order_status'],
                ],
                "message" => "Added new order ID = " . $data['order_id']
            ],
            "type" => "CUSTOMER_WEB_REQUEST_FULL_DATA",
            "occurred_at" => date("d.m.Y H:i")
            ]
        );
        $this->curl->addHeader("Content-Length", strlen($requestData));
        $this->curl->post($createWebRequestActivityUrl, $requestData);
    }
}

<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Modera\SalesfrontConnector\Api\StorageManagementInterface;

class ProductSaveAfterObserver implements ObserverInterface
{
    /**
     * @var StorageManagementInterface
     */
    private $storageManagement;

    /**
     * @param StorageManagementInterface $storageManagement
     */
    public function __construct(
        StorageManagementInterface $storageManagement
    ) {
        $this->storageManagement = $storageManagement;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
        $newProductData = $product->getData();
        $oldProductData = $product->getOrigData();

        foreach ($newProductData as $key => $value) {
            if (strpos($key, 'vh_') !== false
                && !empty($oldProductData[$key])
                && $value != $oldProductData[$key]
            ) {
                $this->storageManagement->setCacheValidTime();
                break;
            }
        }
    }
}

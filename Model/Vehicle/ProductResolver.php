<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Model\Vehicle;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Modera\SalesfrontConnector\Model\Config\Vehicle as VehicleConfig;
use Modera\SalesfrontConnector\Api\StorageManagementInterface;

class ProductResolver
{
    /**
     * @var ProductRepository $productRepository
     */
    private $productRepository;

    /**
     * @var ProductInterfaceFactory
     */
    private $productFactory;

    /**
     * @var StorageManagementInterface
     */
    private $storageManagement;

    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param EavSetup $eavSetup
     * @param ProductInterfaceFactory $productFactory
     * @param StorageManagementInterface $storageManagement
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        EavSetup $eavSetup,
        ProductInterfaceFactory $productFactory,
        StorageManagementInterface $storageManagement
    ) {
        $this->productRepository = $productRepository;
        $this->eavSetup = $eavSetup;
        $this->productFactory = $productFactory;
        $this->storageManagement = $storageManagement;
    }

    /**
     * Create new product if not exist
     *
     * @param string $vinCode
     *
     * @return ProductInterface
     * @throws LocalizedException
     */
    public function getProduct(string $vinCode = ''): ProductInterface
    {
        $product = $this->getProductBySku($vinCode);
        if (!is_object($product)) {
            $attributeSetId = $this->eavSetup->getAttributeSetId(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                VehicleConfig::VH_ATTRIBUTE_SET_NAME
            );
            $product = $this->productFactory->create()->setAttributeSetId($attributeSetId)->setTypeId('simple');
            $this->storageManagement->setCacheValidTime();
        }

        $product->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(ProductStatus::STATUS_ENABLED)
            ->setQty(1);

        return $product;
    }

    /**
     * Retrieve product by sku
     *
     * @param string $sku
     * @return ProductInterface|string|null
     */
    public function getProductBySku(string $sku)
    {
        try {
            $product = $this->productRepository->get($sku);
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }

        return $product ?: null;
    }
}

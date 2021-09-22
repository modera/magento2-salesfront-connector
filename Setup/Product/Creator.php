<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Setup\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Modera\SalesfrontConnector\Model\Vehicle\Inventory as VehicleInventory;

class Creator
{
    /**#@+
     * Product skus
     */
    public const TRADE_IN_SKU = 'trade_in';
    public const FINANCING_SKU = 'financing';
    public const BOOKING_FEE_SKU = 'booking-fee';
    /**#@-*/

    /**
     * @var array
     */
    private $financingOptions = [
        'deposit',
        'period',
        'interest',
        'residual'
    ];

    /**
     * @var array
     */
    private $tradeInOptions = [
        'make',
        'model',
        'year',
        'register_number',
        'gearbox',
        'mileage',
        'suggested_price'
    ];

    /**
     * @var array
     */
    private $bookingFeeOptions = [
        'associated_order'
    ];

    /**
     * @var ProductRepository $productRepository
     */
    private $productRepository;

    /**
     * @var ProductInterfaceFactory
     */
    private $productFactory;

    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * @var ProductCustomOptionInterfaceFactory
     */
    private $productCustomOptionFactory;

    /**
     * @var State
     */
    private $state;

    /**
     * @var VehicleInventory
     */
    private $vehicleInventory;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param EavSetup $eavSetup
     * @param ProductInterfaceFactory $productFactory
     * @param ProductCustomOptionInterfaceFactory $productCustomOptionFactory
     * @param State $state
     * @param Inventory $vehicleInventory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        EavSetup $eavSetup,
        ProductInterfaceFactory $productFactory,
        ProductCustomOptionInterfaceFactory $productCustomOptionFactory,
        State $state,
        VehicleInventory $vehicleInventory
    ) {
        $this->productRepository = $productRepository;
        $this->eavSetup = $eavSetup;
        $this->productFactory = $productFactory;
        $this->productCustomOptionFactory = $productCustomOptionFactory;
        $this->state = $state;
        $this->vehicleInventory = $vehicleInventory;
    }

    /**
     * Create necessary products
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute(): void
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        $product = $this->getProductBySku(self::TRADE_IN_SKU);
        if (!is_object($product)) {
            $attributeSetId = $this->eavSetup->getAttributeSetId(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'Default'
            );

            $product = $this->productFactory->create()
                ->setAttributeSetId($attributeSetId)
                ->setName('Trade In')
                ->setSku(self::TRADE_IN_SKU)
                ->setTypeId('virtual')
                ->setPrice(0)
                ->setVisibility(Visibility::VISIBILITY_BOTH)
                ->setStatus(ProductStatus::STATUS_ENABLED);
            $product = $this->productRepository->save($product);

            $customOptions = [];
            foreach ($this->tradeInOptions as $option) {
                $customOptions[] = $this->productCustomOptionFactory->create()
                    ->setTitle($option)
                    ->setType('field')
                    ->setIsRequire(true)
                    ->setSortOrder(1)
                    ->setPrice(0)
                    ->setPriceType('fixed')
                    ->setMaxCharacters(0)
                    ->setProductSku($product->getSku());
            }

            $product->setOptions($customOptions);
            $this->productRepository->save($product);
            $this->vehicleInventory->updateInventory($product, 99999);
        }

        $product = $this->getProductBySku(self::FINANCING_SKU);
        if (!is_object($product)) {
            $attributeSetId = $this->eavSetup->getAttributeSetId(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'Default'
            );

            $product = $this->productFactory->create()
                ->setAttributeSetId($attributeSetId)
                ->setName('Financing')
                ->setSku(self::FINANCING_SKU)
                ->setTypeId('virtual')
                ->setPrice(0)
                ->setVisibility(Visibility::VISIBILITY_BOTH)
                ->setStatus(ProductStatus::STATUS_ENABLED);
            $product = $this->productRepository->save($product);

            $customOptions = [];
            foreach ($this->financingOptions as $option) {
                $customOptions[] = $this->productCustomOptionFactory->create()
                    ->setTitle($option)
                    ->setType('field')
                    ->setIsRequire(true)
                    ->setSortOrder(1)
                    ->setPrice(0)
                    ->setPriceType('fixed')
                    ->setMaxCharacters(0)
                    ->setProductSku($product->getSku());
            }

            $product->setOptions($customOptions);
            $product = $this->productRepository->save($product);
            $this->vehicleInventory->updateInventory($product, 99999);
        }

        $product = $this->getProductBySku(self::BOOKING_FEE_SKU);
        if (!is_object($product)) {
            $attributeSetId = $this->eavSetup->getAttributeSetId(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'Default'
            );

            $product = $this->productFactory->create()
                ->setAttributeSetId($attributeSetId)
                ->setName('Booking fee')
                ->setSku(self::BOOKING_FEE_SKU)
                ->setTypeId('virtual')
                ->setPrice(0)
                ->setVisibility(Visibility::VISIBILITY_BOTH)
                ->setStatus(ProductStatus::STATUS_ENABLED);
            $product = $this->productRepository->save($product);

            $customOptions = [];
            foreach ($this->bookingFeeOptions as $option) {
                $customOptions[] = $this->productCustomOptionFactory->create()
                    ->setTitle($option)
                    ->setType('field')
                    ->setIsRequire(true)
                    ->setSortOrder(1)
                    ->setPrice(0)
                    ->setPriceType('fixed')
                    ->setMaxCharacters(0)
                    ->setProductSku($product->getSku());
            }

            $product->setOptions($customOptions);
            $product = $this->productRepository->save($product);
            $this->vehicleInventory->updateInventory($product, 99999);
        }
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

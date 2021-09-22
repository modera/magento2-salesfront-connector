<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Model\Vehicle;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Validation\ValidationException;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;

class Inventory
{
    /**
     * @var SourceItemsSaveInterface
     */
    private $sourceItemsSave;

    /**
     * @var SourceItemInterfaceFactory
     */
    private $sourceItemFactory;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @param SourceRepositoryInterface $sourceRepository
     * @param SourceItemsSaveInterface $sourceItemsSave
     * @param SourceItemInterfaceFactory $sourceItemFactory
     */
    public function __construct(
        SourceRepositoryInterface $sourceRepository,
        SourceItemsSaveInterface $sourceItemsSave,
        SourceItemInterfaceFactory $sourceItemFactory
    ) {
        $this->sourceRepository = $sourceRepository;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->sourceItemFactory = $sourceItemFactory;
    }

    /**
     * Add stock to all sources
     *
     * @param ProductInterface $product
     * @param int $qty
     *
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws ValidationException
     */
    public function updateInventory(ProductInterface $product, int $qty = 1): void
    {
        $listSource = $this->sourceRepository->getList()->getItems();
        foreach ($listSource as $source) {
            $sourceItem = $this->sourceItemFactory->create();
            $sourceItem->setSourceCode($source->getSourceCode());
            $sourceItem->setSku($product->getSku());
            $sourceItem->setQuantity($qty);
            $sourceItem->setStatus(ProductStatus::STATUS_ENABLED);
            $this->sourceItemsSave->execute([$sourceItem]);
        }
    }
}

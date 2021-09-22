<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Model\Vehicle;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Modera\SalesfrontConnector\Helper\Data as DataHelper;

class Category
{
    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @var CategoryLinkManagementInterface
     */
    private $categoryLinkManagement;

    /**
     * @param DataHelper $dataHelper
     * @param CategoryLinkManagementInterface $categoryLinkManagement
     */
    public function __construct(
        DataHelper $dataHelper,
        CategoryLinkManagementInterface $categoryLinkManagement
    ) {
        $this->dataHelper = $dataHelper;
        $this->categoryLinkManagement = $categoryLinkManagement;
    }

    /**
     * Set categories to product
     *
     * @param ProductInterface $product
     *
     * @return void
     */
    public function setCategory(ProductInterface $product): void
    {
        $categoryList = $this->dataHelper->getGeneralConfig('category');
        if ($categoryList = explode(',', $categoryList)) {
            $this->categoryLinkManagement->assignProductToCategories(
                $product->getSku(),
                $categoryList
            );
        }
    }
}

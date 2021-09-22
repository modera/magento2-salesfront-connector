<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Model\Config\Source;

use Magento\Catalog\Model\Config\Source\Category;
use Magento\Framework\Exception\LocalizedException;

class AutomotiveCategory extends Category
{
    /**
     * @inheritDoc
     *
     * @throws LocalizedException
     */
    public function toOptionArray($addEmpty = true): array
    {
        $collection = $this->_categoryCollectionFactory->create()->addAttributeToSelect('name')->load();
        $options = [];

        if ($addEmpty) {
            $options[] = ['label' => __('-- Please Select a Category --'), 'value' => ''];
        }

        foreach ($collection as $category) {
            $options[] = [
                'label' => str_repeat('-', (int)$category->getLevel()) . $category->getName(),
                'value' => $category->getId()
            ];
        }

        return $options;
    }
}

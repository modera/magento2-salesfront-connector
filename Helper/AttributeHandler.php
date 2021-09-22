<?php
namespace Modera\SalesfrontConnector\Helper;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\OptionLabel;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\Source\TableFactory;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

class AttributeHandler extends AbstractHelper
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var array
     */
    private $attributeValues;

    /**
     * @var TableFactory
     */
    private $tableFactory;

    /**
     * @var AttributeOptionManagementInterface
     */
    private $attributeOptionManagement;

    /**
     * @var AttributeOptionLabelInterfaceFactory
     */
    private $optionLabelFactory;

    /**
     * @var AttributeOptionInterfaceFactory
     */
    private $optionFactory;

    /**
     * @param Context $context
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param TableFactory $tableFactory
     * @param AttributeOptionManagementInterface $attributeOptionManagement
     * @param AttributeOptionLabelInterfaceFactory $optionLabelFactory
     * @param AttributeOptionInterfaceFactory $optionFactory
     */
    public function __construct(
        Context $context,
        ProductAttributeRepositoryInterface $attributeRepository,
        TableFactory $tableFactory,
        AttributeOptionManagementInterface $attributeOptionManagement,
        AttributeOptionLabelInterfaceFactory $optionLabelFactory,
        AttributeOptionInterfaceFactory $optionFactory
    ) {
        parent::__construct($context);

        $this->attributeRepository = $attributeRepository;
        $this->tableFactory = $tableFactory;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->optionLabelFactory = $optionLabelFactory;
        $this->optionFactory = $optionFactory;
    }

    /**
     * Get Attributes
     *
     * @param string $attributeCode
     * @return ProductAttributeInterface
     * @throws NoSuchEntityException
     */
    public function getAttribute(string $attributeCode): ProductAttributeInterface
    {
        return $this->attributeRepository->get($attributeCode);
    }

    /**
     * Get attribute option ID
     *
     * @param string $attributeCode
     * @param string $label
     *
     * @return false|mixed
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function getAttributeOptionId(string $attributeCode, string $label)
    {
        if (strlen($label) < 1) {
            throw new LocalizedException(
                __('Label for %1 must not be empty.', $attributeCode)
            );
        }

        $optionId = $this->getOptionId($attributeCode, $label);

        if (!$optionId) {
            /** @var OptionLabel $optionLabel */
            $optionLabel = $this->optionLabelFactory->create();
            $optionLabel->setStoreId(0);
            $optionLabel->setLabel($label);
            $option = $this->optionFactory->create();
            $option->setLabel($label);
            $option->setStoreLabels([$optionLabel]);
            $option->setSortOrder(0);
            $option->setIsDefault(false);
            $this->attributeOptionManagement->add(Product::ENTITY, $attributeCode, $option);
            $optionId = $this->getOptionId($attributeCode, $label, true);
        }

        return $optionId;
    }

    /**
     * Get option ID
     *
     * @param string $attributeCode
     * @param string $label
     * @param bool $force
     *
     * @return false|mixed
     * @throws NoSuchEntityException
     */
    public function getOptionId(string $attributeCode, string $label, bool $force = false)
    {
        /** @var Attribute $attribute */
        $attribute = $this->getAttribute($attributeCode);

        if ($force === true || !isset($this->attributeValues[$attribute->getAttributeId()])) {
            $this->attributeValues[$attribute->getAttributeId()] = [];

            $sourceModel = $this->tableFactory->create();
            $sourceModel->setAttribute($attribute);

            foreach ($sourceModel->getAllOptions() as $option) {
                $this->attributeValues[$attribute->getAttributeId()][$option['label']] = $option['value'];
            }
        }

        if (isset($this->attributeValues[$attribute->getAttributeId()][$label])) {
            return $this->attributeValues[$attribute->getAttributeId()][$label];
        }

        return false;
    }
}

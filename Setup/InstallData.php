<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Setup;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\AttributeSetRepository;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Modera\SalesfrontConnector\Model\Config\Source\ConditionOptions;
use Modera\SalesfrontConnector\Model\Config\Source\EngineTypeOptions;
use Modera\SalesfrontConnector\Model\Config\Source\EquipmentOptions;
use Modera\SalesfrontConnector\Model\Config\Source\FuelTypeOptions;
use Modera\SalesfrontConnector\Model\Config\Source\TransmissionOptions;
use Modera\SalesfrontConnector\Model\Config\Vehicle;
use Magento\Store\Model\StoreManagerInterface;
use Modera\SalesfrontConnector\Setup\Product\Creator as ProductCreator;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\Frontend\Datetime;

class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var AttributeSetRepository
     */
    private $attributeSetRepository;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductCreator
     */
    private $productCreator;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeSetRepository $attributeSetRepository
     * @param AttributeSetFactory $attributeSetFactory
     * @param CategorySetupFactory $categorySetupFactory
     * @param StoreManagerInterface $storeManager
     * @param ProductCreator $productCreator
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        AttributeSetRepository $attributeSetRepository,
        AttributeSetFactory $attributeSetFactory,
        CategorySetupFactory $categorySetupFactory,
        StoreManagerInterface $storeManager,
        ProductCreator $productCreator
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->storeManager = $storeManager;
        $this->productCreator = $productCreator;
    }

    /**
     * @inheritDoc
     *
     * @throws LocalizedException|\Zend_Validate_Exception
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) : void
    {
        $setup->startSetup();

        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $entityTypeId = $categorySetup->getEntityTypeId(Product::ENTITY);

        $attributeSet = $this->attributeSetFactory->create();
        $attributeSet->setData(
            [
            'attribute_set_name' => Vehicle::VH_ATTRIBUTE_SET_NAME,
            'entity_type_id' => $entityTypeId,
            'sort_order' => 200,
            ]
        )->validate();
        $attributeSet = $this->attributeSetRepository->save($attributeSet);
        $attributeSetId = $eavSetup->getAttributeSetId('catalog_product', Vehicle::VH_ATTRIBUTE_SET_NAME);
        $attributeSet->initFromSkeleton($categorySetup->getDefaultAttributeSetId($entityTypeId));
        $this->attributeSetRepository->save($attributeSet);

        $this->createAttributeGroups($attributeSetId, $categorySetup);

        $this->addEngineAttributes($eavSetup, $entityTypeId);
        $this->addBodyAttributes($eavSetup, $entityTypeId);
        $this->addEconomyAttributes($eavSetup, $entityTypeId);
        $this->addIdentificationAttributes($eavSetup, $entityTypeId);
        $this->addTransmissionAttributes($eavSetup, $entityTypeId);
        $this->addPerformanceAttributes($eavSetup, $entityTypeId);

        if ($attributeSetId !== null) {
            $this->addEngineAttributesToAttributeSet($attributeSetId, $categorySetup);
            $this->addBodyAttributesToAttributeSet($attributeSetId, $categorySetup);
            $this->addEconomyAttributesToAttributeSet($attributeSetId, $categorySetup);
            $this->addIdentificationAttributesToAttributeSet($attributeSetId, $categorySetup);
            $this->addTransmissionAttributesToAttributeSet($attributeSetId, $categorySetup);
            $this->addPerformanceAttributesToAttributeSet($attributeSetId, $categorySetup);
        }

        $this->productCreator->execute();

        $setup->endSetup();
    }

    /**
     * Create attribute groups
     *
     * @param string $attributeSetId
     * @param CategorySetup $categorySetup
     * @return void
     */
    private function createAttributeGroups(string $attributeSetId, CategorySetup $categorySetup): void
    {
        $categorySetup->addAttributeGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_ENGINE,
            100
        )->addAttributeGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_PERFORMANCE,
            101
        )->addAttributeGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_TRANSMISSION,
            102
        )->addAttributeGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_BODY,
            103
        )->addAttributeGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_ECONOMY,
            104
        )->addAttributeGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_IDENTIFICATION,
            105
        )->addAttributeGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_PRICE,
            106
        );
    }

    /**
     * Adding engine attributes to attribute set
     *
     * @param string $attributeSetId
     * @param CategorySetup $categorySetup
     */
    private function addEngineAttributesToAttributeSet(string $attributeSetId, CategorySetup $categorySetup): void
    {
        $categorySetup->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_ENGINE,
            Vehicle::VH_ATTR_ENGINE_FUEL,
            10
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_ENGINE,
            Vehicle::VH_ATTR_ENGINE_TYPE,
            20
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_ENGINE,
            Vehicle::VH_ATTR_ENGINE_NAME,
            30
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_ENGINE,
            Vehicle::VH_ATTR_ENGINE_CYLINDERS,
            40
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_ENGINE,
            Vehicle::VH_ATTR_ENGINE_CYLINDERS_CAPACITY_CM3,
            50
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_ENGINE,
            Vehicle::VH_ATTR_ENGINE_CYLINDERS_CAPACITY_L,
            60
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_ENGINE,
            Vehicle::VH_ATTR_ENGINE_COMMENTS,
            70
        );
    }

    /**
     * Adding body attributes to attribute set
     *
     * @param string $attributeSetId
     * @param CategorySetup $categorySetup
     */
    private function addBodyAttributesToAttributeSet(string $attributeSetId, CategorySetup $categorySetup): void
    {
        $categorySetup->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_BODY,
            Vehicle::VH_ATTR_BODY_TYPE,
            10
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_BODY,
            Vehicle::VH_ATTR_BODY_TYPE_NAME,
            20
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_BODY,
            Vehicle::VH_ATTR_BODY_COLOR,
            30
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_BODY,
            Vehicle::VH_ATTR_BODY_COLOR_NAME,
            40
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_BODY,
            Vehicle::VH_ATTR_BODY_DOORS,
            50
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_BODY,
            Vehicle::VH_ATTR_BODY_SEATS,
            60
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_BODY,
            Vehicle::VH_ATTR_BODY_TRUNC_SIZE_L,
            70
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_BODY,
            Vehicle::VH_ATTR_BODY_FUEL_TANK_SIZE,
            80
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_BODY,
            Vehicle::VH_ATTR_BODY_WEIGHT,
            90
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_BODY,
            Vehicle::VH_ATTR_BODY_MAX_WEIGHT,
            100
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_BODY,
            Vehicle::VH_ATTR_BODY_LENGTH,
            110
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_BODY,
            Vehicle::VH_ATTR_BODY_WIDTH,
            120
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_BODY,
            Vehicle::VH_ATTR_BODY_HEIGHT,
            130
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_BODY,
            Vehicle::VH_ATTR_BODY_WHEELBASE,
            140
        );
    }

    /**
     * Adding economy attributes to attribute set
     *
     * @param string $attributeSetId
     * @param CategorySetup $categorySetup
     */
    private function addEconomyAttributesToAttributeSet(string $attributeSetId, CategorySetup $categorySetup): void
    {
        $categorySetup->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_ECONOMY,
            Vehicle::VH_ATTR_ECONOMY_EURO_CLASS,
            10
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_ECONOMY,
            Vehicle::VH_ATTR_ECONOMY_CO2_EMISSION,
            20
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_ECONOMY,
            Vehicle::VH_ATTR_ECONOMY_STANDART,
            30
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_ECONOMY,
            Vehicle::VH_ATTR_ECONOMY_CITY,
            40
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_ECONOMY,
            Vehicle::VH_ATTR_ECONOMY_HIGHWAY,
            50
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_ECONOMY,
            Vehicle::VH_ATTR_ECONOMY_COMBINED,
            60
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_ECONOMY,
            Vehicle::VH_ATTR_ECONOMY_RANGE,
            70
        );
    }

    /**
     * Adding transmission attributes to attribute set
     *
     * @param string $attributeSetId
     * @param CategorySetup $categorySetup
     */
    private function addTransmissionAttributesToAttributeSet(string $attributeSetId, CategorySetup $categorySetup): void
    {
        $categorySetup->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_TRANSMISSION,
            Vehicle::VH_ATTR_TRANSMISSION_DRIVETRAIN_TYPE,
            10
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_TRANSMISSION,
            Vehicle::VH_ATTR_TRANSMISSION_TYPE,
            20
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_TRANSMISSION,
            Vehicle::VH_ATTR_TRANSMISSION_NAME,
            30
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_TRANSMISSION,
            Vehicle::VH_ATTR_TRANSMISSION_GEARS,
            40
        );
    }

    /**
     * Adding identification attributes to attribute set
     *
     * @param string $attributeSetId
     * @param CategorySetup $categorySetup
     */
    private function addIdentificationAttributesToAttributeSet(
        string $attributeSetId,
        CategorySetup $categorySetup
    ): void {
        $categorySetup->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_IDENTIFICATION,
            Vehicle::VH_ATTR_IDENTIFICATION_MAKE,
            10
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_IDENTIFICATION,
            Vehicle::VH_ATTR_IDENTIFICATION_MODEL,
            20
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_IDENTIFICATION,
            Vehicle::VH_ATTR_IDENTIFICATION_MODEL_TYPE,
            30
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_IDENTIFICATION,
            Vehicle::VH_ATTR_IDENTIFICATION_MODEL_YEAR,
            40
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_IDENTIFICATION,
            Vehicle::VH_ATTR_IDENTIFICATION_MODEL_CODE,
            50
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_IDENTIFICATION,
            Vehicle::VH_ATTR_IDENTIFICATION_REG_NR,
            60
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_IDENTIFICATION,
            Vehicle::VH_ATTR_IDENTIFICATION_VIN_CODE,
            70
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_IDENTIFICATION,
            Vehicle::VH_ATTR_IDENTIFICATION_MILLEAGE,
            80
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_IDENTIFICATION,
            Vehicle::VH_ATTR_IDENTIFICATION_ORDER_NUMBER,
            90
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_IDENTIFICATION,
            Vehicle::VH_ATTR_IDENTIFICATION_TITLE,
            100
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_IDENTIFICATION,
            Vehicle::VH_ATTR_IDENTIFICATION_FIRST_REGISTRATION,
            110
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_IDENTIFICATION,
            Vehicle::VH_ATTR_IDENTIFICATION_EQUIPMENT,
            120
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_IDENTIFICATION,
            Vehicle::VH_ATTR_IDENTIFICATION_EQUIPMENT_SPECIAL,
            130
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_IDENTIFICATION,
            Vehicle::VH_ATTR_IDENTIFICATION_EQUIPMENT_EXTRA,
            140
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_IDENTIFICATION,
            Vehicle::VH_ATTR_IDENTIFICATION_GALLERY,
            150
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_IDENTIFICATION,
            Vehicle::VH_ATTR_IDENTIFICATION_BOOKED_UNTIL,
            160
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_IDENTIFICATION,
            Vehicle::VH_ATTR_IDENTIFICATION_CONDITION,
            170
        );
    }

    /**
     * Adding performance attributes to attribute set
     *
     * @param string $attributeSetId
     * @param CategorySetup $categorySetup
     */
    private function addPerformanceAttributesToAttributeSet(string $attributeSetId, CategorySetup $categorySetup): void
    {
        $categorySetup->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_PERFORMANCE,
            Vehicle::VH_ATTR_PERFORMANCE_MAX_POWER_KW,
            10
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_PERFORMANCE,
            Vehicle::VH_ATTR_PERFORMANCE_MAX_POWER_RPM,
            20
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_PERFORMANCE,
            Vehicle::VH_ATTR_PERFORMANCE_MAX_TORQUE,
            30
        )->addAttributeToGroup(
            Product::ENTITY,
            $attributeSetId,
            Vehicle::VH_GROUP_NAME_PERFORMANCE,
            Vehicle::VH_ATTR_PERFORMANCE_MAX_SPEED,
            40
        );
    }

    /**
     * Adding options to attribute
     *
     * @param EavSetup $eavSetup
     * @param string $attributeId
     * @param array $attributeOptions
     *
     * @return void
     * @throws LocalizedException
     */
    private function addOptionsForAttribute(EavSetup $eavSetup, string $attributeId, array $attributeOptions = []): void
    {
        $options = ['attribute_id' => $attributeId];
        $storeList = $this->storeManager->getStores();
        foreach ($attributeOptions as $value) {
            $options['value'][$value][0] = $value;
            foreach ($storeList as $store) {
                $options['value'][$value][$store->getId()] = $value;
            }
        }

        $eavSetup->addAttributeOption($options);
    }

    /**
     * Adding engine attributes
     *
     * @param EavSetup $eavSetup
     * @param string $entityTypeId
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    private function addEngineAttributes(EavSetup $eavSetup, string $entityTypeId): void
    {
        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_ENGINE_FUEL);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_ENGINE_FUEL,
                $this->getAttributeData(
                    'int',
                    'select',
                    Vehicle::VH_ATTR_ENGINE_FUEL_LABEL,
                    FuelTypeOptions::class,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_ENGINE
                )
            );
            $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_ENGINE_FUEL);
            $this->addOptionsForAttribute($eavSetup, $attributeId, ['Petrol', 'Diesel', 'Electric', 'CNG', 'LNG']);
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_ENGINE_TYPE);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_ENGINE_TYPE,
                $this->getAttributeData(
                    'int',
                    'select',
                    Vehicle::VH_ATTR_ENGINE_TYPE_LABEL,
                    EngineTypeOptions::class,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_ENGINE
                )
            );
            $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_ENGINE_TYPE);
            $this->addOptionsForAttribute(
                $eavSetup,
                $attributeId,
                ['Petrol', 'Diesel', 'Electric', 'CNG', 'LNG', 'Hybrid']
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_ENGINE_NAME);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_ENGINE_NAME,
                $this->getAttributeData(
                    'varchar',
                    'text',
                    Vehicle::VH_ATTR_ENGINE_NAME_LABEL,
                    '',
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_ENGINE
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_ENGINE_CYLINDERS);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_ENGINE_CYLINDERS,
                $this->getAttributeData(
                    'varchar',
                    'text',
                    Vehicle::VH_ATTR_ENGINE_CYLINDERS_LABEL,
                    '',
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_ENGINE
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_ENGINE_CYLINDERS_CAPACITY_CM3);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_ENGINE_CYLINDERS_CAPACITY_CM3,
                $this->getAttributeData(
                    'varchar',
                    'text',
                    Vehicle::VH_ATTR_ENGINE_CYLINDERS_CAPACITY_CM3_LABEL,
                    '',
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_ENGINE
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_ENGINE_CYLINDERS_CAPACITY_L);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_ENGINE_CYLINDERS_CAPACITY_L,
                $this->getAttributeData(
                    'varchar',
                    'text',
                    Vehicle::VH_ATTR_ENGINE_CYLINDERS_CAPACITY_L_LABEL,
                    '',
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_ENGINE
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_ENGINE_COMMENTS);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_ENGINE_COMMENTS,
                $this->getAttributeData(
                    'varchar',
                    'text',
                    Vehicle::VH_ATTR_ENGINE_COMMENTS_LABEL,
                    '',
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_ENGINE
                )
            );
        }
    }

    /**
     * Adding body attributes
     *
     * @param EavSetup $eavSetup
     * @param string $entityTypeId
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    private function addBodyAttributes(EavSetup $eavSetup, string $entityTypeId): void
    {
        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_BODY_TYPE);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_BODY_TYPE,
                $this->getAttributeData(
                    'varchar',
                    'select',
                    Vehicle::VH_ATTR_BODY_TYPE_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_BODY
                )
            );
            $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_BODY_TYPE);
            $this->addOptionsForAttribute(
                $eavSetup,
                $attributeId,
                [
                    'Convertible',
                    'Coupe',
                    'Hatchback',
                    'Limousine',
                    'Minivan',
                    'Pickup',
                    'Roadster',
                    'Sedan',
                    'Targa',
                    'Touring',
                    'Wagon'
                ]
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_BODY_TYPE_NAME);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_BODY_TYPE_NAME,
                $this->getAttributeData(
                    'varchar',
                    'text',
                    Vehicle::VH_ATTR_BODY_TYPE_NAME_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_BODY
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_BODY_COLOR);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_BODY_COLOR,
                $this->getAttributeData(
                    'int',
                    'select',
                    Vehicle::VH_ATTR_BODY_COLOR_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_BODY
                )
            );
            $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_BODY_COLOR);
            $this->addOptionsForAttribute(
                $eavSetup,
                $attributeId,
                [
                    'Beige', 'Black', 'Blue', 'Brown', 'Dark blue', 'Dark green', 'Dark gray', 'Dark red', 'Gold',
                    'Green', 'Light blue', 'Light green', 'Light gray', 'Light yellow', 'Orange', 'Pink', 'Purple',
                    'Silver', 'White', 'Yellow'
                ]
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_BODY_COLOR_NAME);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_BODY_COLOR_NAME,
                $this->getAttributeData(
                    'varchar',
                    'text',
                    Vehicle::VH_ATTR_BODY_COLOR_NAME_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_BODY
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_BODY_DOORS);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_BODY_DOORS,
                $this->getAttributeData(
                    'varchar',
                    'text',
                    Vehicle::VH_ATTR_BODY_DOORS_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_BODY
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_BODY_SEATS);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_BODY_SEATS,
                $this->getAttributeData(
                    'int',
                    'text',
                    Vehicle::VH_ATTR_BODY_SEATS_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_BODY
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_BODY_TRUNC_SIZE_L);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_BODY_TRUNC_SIZE_L,
                $this->getAttributeData(
                    'int',
                    'text',
                    Vehicle::VH_ATTR_BODY_TRUNC_SIZE_L_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_BODY
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_BODY_FUEL_TANK_SIZE);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_BODY_FUEL_TANK_SIZE,
                $this->getAttributeData(
                    'int',
                    'text',
                    Vehicle::VH_ATTR_BODY_FUEL_TANK_SIZE_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_BODY
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_BODY_WEIGHT);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_BODY_WEIGHT,
                $this->getAttributeData(
                    'int',
                    'text',
                    Vehicle::VH_ATTR_BODY_WEIGHT_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_BODY
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_BODY_MAX_WEIGHT);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_BODY_MAX_WEIGHT,
                $this->getAttributeData(
                    'int',
                    'text',
                    Vehicle::VH_ATTR_BODY_MAX_WEIGHT_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_BODY
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_BODY_LENGTH);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_BODY_LENGTH,
                $this->getAttributeData(
                    'int',
                    'text',
                    Vehicle::VH_ATTR_BODY_LENGTH_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_BODY
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_BODY_WIDTH);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_BODY_WIDTH,
                $this->getAttributeData(
                    'int',
                    'text',
                    Vehicle::VH_ATTR_BODY_WIDTH_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_BODY
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_BODY_HEIGHT);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_BODY_HEIGHT,
                $this->getAttributeData(
                    'int',
                    'text',
                    Vehicle::VH_ATTR_BODY_HEIGHT_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_BODY
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_BODY_WHEELBASE);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_BODY_WHEELBASE,
                $this->getAttributeData(
                    'int',
                    'text',
                    Vehicle::VH_ATTR_BODY_WHEELBASE_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_BODY
                )
            );
        }
    }

    /**
     * Adding economy attributes
     *
     * @param EavSetup $eavSetup
     * @param string $entityTypeId
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    private function addEconomyAttributes(EavSetup $eavSetup, string $entityTypeId): void
    {
        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_ECONOMY_EURO_CLASS);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_ECONOMY_EURO_CLASS,
                $this->getAttributeData(
                    'int',
                    'select',
                    Vehicle::VH_ATTR_ECONOMY_EURO_CLASS_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_ECONOMY
                )
            );
            $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_ECONOMY_EURO_CLASS);
            $this->addOptionsForAttribute(
                $eavSetup,
                $attributeId,
                [
                    'Euro 1',
                    'Euro 2',
                    'Euro 3',
                    'Euro 4',
                    'Euro 5',
                    'Euro 5a',
                    'Euro 5b',
                    'Euro 6',
                    'Euro 6b',
                    'Euro 6c',
                    'Euro 6d'
                ]
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_ECONOMY_CO2_EMISSION);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_ECONOMY_CO2_EMISSION,
                $this->getAttributeData(
                    'int',
                    'text',
                    Vehicle::VH_ATTR_ECONOMY_CO2_EMISSION_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_ECONOMY
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_ECONOMY_STANDART);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_ECONOMY_STANDART,
                $this->getAttributeData(
                    'int',
                    'select',
                    Vehicle::VH_ATTR_ECONOMY_STANDART_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_ECONOMY
                )
            );
            $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_ECONOMY_STANDART);
            $this->addOptionsForAttribute(
                $eavSetup,
                $attributeId,
                [
                    'WLTP',
                    'NEDC',
                    'EPA'
                ]
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_ECONOMY_CITY);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_ECONOMY_CITY,
                $this->getAttributeData(
                    'decimal',
                    'text',
                    Vehicle::VH_ATTR_ECONOMY_CITY_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_ECONOMY
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_ECONOMY_HIGHWAY);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_ECONOMY_HIGHWAY,
                $this->getAttributeData(
                    'decimal',
                    'text',
                    Vehicle::VH_ATTR_ECONOMY_HIGHWAY_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_ECONOMY
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_ECONOMY_COMBINED);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_ECONOMY_COMBINED,
                $this->getAttributeData(
                    'decimal',
                    'text',
                    Vehicle::VH_ATTR_ECONOMY_COMBINED_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_ECONOMY
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_ECONOMY_RANGE);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_ECONOMY_RANGE,
                $this->getAttributeData(
                    'int',
                    'text',
                    Vehicle::VH_ATTR_ECONOMY_RANGE_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_ECONOMY
                )
            );
        }
    }

    /**
     * Adding transmission attributes
     *
     * @param EavSetup $eavSetup
     * @param string $entityTypeId
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    private function addTransmissionAttributes(EavSetup $eavSetup, string $entityTypeId): void
    {
        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_TRANSMISSION_DRIVETRAIN_TYPE);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_TRANSMISSION_DRIVETRAIN_TYPE,
                $this->getAttributeData(
                    'int',
                    'select',
                    Vehicle::VH_ATTR_TRANSMISSION_DRIVETRAIN_TYPE_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_TRANSMISSION
                )
            );
            $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_TRANSMISSION_DRIVETRAIN_TYPE);
            $this->addOptionsForAttribute($eavSetup, $attributeId, ['FWD', 'RWD', 'AWD']);
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_TRANSMISSION_TYPE);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_TRANSMISSION_TYPE,
                $this->getAttributeData(
                    'int',
                    'select',
                    Vehicle::VH_ATTR_TRANSMISSION_TYPE_LABEL,
                    TransmissionOptions::class,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_TRANSMISSION
                )
            );
            $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_TRANSMISSION_TYPE);
            $this->addOptionsForAttribute($eavSetup, $attributeId, ['Automatic', 'Manual']);
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_TRANSMISSION_NAME);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_TRANSMISSION_NAME,
                $this->getAttributeData(
                    'int',
                    'select',
                    Vehicle::VH_ATTR_TRANSMISSION_NAME_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_TRANSMISSION
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_TRANSMISSION_GEARS);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_TRANSMISSION_GEARS,
                $this->getAttributeData(
                    'int',
                    'text',
                    Vehicle::VH_ATTR_TRANSMISSION_GEARS_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_TRANSMISSION
                )
            );
        }
    }

    /**
     * Adding identification attributes
     *
     * @param EavSetup $eavSetup
     * @param string $entityTypeId
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    private function addIdentificationAttributes(EavSetup $eavSetup, string $entityTypeId): void
    {
        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_MAKE);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_IDENTIFICATION_MAKE,
                $this->getAttributeData(
                    'int',
                    'select',
                    Vehicle::VH_ATTR_IDENTIFICATION_MAKE_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_IDENTIFICATION
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_MODEL);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_IDENTIFICATION_MODEL,
                $this->getAttributeData(
                    'int',
                    'select',
                    Vehicle::VH_ATTR_IDENTIFICATION_MODEL_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_IDENTIFICATION
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_MODEL_TYPE);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_IDENTIFICATION_MODEL_TYPE,
                $this->getAttributeData(
                    'int',
                    'select',
                    Vehicle::VH_ATTR_IDENTIFICATION_MODEL_TYPE_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_IDENTIFICATION
                )
            );
            $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_MODEL_TYPE);
            $this->addOptionsForAttribute($eavSetup, $attributeId, ['MC', 'PC', 'LCV']);
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_MODEL_YEAR);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_IDENTIFICATION_MODEL_YEAR,
                $this->getAttributeData(
                    'int',
                    'text',
                    Vehicle::VH_ATTR_IDENTIFICATION_MODEL_YEAR_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_IDENTIFICATION
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_MODEL_CODE);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_IDENTIFICATION_MODEL_CODE,
                $this->getAttributeData(
                    'text',
                    'text',
                    Vehicle::VH_ATTR_IDENTIFICATION_MODEL_CODE_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_IDENTIFICATION
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_REG_NR);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_IDENTIFICATION_REG_NR,
                $this->getAttributeData(
                    'text',
                    'text',
                    Vehicle::VH_ATTR_IDENTIFICATION_REG_NR_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_IDENTIFICATION
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_VIN_CODE);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_IDENTIFICATION_VIN_CODE,
                $this->getAttributeData(
                    'text',
                    'text',
                    Vehicle::VH_ATTR_IDENTIFICATION_VIN_CODE_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_IDENTIFICATION
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_MILLEAGE);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_IDENTIFICATION_MILLEAGE,
                $this->getAttributeData(
                    'int',
                    'text',
                    Vehicle::VH_ATTR_IDENTIFICATION_MILLEAGE_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_IDENTIFICATION
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_ORDER_NUMBER);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_IDENTIFICATION_ORDER_NUMBER,
                $this->getAttributeData(
                    'text',
                    'text',
                    Vehicle::VH_ATTR_IDENTIFICATION_ORDER_NUMBER_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_IDENTIFICATION
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_TITLE);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_IDENTIFICATION_TITLE,
                $this->getAttributeData(
                    'text',
                    'text',
                    Vehicle::VH_ATTR_IDENTIFICATION_TITLE_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_IDENTIFICATION
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_HASH);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_IDENTIFICATION_HASH,
                $this->getAttributeData(
                    'text',
                    'text',
                    Vehicle::VH_ATTR_IDENTIFICATION_HASH_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_IDENTIFICATION
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_FIRST_REGISTRATION);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_IDENTIFICATION_FIRST_REGISTRATION,
                $this->getAttributeData(
                    'datetime',
                    'date',
                    Vehicle::VH_ATTR_IDENTIFICATION_FIRST_REGISTRATION_LABEL,
                    null,
                    Datetime::class,
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_IDENTIFICATION
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_EQUIPMENT);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_IDENTIFICATION_EQUIPMENT,
                $this->getAttributeData(
                    'varchar',
                    'multiselect',
                    Vehicle::VH_ATTR_IDENTIFICATION_EQUIPMENT_LABEL,
                    EquipmentOptions::class,
                    '',
                    ArrayBackend::class,
                    false,
                    Vehicle::VH_GROUP_NAME_IDENTIFICATION
                )
            );
            $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_EQUIPMENT);
            $this->addOptionsForAttribute($eavSetup, $attributeId, EquipmentOptions::EQUIPMENT_LIST);
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_EQUIPMENT_SPECIAL);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_IDENTIFICATION_EQUIPMENT_SPECIAL,
                $this->getAttributeData(
                    'text',
                    'text',
                    Vehicle::VH_ATTR_IDENTIFICATION_EQUIPMENT_SPECIAL_LABEL,
                    null,
                    '',
                    ArrayBackend::class,
                    false,
                    Vehicle::VH_GROUP_NAME_IDENTIFICATION
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_EQUIPMENT_EXTRA);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_IDENTIFICATION_EQUIPMENT_EXTRA,
                $this->getAttributeData(
                    'text',
                    'text',
                    Vehicle::VH_ATTR_IDENTIFICATION_EQUIPMENT_EXTRA_LABEL,
                    null,
                    '',
                    ArrayBackend::class,
                    false,
                    Vehicle::VH_GROUP_NAME_IDENTIFICATION
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_GALLERY);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_IDENTIFICATION_GALLERY,
                $this->getAttributeData(
                    'text',
                    'text',
                    Vehicle::VH_ATTR_IDENTIFICATION_GALLERY_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_IDENTIFICATION
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_TAGS);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_IDENTIFICATION_TAGS,
                $this->getAttributeData(
                    'varchar',
                    'multiselect',
                    Vehicle::VH_ATTR_IDENTIFICATION_TAGS_LABEL,
                    null,
                    '',
                    ArrayBackend::class,
                    false,
                    Vehicle::VH_GROUP_NAME_IDENTIFICATION
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_BOOKED_UNTIL);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_IDENTIFICATION_BOOKED_UNTIL,
                $this->getAttributeData(
                    'datetime',
                    'datetime',
                    Vehicle::VH_ATTR_IDENTIFICATION_BOOKED_UNTIL_LABEL,
                    null,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_IDENTIFICATION
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_CONDITION);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_IDENTIFICATION_CONDITION,
                $this->getAttributeData(
                    'int',
                    'select',
                    Vehicle::VH_ATTR_IDENTIFICATION_CONDITION_LABEL,
                    ConditionOptions::class,
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_IDENTIFICATION
                )
            );
            $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_IDENTIFICATION_CONDITION);
            $this->addOptionsForAttribute($eavSetup, $attributeId, ['New', 'Demo', 'Used']);
        }
    }

    /**
     * Adding performance attributes
     *
     * @param EavSetup $eavSetup
     * @param string $entityTypeId
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    private function addPerformanceAttributes(EavSetup $eavSetup, string $entityTypeId): void
    {
        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_PERFORMANCE_MAX_POWER_KW);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_PERFORMANCE_MAX_POWER_KW,
                $this->getAttributeData(
                    'varchar',
                    'text',
                    Vehicle::VH_ATTR_PERFORMANCE_MAX_POWER_KW_LABEL,
                    '',
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_PERFORMANCE
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_PERFORMANCE_MAX_POWER_RPM);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_PERFORMANCE_MAX_POWER_RPM,
                $this->getAttributeData(
                    'varchar',
                    'text',
                    Vehicle::VH_ATTR_PERFORMANCE_MAX_POWER_RPM_LABEL,
                    '',
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_PERFORMANCE
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_PERFORMANCE_MAX_TORQUE);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_PERFORMANCE_MAX_TORQUE,
                $this->getAttributeData(
                    'varchar',
                    'text',
                    Vehicle::VH_ATTR_PERFORMANCE_MAX_TORQUE_LABEL,
                    '',
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_PERFORMANCE
                )
            );
        }

        $attributeId = $eavSetup->getAttributeId($entityTypeId, Vehicle::VH_ATTR_PERFORMANCE_MAX_SPEED);
        if (empty($attributeId)) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                Vehicle::VH_ATTR_PERFORMANCE_MAX_SPEED,
                $this->getAttributeData(
                    'varchar',
                    'text',
                    Vehicle::VH_ATTR_PERFORMANCE_MAX_SPEED_LABEL,
                    '',
                    '',
                    '',
                    false,
                    Vehicle::VH_GROUP_NAME_PERFORMANCE
                )
            );
        }
    }

    /**
     * Prepare attribute data
     *
     * @param string $type
     * @param string $input
     * @param string $label
     * @param string|null $source
     * @param string $frontend
     * @param string $backend
     * @param bool $required
     * @param string $group
     *
     * @return array
     */
    private function getAttributeData(
        string $type,
        string $input,
        $label = '',
        $source = null,
        $frontend = '',
        $backend = '',
        $required = false,
        $group = Vehicle::VH_GROUP_NAME_IDENTIFICATION
    ): array {
        return [
            'type' => $type,
            'label' => $label,
            'input' => $input,
            'required' => $required,
            'backend' => $backend,
            'frontend' => $frontend,
            'sort_order' => 4,
            'source' => $source,
            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'group' => $group,
            'note' => $label,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
            'used_for_sort_by' => true,
            'searchable' => true,
            'filterable' => true,
            'comparable' => true,
            'user_defined' => true,
        ];
    }
}

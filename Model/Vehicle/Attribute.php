<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Model\Vehicle;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Setup\Exception;
use Modera\SalesfrontConnector\Model\Config\Vehicle as VehicleConfig;
use Modera\SalesfrontConnector\Helper\AttributeHandler;

class Attribute
{
    /**
     * @var ProductRepository $productRepository
     */
    private $productRepository;

    /**
     * @var AttributeRepository $attributeRepository
     */
    private $attributeRepository;

    /**
     * @var AttributeHandler
     */
    private $attributeHelper;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param AttributeRepository $attributeRepository
     * @param AttributeHandler $attrHelper
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        AttributeRepository $attributeRepository,
        AttributeHandler $attrHelper
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->productRepository = $productRepository;
        $this->attributeHelper = $attrHelper;
    }

    /**
     * Save attributes
     *
     * @param object $productData
     * @param ProductInterface $product
     * @param array $errors
     *
     * @return ProductInterface|Product
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws StateException
     * @throws NoSuchEntityException
     */
    public function save(object $productData, ProductInterface $product, array &$errors)
    {
        if (property_exists($productData, 'price')) {
            $product->setPrice($productData->price);
        }

        if (property_exists($productData, 'specialPrice')) {
            $product->setSpecialPrice($productData->specialPrice);
        }

        if (property_exists($productData, 'vinCode')) {
            $product->setVhIdentificationVinCode($productData->vinCode);
            $product->setSku($productData->vinCode);
        }

        if (property_exists($productData, 'title')) {
            $product->setName($productData->title);
            $product->setVhIdentificationTitle($productData->title);
        }

        if (property_exists($productData, 'model')) {
            $product->setVhIdentificationModel($productData->model);
        }

        if (property_exists($productData, 'mileage')) {
            $product->setVhIdentificationMilleage($productData->mileage);
        }

        if (property_exists($productData, 'modelYear')) {
            $product->setVhIdentificationModelYear($productData->modelYear);
        }

        if (property_exists($productData, 'modelYear')) {
            $product->setVhIdentificationModelYear($productData->modelYear);
        }

        if (property_exists($productData, 'modelType')) {
            $product->setVhIdentificationModelType($productData->modelType);
        }

        if (property_exists($productData, 'modelCode')) {
            $product->setVhIdentificationModelCode($productData->modelCode);
        }

        if (property_exists($productData, 'regNr')) {
            $product->setVhIdentificationRegNr($productData->regNr);
        }

        if (property_exists($productData, 'orderNumber')) {
            $product->setVhIdentificationOrderNumber($productData->orderNumber);
            $product->setUrlKey($productData->orderNumber);
        }

        if (property_exists($productData, 'firstRegistration')) {
            $product->setVhIdentificationFirstRegistration($productData->firstRegistration);
        }

        if (property_exists($productData, 'equipment')) {
            $attributeSource = $this->attributeRepository
                ->get(Product::ENTITY, VehicleConfig::VH_ATTR_IDENTIFICATION_EQUIPMENT)
                ->getSource();
            $valueIds = [];
            foreach ($productData->equipment as $equipmentValue) {
                $valueIds[] = $attributeSource->getOptionId($equipmentValue);
            }

            $product->setVhIdentificationEquipment(implode(',', $valueIds));
        }

        if (property_exists($productData, 'specialEquipment')) {
            $product->setVhIdentificationEquipmentSpecial(json_encode($productData->specialEquipment));
        }

        if (property_exists($productData, 'extraEquipment')) {
            $product->setVhIdentificationEquipmentExtra(json_encode($productData->extraEquipment));
        }

        if (property_exists($productData, 'bodyType')) {
            $attribute = $this->attributeRepository->get(Product::ENTITY, VehicleConfig::VH_ATTR_BODY_TYPE);
            $attributeCode = $attribute->getSource()->getOptionId($productData->bodyType);
            $product->setVhBodyType($attributeCode);
        }

        if (property_exists($productData, 'color')) {
            $attribute = $this->attributeRepository->get(Product::ENTITY, VehicleConfig::VH_ATTR_BODY_COLOR);
            $attributeCode = $attribute->getSource()->getOptionId($productData->color);
            $product->setVhBodyColor($attributeCode);
        }

        if (property_exists($productData, 'engineType')) {
            $attribute = $this->attributeRepository->get(
                Product::ENTITY,
                VehicleConfig::VH_ATTR_ENGINE_TYPE
            );
            $attributeCode = $attribute->getSource()->getOptionId($productData->engineType);
            $product->setVhEngineType($attributeCode);
        }

        if (property_exists($productData, 'fuel')) {
            $attribute = $this->attributeRepository->get(
                Product::ENTITY,
                VehicleConfig::VH_ATTR_ENGINE_FUEL
            );
            $attributeCode = $attribute->getSource()->getOptionId($productData->fuel);
            $product->setVhEngineFuel($attributeCode);
        }

        if (property_exists($productData, 'bodyTypeName')) {
            $attribute = $this->attributeRepository->get(
                Product::ENTITY,
                VehicleConfig::VH_ATTR_BODY_TYPE_NAME
            );
            $attributeCode = $attribute->getSource()->getOptionId($productData->bodyTypeName);
            $product->setVhBodyTypeName($attributeCode);
        }

        if (property_exists($productData, 'colorName')) {
            $product->setVhBodyColorName($productData->colorName);
        }

        if (property_exists($productData, 'doors')) {
            $product->setVhBodyDoors($productData->doors);
        }

        if (property_exists($productData, 'seats')) {
            $product->setVhBodySeats($productData->seats);
        }

        if (property_exists($productData, 'trunkSize')) {
            $product->setVhBodyTruncSize($productData->trunkSize);
        }

        if (property_exists($productData, 'fuelTankSize')) {
            $product->setVhBodyFuelTankSize($productData->fuelTankSize);
        }

        if (property_exists($productData, 'weight')) {
            $product->setVhBodyWeight($productData->weight);
            $product->setWeight($productData->weight);
        }

        if (property_exists($productData, 'maxWeight')) {
            $product->setVhBodyMaxWeight($productData->maxWeight);
        }

        if (property_exists($productData, 'length')) {
            $product->setVhBodyLength($productData->length);
        }

        if (property_exists($productData, 'width')) {
            $product->setVhBodyWidth($productData->width);
        }

        if (property_exists($productData, 'height')) {
            $product->setVhBodyHeight($productData->height);
        }

        if (property_exists($productData, 'wheelBase')) {
            $product->setVhBodyWheelbase($productData->wheelBase);
        }

        if (property_exists($productData, 'engineName')) {
            $product->setVhEngineName($productData->engineName);
        }

        if (property_exists($productData, 'cylinders')) {
            $product->setVhEngineCylinders($productData->cylinders);
        }

        if (property_exists($productData, 'cylinderCapacityCm3')) {
            $product->setVhEngineCylindersCm($productData->cylinderCapacityCm3);
        }

        if (property_exists($productData, 'cylinderCapacityL')) {
            $product->setVhEngineCylindersLitre($productData->cylinderCapacityL);
        }

        if (property_exists($productData, 'engineComments')) {
            $product->setVhEngineComments($productData->engineComments);
        }

        if (property_exists($productData, 'status') && property_exists($productData->status, 'booked')) {
            $product->setVhIdentificationBookedUntil($productData->status->booked);
        }

        $attribute = $this->attributeRepository->get(
            Product::ENTITY,
            VehicleConfig::VH_ATTR_IDENTIFICATION_CONDITION
        );
        if (property_exists($productData, 'condition')) {
            $condition = $productData->condition;
        } else {
            if (property_exists($productData, 'tags') && in_array('demo', $productData->tags)) {
                $condition = 'Demo';
            } elseif (property_exists($productData, 'mileage') && $productData->mileage > 0) {
                $condition = 'Used';
            } else {
                $condition = 'New';
            }
        }
        $attributeCode = $attribute->getSource()->getOptionId($condition);
        $product->setVhIdentificationCondition($attributeCode);

        if (property_exists($productData, 'drivetrainType')) {
            $attribute = $this->attributeRepository->get(
                Product::ENTITY,
                VehicleConfig::VH_ATTR_TRANSMISSION_DRIVETRAIN_TYPE
            );
            $attributeCode = $attribute->getSource()->getOptionId($productData->drivetrainType);
            $product->setVhTransmissionDrivetrain($attributeCode);
        }

        if (property_exists($productData, 'economyStandard')) {
            $attribute = $this->attributeRepository->get(
                Product::ENTITY,
                VehicleConfig::VH_ATTR_ECONOMY_STANDART
            );
            $attributeCode = $attribute->getSource()->getOptionId($productData->economyStandard);
            $product->setVhEconomyStantard($attributeCode);
        }

        if (property_exists($productData, 'transmissionType')) {
            $attribute = $this->attributeRepository->get(
                Product::ENTITY,
                VehicleConfig::VH_ATTR_TRANSMISSION_TYPE
            );
            $attributeCode = $attribute->getSource()->getOptionId($productData->transmissionType);
            $product->setVhTransmissionType($attributeCode);
        }

        if (property_exists($productData, 'euroClass')) {
            $attribute = $this->attributeRepository->get(
                Product::ENTITY,
                VehicleConfig::VH_ATTR_ECONOMY_EURO_CLASS
            );
            $attributeCode = $attribute->getSource()->getOptionId($productData->euroClass);
            $product->setVhEconomyEuroClass($attributeCode);
        }

        if (property_exists($productData, 'transmissionName')) {
            $product->setVhTransmissionName($productData->transmissionName);
        }

        if (property_exists($productData, 'gears')) {
            $product->setVhTransmissionGears($productData->gears);
        }

        if (property_exists($productData, 'images')) {
            $product->setVhIdentificationGallery(json_encode($productData->images));
        }

        if (property_exists($productData, 'maxPowerKw')) {
            $product->setVhPerformanceMaxPowerKw($productData->maxPowerKw);
        }

        if (property_exists($productData, 'maxPowerRpm')) {
            $product->setVhPerformanceMaxPowerRpm($productData->maxPowerRpm);
        }

        if (property_exists($productData, 'maxTorque')) {
            $product->setVhPerformanceMaxTorque($productData->maxTorque);
        }

        if (property_exists($productData, 'maxSpeed')) {
            $product->setVhPerformanceMaxSpeed($productData->maxSpeed);
        }

        if (property_exists($productData, 'co2Emission')) {
            $product->setVhEconomyCo2Emission($productData->co2Emission);
        }

        if (property_exists($productData, 'economyStandard')) {
            $product->setVhEconomyStandart($productData->economyStandard);
        }

        if (property_exists($productData, 'economyCity')) {
            $product->setVhEconomyCity($productData->economyCity);
        }

        if (property_exists($productData, 'economyHighway')) {
            $product->setVhEconomyHighway($productData->economyHighway);
        }

        if (property_exists($productData, 'economyCombined')) {
            $product->setVhEconomyCombined($productData->economyCombined);
        }

        if (property_exists($productData, 'range')) {
            $product->setVhEconomyRange($productData->range);
        }

        if (property_exists($productData, 'model')) {
            try {
                $optionId = $this->attributeHelper
                    ->getAttributeOptionId(VehicleConfig::VH_ATTR_IDENTIFICATION_MODEL, $productData->model);
                if ($optionId !== null) {
                    $product->setData(VehicleConfig::VH_ATTR_IDENTIFICATION_MODEL, $optionId);
                }
            } catch (\Exception $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        if (property_exists($productData, 'tags')) {
            try {
                $optionIds = [];
                foreach ($productData->tags as $tag) {
                    $optionIds[] = $this->attributeHelper
                        ->getAttributeOptionId(VehicleConfig::VH_ATTR_IDENTIFICATION_TAGS, $tag);
                }

                if (count($optionIds)) {
                    $product->setVhIdentificationTags(implode(',', $optionIds));
                }
            } catch (Exception $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        if (property_exists($productData, 'make')) {
            try {
                $optionId = $this->attributeHelper
                    ->getAttributeOptionId(VehicleConfig::VH_ATTR_IDENTIFICATION_MAKE, $productData->make);
                if ($optionId !== null) {
                    $product->setVhIdentificationMake($optionId);
                }
            } catch (\Exception $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        return $this->productRepository->save($product, true);
    }
}

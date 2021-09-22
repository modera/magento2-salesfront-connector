<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class BodyTypeOptions extends AbstractSource
{
    /**
     * @inheritDoc
     */
    public function getAllOptions(): array
    {
        if ($this->_options === null) {
            $this->_options = [
                ['value' => '1', 'label' => __('Convertible')],
                ['value' => '2', 'label' => __('Coupe')],
                ['value' => '3', 'label' => __('Hatchback')],
                ['value' => '4', 'label' => __('Limousine')],
                ['value' => '5', 'label' => __('Minivan')],
                ['value' => '6', 'label' => __('Pickup')],
                ['value' => '7', 'label' => __('Roadster')],
                ['value' => '8', 'label' => __('Sedan')],
                ['value' => '9', 'label' => __('Targa')],
                ['value' => '10', 'label' => __('Wagon')],
                ['value' => '11', 'label' => __('Touring')]
            ];
        }

        return $this->_options;
    }

    /**
     * Get text of the option value
     *
     * @param string|integer $value
     * @return string|bool
     */
    public function getOptionValue($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }

        return false;
    }
}

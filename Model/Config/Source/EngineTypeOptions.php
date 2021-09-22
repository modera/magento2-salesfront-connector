<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class EngineTypeOptions extends AbstractSource
{
    /**
     * @inheritDoc
     */
    public function getAllOptions(): array
    {
        if ($this->_options === null) {
            $this->_options = [
                ['value' => '1', 'label' => __('Petrol')],
                ['value' => '2', 'label' => __('Diesel')],
                ['value' => '3', 'label' => __('Electric')],
                ['value' => '4', 'label' => __('CNG')],
                ['value' => '5', 'label' => __('LNG')],
                ['value' => '6', 'label' => __('Hybrid')]
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

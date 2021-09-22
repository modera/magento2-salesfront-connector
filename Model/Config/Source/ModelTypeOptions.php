<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class ModelTypeOptions extends AbstractSource
{
    /**
     * @inheritDoc
     */
    public function getAllOptions(): array
    {
        if ($this->_options === null) {
            $this->_options = [
                ['value' => '1', 'label' => __('MC')],
                ['value' => '2', 'label' => __('PC')],
                ['value' => '3', 'label' => __('LCV')]
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

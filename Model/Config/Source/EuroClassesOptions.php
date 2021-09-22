<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class EuroClassesOptions extends AbstractSource
{
    /**
     * @inheritDoc
     */
    public function getAllOptions(): array
    {
        if ($this->_options === null) {
            $this->_options = [
                ['value' => '1', 'label' => __('Euro 1')],
                ['value' => '2', 'label' => __('Euro 2')],
                ['value' => '3', 'label' => __('Euro 3')],
                ['value' => '4', 'label' => __('Euro 4')],
                ['value' => '5', 'label' => __('Euro 5')],
                ['value' => '6', 'label' => __('Euro 5a')],
                ['value' => '7', 'label' => __('Euro 5b')],
                ['value' => '8', 'label' => __('Euro 6')],
                ['value' => '9', 'label' => __('Euro 6b')],
                ['value' => '10', 'label' => __('Euro 6c')],
                ['value' => '11', 'label' => __('Euro 6d')]
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

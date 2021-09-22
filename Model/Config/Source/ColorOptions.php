<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class ColorOptions extends AbstractSource
{
    /**
     * @inheritDoc
     */
    public function getAllOptions(): array
    {
        if ($this->_options === null) {
            $this->_options = [
                ['value' => '1', 'label' => __('Beige')],
                ['value' => '2', 'label' => __('Black')],
                ['value' => '3', 'label' => __('Blue')],
                ['value' => '4', 'label' => __('Brown')],
                ['value' => '5', 'label' => __('Dark blue')],
                ['value' => '6', 'label' => __('Dark green')],
                ['value' => '7', 'label' => __('Dark gray')],
                ['value' => '8', 'label' => __('Dark red')],
                ['value' => '9', 'label' => __('Gold')],
                ['value' => '10', 'label' => __('Green')],
                ['value' => '11', 'label' => __('Light blue')],
                ['value' => '12', 'label' => __('Light green')],
                ['value' => '13', 'label' => __('Light gray')],
                ['value' => '14', 'label' => __('Light yellow')],
                ['value' => '15', 'label' => __('Orange')],
                ['value' => '16', 'label' => __('Pink')],
                ['value' => '17', 'label' => __('Purple')],
                ['value' => '18', 'label' => __('Silver')],
                ['value' => '19', 'label' => __('White')],
                ['value' => '20', 'label' => __('Yellow')]
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

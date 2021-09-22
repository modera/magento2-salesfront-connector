<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Calendar extends Field
{
    /**
     * @inheritDoc
     */
    public function render(AbstractElement $element): string
    {
        $element->setDateFormat('dd-MM-yyyy');
        $element->setTimeFormat('HH:mm:ss');
        $element->setShowsTime(true);

        return parent::render($element);
    }
}

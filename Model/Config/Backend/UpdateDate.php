<?php
namespace Modera\SalesfrontConnector\Model\Config\Backend;

use Magento\Framework\App\Config\Value;

class UpdateDate extends Value
{
    /**
     * @inheritDoc
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $date = \DateTime::createFromFormat('d-m-Y h:i:s', $value);
        $this->setValue($date->getTimestamp());

        parent::beforeSave();
    }
}

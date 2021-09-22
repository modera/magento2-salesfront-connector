<?php
namespace Modera\SalesfrontConnector\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    const XML_PATH_SALESFRONT = 'salesfront/';

    /**
     * Get config value
     *
     * @param string $field
     * @param int|null $storeId
     * @return mixed
     */
    public function getConfigValue(string $field, int $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get general config
     *
     * @param string $code
     * @param int|null $storeId
     * @return mixed
     */
    public function getGeneralConfig(string $code, int $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_SALESFRONT . 'general/' . $code, $storeId);
    }
}

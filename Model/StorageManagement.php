<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Modera\SalesfrontConnector\Api\StorageManagementInterface;
use Magento\Framework\App\Cache\TypeListInterface as CacheTypeList;

class StorageManagement implements StorageManagementInterface
{
    /**
     * Vehicle update time cache XML
     */
    const VEHICLE_UPDATE_TIME_CACHE_XML_PATH = 'salesfront/info/vehicle_update_time';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * @var CacheTypeList
     */
    private $cacheTypeList;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $writer
     * @param CacheTypeList $cacheTypeList
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface $writer,
        CacheTypeList $cacheTypeList
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->writer = $writer;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * @inheritDoc
     */
    public function getCacheValidTime(): string
    {
        return $this->scopeConfig->getValue(self::VEHICLE_UPDATE_TIME_CACHE_XML_PATH) ?: '';
    }

    /**
     * @inheritDoc
     */
    public function setCacheValidTime()
    {
        $this->writer->save(self::VEHICLE_UPDATE_TIME_CACHE_XML_PATH, time());
        $this->cacheTypeList->cleanType('config');
    }
}

<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Api;

/**
 * Interface StorageManagementInterface
 */
interface StorageManagementInterface
{
    /**
     * Retrieve vehicle update time cache mark
     *
     * @return string
     */
    public function getCacheValidTime(): string;

    /**
     * Set vehicle update time cache mark
     *
     * @return void
     */
    public function setCacheValidTime();
}

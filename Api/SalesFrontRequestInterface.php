<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Api;

/**
 * Interface SalesFrontRequest
 */
interface SalesFrontRequestInterface
{
    /**
     * Create customer
     *
     * @param array $data
     * @return null|array
     */
    public function createCustomer(array $data): ?array;

    /**
     * Create lead
     *
     * @param array $data
     * @return void
     */
    public function createLead(array $data): void;

    /**
     * Create web request activity
     *
     * @param array $data
     * @return void
     */
    public function createWebRequestActivity(array $data): void;
}

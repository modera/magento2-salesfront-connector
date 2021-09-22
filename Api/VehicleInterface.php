<?php
declare(strict_types=1);

namespace Modera\SalesfrontConnector\Api;

/**
 * Interface VehicleInterface
 */
interface VehicleInterface
{
    /**
     * Create vehicle
     *
     * @return mixed
     */
    public function createVehicle();

    /**
     * Delete vehicle
     *
     * @return mixed
     */
    public function deleteVehicle();
}

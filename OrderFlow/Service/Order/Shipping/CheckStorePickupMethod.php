<?php

namespace Fisha\OrderFlow\Service\Order\Shipping;

class CheckStorePickupMethod
{
    const STORE_PICKUP_METHODS = [
        'storepickup_storepickup',
        'storedelivery_storedelivery',
    ];

    /**
     * @param string $method
     * @return bool
     */
    public function execute(string $method): bool
    {
        return in_array($method, self::STORE_PICKUP_METHODS);
    }
}

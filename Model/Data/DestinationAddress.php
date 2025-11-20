<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Data;

class DestinationAddress
{
    /**
     * Address of the shipment's destination
     *
     * @var Address
     */
    public Address $address;

    /**
     * Get the address of the shipment's destination
     *
     * @return Address
     */
    public function getAddress(): Address
    {
        return $this->address;
    }

    /**
     * Set the address of the shipment's destination
     *
     * @param Address $address
     * @return void
     */
    public function setAddress(Address $address): void
    {
        $this->address = $address;
    }
}

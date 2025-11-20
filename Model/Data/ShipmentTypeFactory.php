<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Data;

use InvalidArgumentException;

class ShipmentTypeFactory
{

    /**
     * ShipmentTypeFactory constructor.
     *
     * @param AddressToPointShipmentDtoFactory $addrToPointFactory
     * @param PointToPointShipmentDtoFactory $pointToPointFactory
     * @param PointToAddressShipmentDtoFactory $pointToAddressFactory
     * @param AddressToAddressShipmentDtoFactory $addrToAddressFactory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        private readonly AddressToPointShipmentDtoFactory $addrToPointFactory,
        private readonly PointToPointShipmentDtoFactory   $pointToPointFactory,
        private readonly PointToAddressShipmentDtoFactory $pointToAddressFactory,
        private readonly AddressToAddressShipmentDtoFactory $addrToAddressFactory
    ) {
    }

    /**
     * Create a new shipment instance
     *
     * @param string $shipmentType
     * @return ShipmentTypeInterface
     * @throws InvalidArgumentException
     */
    public function create(string $shipmentType): ShipmentTypeInterface
    {
        $factoryMap = [
            'address-to-point' => $this->addrToPointFactory,
            'point-to-point' => $this->pointToPointFactory,
            'point-to-address' => $this->pointToAddressFactory,
            'address-to-address' => $this->addrToAddressFactory,
        ];

        if (!isset($factoryMap[$shipmentType])) {
            throw new InvalidArgumentException("Invalid shipment type: $shipmentType");
        }

        return $factoryMap[$shipmentType]->create();
    }
}

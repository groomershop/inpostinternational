<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Data\Trait;

use Smartcore\InPostInternational\Model\Data\AddressDto;
use Smartcore\InPostInternational\Model\Data\DestinationAddressDto;
use Smartcore\InPostInternational\Model\Data\DestinationInterface;

/**
 * Trait for ShipmentType which supports insurance
 */
trait DestinationAddressCreatorTrait
{
    /**
     * Creates ValueAddedServices from insurance
     *
     * @param array $shipmentFieldsetData
     * @param string $destCountryType
     * @return DestinationInterface
     */
    protected function createDestinationAddress(
        array $shipmentFieldsetData,
        string $destCountryType
    ): DestinationInterface {
        /** @var AddressDto $address */
        $address = $this->abstractDtoBuilder->buildDtoInstance(AddressDto::class);
        $address
            ->setStreet($shipmentFieldsetData['street'])
            ->setHouseNumber($shipmentFieldsetData['house_number'])
            ->setFlatNumber($shipmentFieldsetData['flat_number'])
            ->setCity($shipmentFieldsetData['city'])
            ->setPostalCode($shipmentFieldsetData['postal_code'])
            ->setCountryCode($shipmentFieldsetData['destination_country_' . $destCountryType]);

        /** @var DestinationAddressDto $destinationAddress */
        $destinationAddress = $this->abstractDtoBuilder->buildDtoInstance(DestinationAddressDto::class);
        $destinationAddress->setAddress($address);

        return $destinationAddress;
    }
}

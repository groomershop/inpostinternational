<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Data\Trait;

use Smartcore\InPostInternational\Model\Data\AddressDto;
use Smartcore\InPostInternational\Model\Data\OriginDto;

/**
 * Trait for ShipmentType to create origin address
 */
trait OriginAddressCreatorTrait
{
    /**
     * Creates OriginDto from pickup address
     *
     * @param array $shipmentFieldsetData
     * @return OriginDto
     */
    protected function createOriginAddress(
        array $shipmentFieldsetData
    ): OriginDto {
        $pickupAddress = $this->pickupAddrRepository->load((int) $shipmentFieldsetData['origin']);

        /** @var AddressDto $address */
        $address = $this->abstractDtoBuilder->buildDtoInstance(AddressDto::class);
        $address->setHouseNumber($pickupAddress->getAddressHouseNumber())
            ->setFlatNumber($pickupAddress->getAddressFlatNumber())
            ->setStreet($pickupAddress->getAddressStreet())
            ->setCity($pickupAddress->getAddressCity())
            ->setPostalCode($pickupAddress->getAddressPostalCode())
            ->setCountryCode($pickupAddress->getAddressCountryCode());

        /** @var OriginDto $origin */
        $origin = $this->abstractDtoBuilder->buildDtoInstance(OriginDto::class);
        $origin->setAddress($address);

        return $origin;
    }
}

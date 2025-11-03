<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Data;

class DestinationAddressDto extends AbstractDto implements DestinationInterface
{
    /**
     * Address of the shipment's origin
     *
     * @var AddressDto|null
     */
    public ?AddressDto $address;

    /**
     * Get the address of the shipment's origin
     *
     * @return AddressDto
     */
    public function getAddress(): AddressDto
    {
        return $this->address;
    }

    /**
     * Set the address of the shipment's origin
     *
     * @param AddressDto|null $address
     * @return void
     */
    public function setAddress(?AddressDto $address): void
    {
        $this->address = $address;
    }

    /**
     * Get the type of destination
     *
     * @return string
     */
    public function getType(): string
    {
        return 'address';
    }

    /**
     * Convert to API array format
     *
     * @return array<array<string, mixed>>
     */
    public function toApiArray(): array
    {
        return [
            'address' => [
                'street' => $this->address->getStreet(),
                'house_number' => $this->address->getHouseNumber(),
                'flat_number' => $this->address->getFlatNumber(),
                'city' => $this->address->getCity(),
                'postal_code' => $this->address->getPostalCode(),
                'country_code' => $this->address->getCountryCode(),
            ]
        ];
    }

    /**
     * Convert to DB array format
     *
     * @return array<string, mixed>
     */
    public function toDbArray(): array
    {
        return [
            'destination_country_code' => $this->address->getCountryCode(),
            'destination_street' => $this->address->getStreet(),
            'destination_house_number' => $this->address->getHouseNumber(),
            'destination_flat_number' => $this->address->getFlatNumber(),
            'destination_city' => $this->address->getCity(),
            'destination_postal_code' => $this->address->getPostalCode(),
            'destination_point_name' => null,
        ];
    }
}

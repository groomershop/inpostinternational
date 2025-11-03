<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Data;

class DestinationDto extends AbstractDto implements DestinationInterface
{
    /**
     * ISO country code of the destination
     *
     * @var string
     */
    public string $countryCode;

    /**
     * Name of the destination point
     *
     * @var string
     */
    public string $pointName;

    /**
     * Get the ISO country code of the destination
     *
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * Set the ISO country code of the destination
     *
     * @param string $countryCode
     * @return $this
     */
    public function setCountryCode(string $countryCode): static
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    /**
     * Get the name of the destination point
     *
     * @return string
     */
    public function getPointName(): string
    {
        return $this->pointName;
    }

    /**
     * Set the name of the destination point
     *
     * @param string $pointName
     * @return $this
     */
    public function setPointName(string $pointName): static
    {
        $this->pointName = $pointName;
        return $this;
    }

    /**
     * Get destination type
     *
     * @return string
     */
    public function getType(): string
    {
        return 'point';
    }

    /**
     * Convert to API array format
     *
     * @return array<array<string, mixed>>
     */
    public function toApiArray(): array
    {
        return [
            'point' => [
                'name' => $this->pointName
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
            'destination_country_code' => $this->countryCode,
            'destination_point_name' => $this->pointName,
            'destination_street' => null,
            'destination_house_number' => null,
            'destination_flat_number' => null,
            'destination_city' => null,
            'destination_postal_code' => null,
        ];
    }
}

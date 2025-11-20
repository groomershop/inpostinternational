<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Data\Trait;

use Smartcore\InPostInternational\Model\Data\InsuranceDto;
use Smartcore\InPostInternational\Model\Data\ValueAddedServicesDto;

/**
 * Trait for ShipmentType which supports insurance
 */
trait InsuranceCreatorTrait
{
    /**
     * Creates ValueAddedServices from insurance
     *
     * @param array $shipmentFieldsetData
     * @return ValueAddedServicesDto|null
     */
    protected function createValueAddedServicesWithInsurance(
        array $shipmentFieldsetData
    ): ?ValueAddedServicesDto {
        /** @var InsuranceDto $insurance */
        $insurance = $this->abstractDtoBuilder->buildDtoInstance(InsuranceDto::class);
        $insurance->setValue((float) $shipmentFieldsetData['insurance_value'])
            ->setCurrency($shipmentFieldsetData['insurance_currency']);

        /** @var ValueAddedServicesDto $valueAddedServices */
        $valueAddedServices = $this->abstractDtoBuilder->buildDtoInstance(ValueAddedServicesDto::class);
        $valueAddedServices->setInsurance($insurance);

        return $valueAddedServices;
    }
}

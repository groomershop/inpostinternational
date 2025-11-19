<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Config\Source;

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;
use Smartcore\InPostInternational\Model\Config\CountrySettings;
use Smartcore\InPostInternational\Model\ConfigProvider;

class DestinationCountryPointToPoint extends DestinationCountry implements OptionSourceInterface
{
    /**
     * Shipping methods mapper
     *
     * @param ConfigProvider $configProvider
     * @param CountryCollectionFactory $countryCollFactory
     * @param CountrySettings $countrySettings
     */
    public function __construct(
        protected ConfigProvider           $configProvider,
        protected CountryCollectionFactory $countryCollFactory,
        protected CountrySettings          $countrySettings,
    ) {
        parent::__construct($configProvider, $countryCollFactory);
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        $options = parent::toOptionArray();
        $allowedCountries = $this->countrySettings->getCountryCanInPostShipPointToPointSettings();
        return array_filter(
            $options,
            static fn ($option) => in_array($option['value'], array_keys($allowedCountries), true)
        );
    }
}

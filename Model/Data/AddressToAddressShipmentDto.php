<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Data;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Smartcore\InPostInternational\Model\Data\Trait\DestinationAddressCreatorTrait;
use Smartcore\InPostInternational\Model\Data\Trait\OriginAddressCreatorTrait;
use Smartcore\InPostInternational\Model\InPostShipment as ShipmentModel;
use Smartcore\InPostInternational\Model\InPostShipmentFactory;
use Smartcore\InPostInternational\Model\PickupAddressRepository;

class AddressToAddressShipmentDto extends ShipmentTypeDto implements ShipmentTypeInterface
{
    use DestinationAddressCreatorTrait;
    use OriginAddressCreatorTrait;
    public const ADDRESS_TO_ADDRESS = 'address-to-address';
    public const LABEL = 'From address to address (courier)';

    /**
     * @param InPostShipmentFactory $shipmentFactory
     * @param AbstractDtoBuilder $abstractDtoBuilder
     * @param PickupAddressRepository $pickupAddrRepository
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     */
    public function __construct(
        readonly InPostShipmentFactory $shipmentFactory,
        private readonly AbstractDtoBuilder $abstractDtoBuilder,
        private readonly PickupAddressRepository $pickupAddrRepository,
        Context $context,
        Registry $registry,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null
    ) {
        parent::__construct($shipmentFactory, $context, $registry, $resource, $resourceCollection);
    }

    /**
     * Get API endpoint for shipment type
     *
     * @return string
     */
    public function getEndpoint(): string
    {
        return self::ADDRESS_TO_ADDRESS;
    }

    /**
     * Get the label for the shipment type
     *
     * @return string
     */
    public function getLabel(): string
    {
        return self::LABEL;
    }

    /**
     * Convert shipment data to database model
     *
     * @return ShipmentModel
     */
    public function toDbModel(): ShipmentModel
    {
        $shipmentDbModel = parent::toDbModel();
        $shipment = $this->getShipment();
        $originAddress = $shipment->getOrigin()->getAddress();

        $shipmentDbModel
            ->setOriginHouseNumber($originAddress->getHouseNumber())
            ->setOriginFlatNumber($originAddress->getFlatNumber())
            ->setOriginStreet($originAddress->getStreet())
            ->setOriginCity($originAddress->getCity())
            ->setOriginPostalCode($originAddress->getPostalCode())
            ->setOriginCountryCode($originAddress->getCountryCode());

        return $shipmentDbModel;
    }

    /**
     * Create origin object
     *
     * @param array<string,mixed> $shipmentFieldsetData
     */
    public function createOrigin(array $shipmentFieldsetData): OriginDto
    {
        return $this->createOriginAddress($shipmentFieldsetData);
    }

    /**
     * Get label format
     *
     * @return string
     */
    public function getLabelFormat(): string
    {
        return $this->getData(self::LABEL_FORMAT);
    }

    /**
     * Set label format
     *
     * @param string $labelFormat
     * @return $this
     */
    public function setLabelFormat(string $labelFormat): self
    {
        $this->setData(self::LABEL_FORMAT, $labelFormat);
        return $this;
    }

    /**
     * Get shipment data
     *
     * @return ShipmentDto
     */
    public function getShipment(): ShipmentDto
    {
        return $this->getData(self::SHIPMENT);
    }

    /**
     * Set shipment data
     *
     * @param ShipmentDto $shipment
     * @return $this
     */
    public function setShipment(ShipmentDto $shipment): self
    {
        $this->setData(self::SHIPMENT, $shipment);
        return $this;
    }

    /**
     * Create destination object
     *
     * @param array<string,mixed> $shipmentFieldsetData
     */
    public function createDestination(array $shipmentFieldsetData): DestinationInterface
    {
        return $this->createDestinationAddress($shipmentFieldsetData);
    }

    /**
     * Create value added services object
     *
     * @param array $shipmentFieldsetData
     * @return ValueAddedServicesDto|null
     */
    public function createValueAddedServices(array $shipmentFieldsetData): ?ValueAddedServicesDto
    {
        return null;
    }
}

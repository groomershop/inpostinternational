<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Carrier;

use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Item;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Smartcore\InPostInternational\Model\Config\Source\PriceCalculationType;
use Smartcore\InPostInternational\Model\Config\Source\WeightOutOfRange;
use Smartcore\InPostInternational\Model\ResourceModel\WeightPrice\CollectionFactory as WeightPriceCollectionFactory;
use Smartcore\InPostInternational\Setup\Patch\Data\AddProductBlockPointsAttribute;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractInternationalCourier extends AbstractCarrier implements CarrierInterface
{
    /**
     * Carrier code
     *
     * @var string
     */
    protected $_code = '';

    /**
     * Shipping method code
     *
     * @var string
     */
    protected string $_method = '';

    /**
     * List of allowed countries for shipping
     *
     * @var array<string>
     */
    protected array $countryAllowed = [];

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param StoreManagerInterface $storeManager
     * @param ProductResource $productResource
     * @param WeightPriceCollectionFactory $weightPriceColFactor
     * @param array $data
     */
    public function __construct(
        protected ScopeConfigInterface         $scopeConfig,
        ErrorFactory                           $rateErrorFactory,
        LoggerInterface                        $logger,
        protected ResultFactory                $rateResultFactory,
        protected MethodFactory                $rateMethodFactory,
        protected StoreManagerInterface        $storeManager,
        protected ProductResource              $productResource,
        protected WeightPriceCollectionFactory $weightPriceColFactor,
        array                                  $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * Check if tracking is available for the carrier
     *
     * @return bool
     */
    public function isTrackingAvailable(): bool
    {
        return true;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array<string>
     */
    public function getAllowedMethods(): array
    {
        return [$this->_method => $this->getConfigData('name')];
    }

    /**
     * Collect and get shipping rates based on the request
     *
     * @param RateRequest $request
     * @return Result|bool
     * @throws NoSuchEntityException
     */
    public function collectRates(RateRequest $request): Result|bool
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        if (!$this->isShippingMethodAvailable($request)) {
            return false;
        }

        $result = $this->rateResultFactory->create();
        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod($this->_method);
        $method->setMethodTitle($this->getConfigData('name'));

        $shippingCost = $this->calculateShippingCost($request);
        if ($shippingCost === null) {
            return false;
        }
        $method->setPrice($shippingCost);
        $method->setCost($shippingCost);

        $result->append($method);
        return $result;
    }

    /**
     * Check if shipping is available
     *
     * @param RateRequest $request
     * @return bool
     * @throws NoSuchEntityException
     */
    protected function isShippingMethodAvailable(RateRequest $request): bool
    {
        $items = $request->getAllItems();
        if (empty($items)) {
            return true;
        }

        $destCountry = $request->getDestCountryId();
        if (!$this->isCountryAllowed($destCountry)) {
            return false;
        }

        $storeId = $this->storeManager->getStore()->getId();

        /** @var Item $item */
        foreach ($items as $item) {
            $blockShip = $this->productResource->getAttributeRawValue(
                $item->getProduct()->getId(),
                AddProductBlockPointsAttribute::ATTRIBUTE_CODE,
                $storeId
            );

            if ($blockShip) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate shipping cost
     *
     * @param RateRequest $request
     * @return float|null
     */
    protected function calculateShippingCost(RateRequest $request): ?float
    {
        $calculationType = $this->getConfigData('price_calculation_type');

        if ($this->getConfigFlag('free_shipping_enable')) {
            $freeShippingAmount = (float) $this->getConfigData('free_shipping_subtotal');
            $freeAmountInclTax = $this->getConfigFlag('free_shipping_subtotal_incl_tax');
            $subtotal = $this->getQuoteTotal($request, $freeAmountInclTax);
            if ($subtotal >= $freeShippingAmount) {
                return 0;
            }
        }

        if ($calculationType === PriceCalculationType::WEIGHT) {
            $totalWeight = 0;
            $weightAttr = $this->scopeConfig->getValue(
                'shipping/inpostinternational/weight_attribute_code',
                ScopeInterface::SCOPE_STORE
            ) ?: 'weight';

            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->getTypeId() === 'configurable') {
                    continue;
                }
                $weight = (float)$item->getProduct()->getData($weightAttr);
                $qty = (float)$item->getQty();
                $totalWeight += $weight * $qty;
            }

            $collection = $this->weightPriceColFactor->create();
            $collection
                ->addFieldToFilter('weight_from', ['lteq' => $totalWeight])
                ->addFieldToFilter(
                    ['weight_to', 'weight_to'],
                    [
                        ['gteq' => $totalWeight],
                        ['null' => true]
                    ]
                )
                ->setPageSize(1);

            $weightPrice = $collection->getFirstItem();
            if ($weightPrice->getId()) {
                return (float)$weightPrice->getPrice();
            }
        }

        $weightOutOfRange = $this->getConfigData('weight_out_of_range');
        if ($weightOutOfRange === WeightOutOfRange::BLOCK_SHIP) {
            return null;
        }

        return (float)$this->getConfigData('price');
    }

    /**
     * Get quote total
     *
     * @param RateRequest $request
     * @param bool $inclTax
     * @return float
     */
    protected function getQuoteTotal(RateRequest $request, bool $inclTax): float
    {
        $discountAmount = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }
                $discountAmount += $item->getBaseDiscountAmount();
            }

            if (!isset($item)) {
                return 0;
            }

            $subTotal = $request->getBaseSubtotalInclTax();
            if (!$inclTax) {
                $subTotal = $item->getQuote()->getBaseSubtotal();
            }
            $total = $subTotal - $discountAmount;

            return (float) $total;
        }

        return 0;
    }

    /**
     * Check if the destination country is allowed for shipping
     *
     * @param string $destCountry
     * @return bool
     */
    private function isCountryAllowed(string $destCountry): bool
    {
        return in_array($destCountry, $this->countryAllowed, true);
    }

    /**
     * Get all allowed countries for shipping
     *
     * @return string[]
     */
    public function getAllAllowedCountries(): array
    {
        return $this->countryAllowed;
    }
}

<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Carrier;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MondialRelayCourier extends AbstractInternationalCourier
{

    /**
     * @var string
     */
    protected $_code = 'mondialrelaycourier';

    /**
     * @var string
     */
    protected string $_method = 'courier';

    /**
     * @var array<string>
     */
    protected array $countryAllowed = ['NL', 'FR', 'BE', 'LU'];
}

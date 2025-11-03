<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class OriginType implements OptionSourceInterface
{
    public const ORIGIN_POINT = 'point';
    public const ORIGIN_ADDRESS = 'address';

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::ORIGIN_POINT,
                'label' => __('Point (Parcel Locker)')
            ],
            [
                'value' => self::ORIGIN_ADDRESS,
                'label' => __('Address (Courier Pickup)')
            ]
        ];
    }
}

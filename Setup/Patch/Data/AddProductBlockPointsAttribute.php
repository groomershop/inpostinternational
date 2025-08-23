<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\Source\Boolean;

/**
 * Class AddProductBlockPointsAttribute, which adds an attribute block_inpostinternational_points to the product
 */
class AddProductBlockPointsAttribute extends AbstractProductAttributePatch
{
    protected const GROUP_NAME = 'General';
    protected const SORT_ORDER = 33766;

    /**
     * Attribute code
     *
     * @return string
     */
    protected static function attributeCode(): string
    {
        return 'block_inpostinternational_points';
    }

    /**
     * Attribute definition
     *
     * @return string[]
     */
    protected function attributeDefinition(): array
    {
        return [
            'type'    => 'int',
            'label'   => 'Block send to InPost International Points',
            'input'   => 'boolean',
            'source'  => Boolean::class,
            'default' => 0,
        ];
    }
}

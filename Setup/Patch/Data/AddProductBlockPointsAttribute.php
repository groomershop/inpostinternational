<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Validator\ValidateException;
use Psr\Log\LoggerInterface;

/**
 * Data patch: adds product attribute 'block_inpostinternational_points'.
 */
class AddProductBlockPointsAttribute implements DataPatchInterface, PatchRevertableInterface
{
    protected const ATTRIBUTE_CODE = 'block_inpostinternational_points';
    protected const GROUP_NAME = 'General';
    protected const SORT_ORDER = 33766;

    /**
     * Constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected ModuleDataSetupInterface $moduleDataSetup,
        protected EavSetupFactory $eavSetupFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Apply patch: ensure attribute exists and assigned to all product attribute sets.
     *
     * @return $this
     * @throws LocalizedException
     * @throws ValidateException
     */
    public function apply(): self
    {
        $this->db()->startSetup();
        try {
            $this->ensureAttributeExistsAndAssigned();
        } finally {
            $this->db()->endSetup();
        }
        return $this;
    }

    /**
     * Revert patch: remove attribute if exists.
     */
    public function revert(): void
    {
        $this->db()->startSetup();
        try {
            $this->safeRemoveAttribute(self::ATTRIBUTE_CODE);
        } finally {
            $this->db()->endSetup();
        }
    }

    /**
     * Defines the attribute properties.
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

    /**
     * Ensures the attribute exists and is assigned to all attribute sets.
     *
     * @return void
     * @throws LocalizedException
     * @throws ValidateException
     */
    protected function ensureAttributeExistsAndAssigned(): void
    {
        $eav = $this->eav();

        $existing = $eav->getAttribute(Product::ENTITY, self::ATTRIBUTE_CODE);
        if (!$existing || empty($existing['attribute_id'])) {
            $definition = $this->withSafeDefaults($this->attributeDefinition());
            $eav->addAttribute(Product::ENTITY, self::ATTRIBUTE_CODE, $definition);
        }

        $this->assignToAllSets(self::ATTRIBUTE_CODE, static::GROUP_NAME, static::SORT_ORDER);
    }

    /**
     * Assigns the attribute to all attribute sets under the specified group name and sort order.
     *
     * @param string $code
     * @param string $groupName
     * @param int $sortOrder
     * @return void
     * @throws LocalizedException
     */
    protected function assignToAllSets(string $code, string $groupName, int $sortOrder): void
    {
        $eav = $this->eav();
        $entityTypeId = (int) $eav->getEntityTypeId(Product::ENTITY);

        $sets = $this->db()->fetchCol(
            $this->db()->select()
                ->from($this->table('eav_attribute_set'), ['attribute_set_id'])
                ->where('entity_type_id = ?', $entityTypeId)
        );

        foreach ($sets as $setId) {
            $groupId = $this->getOrCreateAttributeGroupId($eav, $entityTypeId, (int)$setId, $groupName);

            if (!$groupId) {
                $this->logger->error(sprintf(
                    'Failed to create or retrieve attribute group "%s" for set ID %d',
                    $groupName,
                    (int)$setId
                ));
                continue;
            }

            try {
                $eav->addAttributeToGroup(
                    $entityTypeId,
                    (int)$setId,
                    $groupId,
                    $code,
                    $sortOrder
                );
            } catch (\Throwable $e) {
                $this->logger->warning(sprintf(
                    'Attribute %s already in a group for set ID %d: %s',
                    $code,
                    (int)$setId,
                    $e->getMessage()
                ));
            }
        }
    }

    /**
     * Retrieves the attribute group ID for the specified entity type, set ID, and group name. Eventually creates new.
     *
     * @param EavSetup $eav
     * @param int $entityTypeId
     * @param int $setId
     * @param string $groupName
     * @return int|null
     * @throws LocalizedException
     */
    private function getOrCreateAttributeGroupId(EavSetup $eav, int $entityTypeId, int $setId, string $groupName): ?int
    {
        /** @var int|string|false|null $groupId */
        $groupId = $eav->getAttributeGroupId($entityTypeId, $setId, $groupName);

        if (!is_int($groupId) && is_numeric($groupId)) {
            $groupId = (int) $groupId;
        } elseif (!is_int($groupId)) {
            $groupId = null;
        }

        if (!$groupId) {
            $eav->addAttributeGroup($entityTypeId, $setId, $groupName);
            /** @var int|string|false|null $groupId */
            $groupId = $eav->getAttributeGroupId($entityTypeId, $setId, $groupName);

            if (!is_int($groupId) && is_numeric($groupId)) {
                $groupId = (int) $groupId;
            } elseif (!is_int($groupId)) {
                $groupId = null;
            }
        }

        return $groupId ?: null;
    }

    /**
     * Safely removes the attribute if it exists.
     *
     * @param string $code
     * @return void
     */
    protected function safeRemoveAttribute(string $code): void
    {
        $eav = $this->eav();
        if ($eav->getAttribute(Product::ENTITY, $code)) {
            $eav->removeAttribute(Product::ENTITY, $code);
        }
    }

    /**
     * Merges the provided attribute definition with safe default values.
     *
     * @param array $def
     * @return array
     */
    protected function withSafeDefaults(array $def): array
    {
        return $def + [
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'visible_on_front' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'used_in_product_listing' => false,
                'sort_order' => static::SORT_ORDER,
                'required' => false,
            ];
    }

    /**
     * Creates and returns an instance of EavSetup.
     *
     * @return EavSetup
     */
    protected function eav(): EavSetup
    {
        return $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
    }

    /**
     * Returns the database connection adapter.
     *
     * @return AdapterInterface
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    protected function db(): AdapterInterface
    {
        return $this->moduleDataSetup->getConnection();
    }

    /**
     * Returns the full table name for the given table identifier.
     *
     * @param string $name
     * @return string
     */
    protected function table(string $name): string
    {
        return $this->moduleDataSetup->getTable($name);
    }

    /**
     * Get dependencies.
     *
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get aliases.
     *
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}

<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Data;

interface DestinationInterface
{
    /**
     * Returns the type of destination
     *
     * @return string 'point' or 'address'
     */
    public function getType(): string;

    /**
     * Converts the destination to the format for the API payload
     *
     * @return array<array<string, mixed>>
     */
    public function toApiArray(): array;

    /**
     * Returns the destination in the format for database storage. Each implementation returns the appropriate fields.
     *
     * @return array<string, mixed>
     */
    public function toDbArray(): array;
}

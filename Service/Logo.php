<?php
declare(strict_types=1);

namespace Smartcore\InPostInternational\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\ScopeInterface;

class Logo
{
    private const XML_PATH_LOGO = 'carriers/%s/logo';
    private const XML_PATH_DEFAULT_LOGO = 'carriers/%s/default_logo';

    /**
     * Logo constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Repository $assetRepository
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly Repository $assetRepository,
        private readonly UrlInterface $urlBuilder
    ) {
    }

    /**
     * Get carrier logo URL
     *
     * @param string|null $carrierCode
     * @param int|null $storeId
     * @return string
     */
    public function getLogoUrl(string $carrierCode = null, ?int $storeId = null): string
    {
        $logoPath = $this->getConfiguredLogoPath(self::XML_PATH_LOGO, $carrierCode, $storeId);
        if ($logoPath) {
            return $this->buildMediaUrl('inpostinternational/logo/' . $logoPath);
        }

        $defaultLogoPath = $this->getConfiguredLogoPath(self::XML_PATH_DEFAULT_LOGO, $carrierCode, $storeId);
        return $this->assetRepository->getUrl($defaultLogoPath);
    }

    /**
     * Get configured logo path from the scope configuration
     *
     * @param string $configPathTemplate
     * @param string|null $carrierCode
     * @param int|null $storeId
     * @return string|null
     */
    private function getConfiguredLogoPath(string $configPathTemplate, ?string $carrierCode, ?int $storeId): ?string
    {
        return $this->scopeConfig->getValue(
            sprintf($configPathTemplate, $carrierCode),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Build the full media URL for the given relative path
     *
     * @param string $relativePath
     * @return string
     */
    private function buildMediaUrl(string $relativePath): string
    {
        return $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . ltrim($relativePath, '/');
    }
}

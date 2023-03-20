<?php

namespace Growcode\Omnibus\ViewModel\Product;

use Growcode\Omnibus\Api\Data\HistoricalPriceInterface;
use Growcode\Omnibus\Model\ResourceModel\HistoricalPrice\CollectionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\ResourceModel\Rule as CatalogRule;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class HistoricalPrice implements ArgumentInterface
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    private PriceCurrencyInterface $priceCurrency;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var CatalogRule
     */
    private CatalogRule $catalogRule;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var FormatInterface 
     */
    private FormatInterface $localeFormat;

    /**
     * @param CollectionFactory $collectionFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param StoreManagerInterface $storeManager
     * @param CatalogRule $catalogRule
     * @param ProductRepositoryInterface $productRepository
     * @param FormatInterface $localeFormat
     */
    public function __construct(
        CollectionFactory          $collectionFactory,
        PriceCurrencyInterface     $priceCurrency,
        StoreManagerInterface      $storeManager,
        CatalogRule                $catalogRule,
        ProductRepositoryInterface $productRepository,
        FormatInterface            $localeFormat
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
        $this->catalogRule = $catalogRule;
        $this->productRepository = $productRepository;
        $this->localeFormat = $localeFormat;
    }

    /**
     * @param int $productId
     * @return HistoricalPriceInterface
     */
    public function getLowestHistoricalPrice(int $productId): ?HistoricalPriceInterface
    {
        try {
            $websiteId = $this->storeManager->getWebsite()->getId();
        } catch (LocalizedException $e) {
            $websiteId = 0;
        }

        $collection = $this->collectionFactory->create();
        $collection->addFilter(HistoricalPriceInterface::PRODUCT_ID, $productId)
            ->addFilter(HistoricalPriceInterface::WEBSITE_ID, $websiteId)
            ->setOrder('price', Collection::SORT_ORDER_ASC);

        if ($collection->count()) {
            /** @var HistoricalPriceInterface */
            return $collection->getFirstItem();
        }

        $collection = $this->collectionFactory->create();
        $collection->addFilter(HistoricalPriceInterface::PRODUCT_ID, $productId)
            ->addFilter(HistoricalPriceInterface::WEBSITE_ID, 0)
            ->setOrder('price', Collection::SORT_ORDER_ASC);

        if (!$collection->count()) {
            return null;
        }

        /** @var HistoricalPriceInterface */
        return $collection->getFirstItem();
    }

    /**
     * @param float $price
     * @param bool $includeContainer
     * @return string
     */
    public function formatPrice(float $price, bool $includeContainer = true): string
    {
        return $this->priceCurrency->format($price, $includeContainer);
    }

    /**
     * @param ProductInterface $product
     * @return bool
     */
    public function hasDiscount(ProductInterface $product): bool
    {
        try {
            $websiteId = $this->storeManager->getWebsite()->getId();
        } catch (LocalizedException $e) {
            $websiteId = 0;
        }

        if ($product->getTypeId() == 'configurable') {
             $groups = $product->getTypeInstance()->getChildrenIds($product->getId());

            foreach ($groups as $group) {
                foreach ($group as $childId) {
                    try {
                        $childProduct = $this->productRepository->getById($childId);
                        $childCatalogRules = $this->catalogRule->getRulesFromProduct(time(), $websiteId, 0, $childId);

                        if ($childProduct->getSpecialPrice() || !empty($childCatalogRules)) {
                            return true;
                        }
                    } catch (NoSuchEntityException $e) {
                        continue;
                    }
                }
            }
        } else {
            $catalogRules = $this->catalogRule->getRulesFromProduct(time(), $websiteId, 0, $product->getId());

            return $product->getSpecialPrice() || !empty($catalogRules);
        }

        return false;
    }

    /**
     * @param Product $product
     * @return false|string
     */
    public function getConfigurableProductLowestHistoricalPrices(Product $product)
    {
        $groups = $product->getTypeInstance()->getChildrenIds($product->getId());
        $prices = [];

        foreach ($groups as $group) {
            foreach ($group as $childId) {
                $lowestHistoricalPrice = $this->getLowestHistoricalPrice($childId);

                if ($lowestHistoricalPrice) {
                    $prices[$childId] = $lowestHistoricalPrice->getPrice();
                } else {
                    try {
                        $childProduct = $this->productRepository->getById($childId);
                        $prices[$childId] = $childProduct->getFinalPrice();
                    } catch (NoSuchEntityException $e) {
                        continue;
                    }
                }
            }
        }

        return json_encode($prices);
    }

    /**
     * @return false|string
     */
    public function getPriceFormat()
    {
        return json_encode($this->localeFormat->getPriceFormat());
    }
}

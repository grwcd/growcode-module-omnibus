<?php

namespace Growcode\Omnibus\ViewModel\Product;

use Growcode\Omnibus\Api\Data\HistoricalPriceInterface;
use Growcode\Omnibus\Model\ResourceModel\HistoricalPrice\Collection as HistoricalPriceCollection;
use Growcode\Omnibus\Model\ResourceModel\HistoricalPrice\CollectionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\CatalogRule\Model\ResourceModel\Rule as CatalogRule;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
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
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var FormatInterface
     */
    private FormatInterface $localeFormat;

    /**
     * @var LayoutInterface
     */
    private LayoutInterface $layout;

    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $timezone;

    /**
     * @param CollectionFactory $collectionFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param StoreManagerInterface $storeManager
     * @param CatalogRule $catalogRule
     * @param ProductRepositoryInterface $productRepository
     * @param FormatInterface $localeFormat
     * @param LayoutInterface $layout
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        CollectionFactory          $collectionFactory,
        PriceCurrencyInterface     $priceCurrency,
        StoreManagerInterface      $storeManager,
        CatalogRule                $catalogRule,
        ProductRepositoryInterface $productRepository,
        FormatInterface            $localeFormat,
        LayoutInterface            $layout,
        TimezoneInterface          $timezone
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
        $this->catalogRule = $catalogRule;
        $this->productRepository = $productRepository;
        $this->localeFormat = $localeFormat;
        $this->layout = $layout;
        $this->timezone = $timezone;
    }

    /**
     * @param int $productId
     * @return HistoricalPriceInterface|null
     */
    public function getLowestHistoricalPrice(int $productId): ?HistoricalPriceInterface
    {
        try {
            $websiteId = $this->storeManager->getWebsite()->getId();
        } catch (LocalizedException $e) {
            $websiteId = 0;
        }

        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        if ($product->getTypeId() === Configurable::TYPE_CODE) {
            $groups = $product->getTypeInstance()->getChildrenIds($product->getId());
            $productIds = array_merge(...$groups);
        } else {
            $productIds = [$productId];
        }

        $collection = $this->searchHistoricalPrices($productIds, $websiteId);

        return $collection->count() ? $this->getFirstPrice($collection, $productId) : null;
    }

    private function searchHistoricalPrices(array $productIds, $websiteId): ?HistoricalPriceCollection
    {
        /** @var HistoricalPriceCollection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(HistoricalPriceInterface::PRODUCT_ID, ['in' => $productIds])
            ->addFilter(HistoricalPriceInterface::WEBSITE_ID, $websiteId)
            ->setOrder('price', Collection::SORT_ORDER_ASC);

        if (!$collection->count()) {
            /** @var HistoricalPriceCollection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(HistoricalPriceInterface::PRODUCT_ID, ['in' => $productIds])
                ->addFilter(HistoricalPriceInterface::WEBSITE_ID, 0)
                ->setOrder('price', Collection::SORT_ORDER_ASC);
        }

        return $collection;
    }

    /**
     * @param Collection $collection
     * @param int $productId
     * @return HistoricalPriceInterface|null
     */
    public function getFirstPrice(Collection $collection, int $productId): ?HistoricalPriceInterface
    {
        try {
            $parentProduct = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            /** @var HistoricalPriceInterface */
            return $collection->getFirstItem();
        }

        $product = $parentProduct;

        /** @var HistoricalPriceInterface $item */
        foreach ($collection as $item) {
            if ($parentProduct->getTypeId() === Configurable::TYPE_CODE) {
                try {
                    $product = $this->productRepository->getById($item->getProductId());
                } catch (NoSuchEntityException $e) {
                    continue;
                }
            }

            if ($item->getPrice() != $product->getFinalPrice()) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param float $price
     * @param bool $includeContainer
     * @return string
     */
    public function formatPrice(float $price, bool $includeContainer = true): string
    {
        return $this->priceCurrency->convertAndFormat($price, $includeContainer);
    }

    /**
     * @param ProductInterface $product
     * @param bool $longMessage
     * @param int $quantity
     * @return string
     */
    public function getHistoricalPriceItemHtml(ProductInterface $product, bool $longMessage = false, int $quantity = 1)
    {
        if (!($block = $this->layout->getBlock('item.historical.price'))) {
            $block = $this->layout->createBlock(Template::class, 'item.historical.price')
                ->setTemplate('Growcode_Omnibus::list/historical_price.phtml')
                ->setHistoricalPrice($this)
                ->setQuantity($quantity);
        }

        return $block->setProduct($product)->setDisplayLongMessage($longMessage)->toHtml();
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

                        if (!empty($childCatalogRules) || $this->isSpecialPriceActive($childProduct)) {
                            return true;
                        }
                    } catch (NoSuchEntityException $e) {
                        continue;
                    }
                }
            }
        } else {
            $catalogRules = $this->catalogRule->getRulesFromProduct(time(), $websiteId, 0, $product->getId());

            return !empty($catalogRules) || $this->isSpecialPriceActive($product);
        }

        return false;
    }

    /**
     * @param Product $product
     * @return false|string
     */
    public function getConfigurableProductLowestHistoricalPrices(Product $product): false|string
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
                        $prices[$childId] = $childProduct->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getValue();
                    } catch (NoSuchEntityException $e) {
                        continue;
                    }
                }
            }
        }

        return json_encode($prices);
    }

    /**
     * @param ProductInterface $product
     * @return bool
     */
    private function isSpecialPriceActive(ProductInterface $product): bool
    {
        $specialFromDate = $product->getSpecialFromDate();
        $specialToDate = $product->getSpecialToDate();

        return $product->getSpecialPrice()
            && $this->timezone->isScopeDateInInterval(
                $product->getStoreId(),
                $specialFromDate,
                $specialToDate
            );
    }

    /**
     * @return false|string
     */
    public function getPriceFormat()
    {
        return json_encode($this->localeFormat->getPriceFormat());
    }
}

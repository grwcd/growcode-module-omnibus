<?php

namespace Growcode\Omnibus\Service;

use DateTime;
use Growcode\Omnibus\Api\Data\HistoricalPriceInterfaceFactory;
use Growcode\Omnibus\Api\HistoricalPriceRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogRule\Model\ResourceModel\Rule as CatalogRule;

class HistoricalPrice
{
    /**
     * @var HistoricalPriceRepositoryInterface
     */
    protected HistoricalPriceRepositoryInterface $historicalPriceRepository;

    /**
     * @var HistoricalPriceInterfaceFactory
     */
    protected HistoricalPriceInterfaceFactory $historicalPriceFactory;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var CatalogRule
     */
    protected CatalogRule $catalogRule;

    /**
     * @param HistoricalPriceInterfaceFactory $historicalPriceFactory
     * @param HistoricalPriceRepositoryInterface $historicalPriceRepository
     * @param StoreManagerInterface $storeManager
     * @param CatalogRule $catalogRule
     */
    public function __construct(
        HistoricalPriceInterfaceFactory $historicalPriceFactory,
        HistoricalPriceRepositoryInterface $historicalPriceRepository,
        StoreManagerInterface $storeManager,
        CatalogRule $catalogRule
    )
    {
        $this->historicalPriceRepository = $historicalPriceRepository;
        $this->historicalPriceFactory = $historicalPriceFactory;
        $this->storeManager = $storeManager;
        $this->catalogRule = $catalogRule;
    }

    /**
     * @throws LocalizedException
     */
    public function verifyPriceChange(ProductInterface $product)
    {
        $price = $product->getPrice();
        $origPrice = floatval($product->getOrigData('price'));

        if ($price !== $origPrice) {
            $this->savePriceChange($product, $price);
        }

        if ($specialPrice = $product->getSpecialPrice()) {
            $origSpecialPrice = $product->getOrigData('special_price');

            if ($specialPrice !== $origSpecialPrice) {
                $this->savePriceChange($product, $specialPrice);
            }
        }
    }

    /**
     * @throws LocalizedException
     */
    protected function savePriceChange(ProductInterface $product, float $price)
    {
        $websiteId = $this->storeManager->getWebsite()->getId();
        $catalogRules = $this->catalogRule->getRulesFromProduct(time(), $websiteId, 0, $product->getId());

        if (!empty($catalogRules)) {
            $catalogPrice = $this->catalogRule->getRulePrice(new DateTime(), $websiteId, 0, $product->getId());

            if ($catalogPrice && $catalogPrice < $price) {
                $price = $catalogPrice;
            }
        }

        $model = $this->historicalPriceFactory->create()
            ->setProductId($product->getId())
            ->setPrice($price)
            ->setWebsiteId($websiteId);
        $this->historicalPriceRepository->save($model);
    }
}

<?php

namespace Growcode\Omnibus\Observer;

use Growcode\Omnibus\Service\HistoricalPrice;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class ProductSaveAfter implements ObserverInterface
{
    private HistoricalPrice $historicalPrice;

    /**
     * @param HistoricalPrice $historicalPrice
     */
    public function __construct(HistoricalPrice $historicalPrice)
    {
        $this->historicalPrice = $historicalPrice;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getProduct();
        $price = $product->getPrice();
        $origPrice = floatval($product->getOrigData('price'));

        if (empty($price) || empty($origPrice)) {
            return;
        }

        $this->historicalPrice->verifyPriceChange($product);
    }
}

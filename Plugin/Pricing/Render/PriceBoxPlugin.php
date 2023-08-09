<?php

namespace Growcode\Omnibus\Plugin\Pricing\Render;

use Growcode\Omnibus\ViewModel\Product\HistoricalPrice;
use Magento\Framework\Pricing\Render\PriceBox;

class PriceBoxPlugin
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
     * @param PriceBox $subject
     * @param ...$args
     * @return array[]
     */
    public function beforeToHtml(PriceBox $subject, ...$args)
    {
        $subject->setData('historical_price', $this->historicalPrice);

        return [$args];
    }
}

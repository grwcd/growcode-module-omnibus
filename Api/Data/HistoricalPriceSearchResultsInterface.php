<?php

namespace Growcode\Omnibus\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface HistoricalPriceSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return HistoricalPriceInterface[]
     */
    public function getItems(): array;

    /**
     * @param array $items
     * @return HistoricalPriceSearchResultsInterface
     */
    public function setItems(array $items): HistoricalPriceSearchResultsInterface;
}

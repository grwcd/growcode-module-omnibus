<?php

namespace Growcode\Omnibus\Api;

use Growcode\Omnibus\Api\Data\HistoricalPriceInterface;
use Growcode\Omnibus\Api\Data\HistoricalPriceSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface HistoricalPriceRepositoryInterface
{
    /**
     * Save catalog_product_price_history
     * @param HistoricalPriceInterface $historicalPrice
     * @return HistoricalPriceInterface
     * @throws LocalizedException
     */
    public function save(HistoricalPriceInterface $historicalPrice): HistoricalPriceInterface;

    /**
     * Retrieve catalog_product_price_history
     * @param int $historyId
     * @return HistoricalPriceInterface
     * @throws LocalizedException
     */
    public function get(int $historyId): HistoricalPriceInterface;

    /**
     * Retrieve catalog_product_price_history matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return HistoricalPriceSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): HistoricalPriceSearchResultsInterface;

    /**
     * Delete catalog_product_price_history
     * @param HistoricalPriceInterface $historicalPrice
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(HistoricalPriceInterface $historicalPrice): bool;

    /**
     * Delete catalog_product_price_history by ID
     * @param int $historyId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById(int $historyId): bool;
}


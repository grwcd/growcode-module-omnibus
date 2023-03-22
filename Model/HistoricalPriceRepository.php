<?php

namespace Growcode\Omnibus\Model;

use Exception;
use Growcode\Omnibus\Api\Data\HistoricalPriceInterface;
use Growcode\Omnibus\Api\Data\HistoricalPriceInterfaceFactory;
use Growcode\Omnibus\Api\Data\HistoricalPriceSearchResultsInterface;
use Growcode\Omnibus\Api\HistoricalPriceRepositoryInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Growcode\Omnibus\Model\ResourceModel\HistoricalPrice;
use Growcode\Omnibus\Model\ResourceModel\HistoricalPrice\CollectionFactory;
use Growcode\Omnibus\Api\Data\HistoricalPriceSearchResultsInterfaceFactory;

class HistoricalPriceRepository implements HistoricalPriceRepositoryInterface
{

    /**
     * @var HistoricalPrice
     */
    protected HistoricalPrice $resource;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $historicalPriceCollectionFactory;

    /**
     * @var HistoricalPriceSearchResultsInterfaceFactory
     */
    protected HistoricalPriceSearchResultsInterfaceFactory $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected CollectionProcessorInterface $collectionProcessor;

    /**
     * @var HistoricalPriceInterfaceFactory
     */
    protected HistoricalPriceInterfaceFactory $historicalPriceFactory;


    /**
     * @param HistoricalPrice $resource
     * @param HistoricalPriceInterfaceFactory $historicalPriceFactory
     * @param CollectionFactory $historicalPriceCollectionFactory
     * @param HistoricalPriceSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        HistoricalPrice $resource,
        HistoricalPriceInterfaceFactory $historicalPriceFactory,
        CollectionFactory $historicalPriceCollectionFactory,
        HistoricalPriceSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->historicalPriceFactory = $historicalPriceFactory;
        $this->historicalPriceCollectionFactory = $historicalPriceCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(HistoricalPriceInterface $historicalPrice): HistoricalPriceInterface
    {
        try {
            $this->resource->save($historicalPrice);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save HistoricalPrice: %1',
                $exception->getMessage()
            ));
        }

        return $historicalPrice;
    }

    /**
     * @inheritDoc
     */
    public function get(int $historyId): HistoricalPriceInterface
    {
        $catalogProductIndexPriceHistory = $this->historicalPriceFactory->create();
        $this->resource->load($catalogProductIndexPriceHistory, $historyId);

        if (!$catalogProductIndexPriceHistory->getId()) {
            throw new NoSuchEntityException(__('HistoricalPrice with ID "%1" does not exist.', $historyId));
        }

        return $catalogProductIndexPriceHistory;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): HistoricalPriceSearchResultsInterface
    {
        $collection = $this->historicalPriceCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $items = [];

        foreach ($collection as $model) {
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(HistoricalPriceInterface $historicalPrice): bool
    {
        try {
            $historicalPriceModel = $this->historicalPriceFactory->create();
            $this->resource->load($historicalPriceModel, $historicalPrice->getHistoryId());
            $this->resource->delete($historicalPriceModel);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete HistoricalPrice: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $historyId): bool
    {
        return $this->delete($this->get($historyId));
    }
}


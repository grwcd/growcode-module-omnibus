<?php

namespace Growcode\Omnibus\Cron;

use Growcode\Omnibus\Api\Data\HistoricalPriceInterface;
use Growcode\Omnibus\Api\HistoricalPriceRepositoryInterface;
use Growcode\Omnibus\Model\ResourceModel\HistoricalPrice\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;

class ClearHistoricalPrices
{
    private CollectionFactory $collectionFactory;
    private HistoricalPriceRepositoryInterface $historicalPriceRepository;

    /**
     * @param CollectionFactory $collectionFactory
     * @param HistoricalPriceRepositoryInterface $historicalPriceRepository
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        HistoricalPriceRepositoryInterface $historicalPriceRepository
    )
    {
        $this->collectionFactory = $collectionFactory;
        $this->historicalPriceRepository = $historicalPriceRepository;
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function execute()
    {
        $collection = $this->collectionFactory->create();
        /** @var HistoricalPriceInterface[] $items */
        $items = $collection->addTimestampFilter()->getItems();
        
        foreach ($items as $item) {
            $this->historicalPriceRepository->delete($item);
        }
    }
}

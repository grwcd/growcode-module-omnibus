<?php

namespace Growcode\Omnibus\Model;

use Growcode\Omnibus\Api\Data\HistoricalPriceInterface;
use Magento\Framework\Model\AbstractModel;

class HistoricalPrice extends AbstractModel implements HistoricalPriceInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel\HistoricalPrice::class);
    }

    /**
     * @inheritDoc
     */
    public function getProductId(): int
    {
        return $this->_getData(self::PRODUCT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setProductId(int $productId): HistoricalPriceInterface
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @inheritDoc
     */
    public function getWebsiteId(): int
    {
        return $this->_getData(self::WEBSITE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setWebsiteId(int $websiteId): HistoricalPriceInterface
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * @inheritDoc
     */
    public function getPrice(): float
    {
        return $this->_getData(self::PRICE);
    }

    /**
     * @inheritDoc
     */
    public function setPrice(float $price): HistoricalPriceInterface
    {
        return $this->setData(self::PRICE, $price);
    }

    /**
     * @inheritDoc
     */
    public function getTimestamp(): string
    {
        return $this->_getData(self::TIMESTAMP);
    }

    /**
     * @inheritDoc
     */
    public function setTimestamp(string $timestamp): HistoricalPriceInterface
    {
        return $this->setData(self::TIMESTAMP, $timestamp);
    }

    /**
     * @inheritDoc
     */
    public function getHistoryId(): int
    {
        return $this->_getData(self::HISTORY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setHistoryId(int $id): HistoricalPriceInterface
    {
        return $this->setData(self::HISTORY_ID, $id);
    }
}

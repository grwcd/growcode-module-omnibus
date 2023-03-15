<?php

namespace Growcode\Omnibus\Api\Data;

interface HistoricalPriceInterface
{
    const HISTORY_ID = 'history_id';
    const PRODUCT_ID = 'product_id';
    const WEBSITE_ID = 'website_id';
    const PRICE = 'price';
    const TIMESTAMP = 'timestamp';

    /**
     * @return int
     */
    public function getHistoryId(): int;

    /**
     * @param int $id
     * @return $this
     */
    public function setHistoryId(int $id): self;

    /**
     * @return int
     */
    public function getProductId(): int;

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId(int $productId): self;

    /**
     * @return int
     */
    public function getWebsiteId(): int;

    /**
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId(int $websiteId): self;

    /**
     * @return float
     */
    public function getPrice(): float;

    /**
     * @param float $price
     * @return $this
     */
    public function setPrice(float $price): self;

    /**
     * @return string
     */
    public function getTimestamp(): string;

    /**
     * @param string $timestamp
     * @return $this
     */
    public function setTimestamp(string $timestamp): self;
}

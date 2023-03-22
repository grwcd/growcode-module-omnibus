<?php

namespace Growcode\Omnibus\Model\ResourceModel\HistoricalPrice;

use Growcode\Omnibus\Model\HistoricalPrice;
use Growcode\Omnibus\Model\ResourceModel\HistoricalPrice as HistoricalPriceResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'history_id';

    /**
     * Define module
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            HistoricalPrice::class,
            HistoricalPriceResourceModel::class
        );
        $this->setRowIdFieldName('history_id');
    }

    /**
     * Set row id field name
     *
     * @param string $fieldName
     * @return Collection
     */
    public function setRowIdFieldName(string $fieldName): Collection
    {
        return $this->_setIdFieldName($fieldName);
    }

    /**
     * Initialize select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->_joinFields();

        return $this;
    }

    /**
     * Join fields to entity
     *
     * @return $this
     */
    protected function _joinFields(): Collection
    {
        $this->addFieldToSelect([
            'product_id',
            'timestamp',
            'price',
            'website_id'
        ]);

        return $this;
    }

    /**
     * Add entity filter
     *
     * @param int $entityId Entity Id
     * @return $this
     */
    public function addEntityFilter(int $entityId): Collection
    {
        $this->getSelect()->where('main_table.product_id = ?', $entityId);

        return $this;
    }

    /**
     * Add date filter
     *
     * @return $this
     */
    public function addTimestampFilter(): Collection
    {
        $dateFrom = date('Y-m-d H:i:s', strtotime('-30 days'));
        $this->getSelect()->where('timestamp < (?)', $dateFrom);

        return $this;
    }
}

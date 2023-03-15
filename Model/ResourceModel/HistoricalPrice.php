<?php

namespace Growcode\Omnibus\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class HistoricalPrice extends AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('growcode_omnibus_catalog_product_price_history', 'history_id');
    }
}

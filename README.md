# Omnibus Module #

This module keeps track of product price changes, and displays the lowest historical price on the

- product page underneath the price box
- product listing (after manual installation)

**before** currently set promotion.

When detecting price changes, special prices and catalog price rules are taken into account.

`growcode_omnibus_clear_historical_prices` cron is executed once a day to purge historical prices that are older than
30 days.

## Installation

In order to display lowest historical prices in product listing, the following code needs to be added to `Magento_Catalog::product/list.phtml` (for Hyvä `Magento_Catalog::product/list/item.phtml`):

```
<?= $block->getChildBlock('historical_price')->setData('product', $product)->toHtml(); ?>
```

## Configuration

No configuration is needed.

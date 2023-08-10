# Omnibus Module

This module keeps track of product price changes, and displays the lowest historical price in price boxes that contain
special prices.

When detecting price changes, special prices and catalog price rules are taken into account.

`growcode_omnibus_clear_historical_prices` cron is executed once a day to purge historical prices that are older than
30 days.

## Configuration

No configuration is needed.

## Development

In order to display the lowest historical price message, we can use the following code in a `.phtml` template:

```
<?php
$historicalPrice = $block->getHistoricalPrice();
?>

...

<?= $historicalPrice->getHistoricalPriceItemHtml($product); ?>
```

whilst passing in `HistoricalPrice` view model in layout:

```
<referenceBlock name="...">
    <arguments>
        <argument name="historical_price" xsi:type="object">Growcode\Omnibus\ViewModel\Product\HistoricalPrice</argument>
    </arguments>
</referenceBlock>
```

### Hyvä Compatibility

For Hyvä, we can access the view model directly via `ViewModelRegistry`:

```
<?php
$historicalPriceViewModel = $viewModels->require(\Growcode\Omnibus\ViewModel\Product\HistoricalPrice::class);
?>

...

<?= $historicalPriceViewModel->getHistoricalPriceItemHtml($item); ?>
```

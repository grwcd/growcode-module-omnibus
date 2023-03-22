# Omnibus Module #

This module keeps track of product price changes, and displays the lowest historical price on the product page
underneath the price box.

When detecting price changes, special prices and catalog price rules are taken into account.

`growcode_omnibus_clear_historical_prices` cron is executed once a day to purge historical prices that are older than
30 days.

## Configuration

No configuration is needed.

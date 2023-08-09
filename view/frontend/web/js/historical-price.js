define([
    'jquery',
    'Magento_Catalog/js/price-utils'
], function ($, utils) {
    return function (config, element) {
        $(config.priceBox).on('priceUpdated', function (event, data) {
            if (data.finalPrice.amount === data.oldPrice.amount) {
                $(element).hide();

                return;
            }

            const selectedConfigurableOption = $('[name="selected_configurable_option"]').val();

            if (selectedConfigurableOption) {
                const lowestHistoricalPrice = config.lowestHistoricalPrices[selectedConfigurableOption];

                if (lowestHistoricalPrice) {
                    const price = utils.formatPrice(lowestHistoricalPrice, config.priceFormat);
                    $(element).find('.price').text(price);
                    $(element).show();
                } else {
                    $(element).hide();
                }
            }
        });
    }
})

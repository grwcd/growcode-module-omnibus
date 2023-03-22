define([
    'jquery',
    'Magento_Catalog/js/price-utils'
], function ($, utils) {
    return function (config, element) {
        $(config.priceBox).on('reloadPrice', function () {
            const selectedConfigurableOption = $('[name="selected_configurable_option"]').val();

            if (selectedConfigurableOption) {
                const price = utils.formatPrice(config.lowestHistoricalPrices[selectedConfigurableOption], config.priceFormat);
                $(element).find('.price').text(price);
            }
        });
    }
})

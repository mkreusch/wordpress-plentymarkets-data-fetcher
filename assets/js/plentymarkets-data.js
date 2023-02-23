jQuery(() => {
    PlentymarketsDataFetcher.init();
});

const PlentymarketsDataFetcher = {
    init: (selector) => {
        if (typeof selector === 'undefined') {
            selector = 'body';
        }
        const variationIds = [];
        jQuery(selector).find('[data-plenty-variation-id]').each((index, element) => {
            variationIds.push(jQuery(element).data('plentyVariationId'));
        });

        if (variationIds.length === 0) {
            return;
        }
        PlentymarketsDataFetcher.loadData(selector, variationIds);
    },

    loadData: (selector, variationIds) => {
        const domain = PmdfData.domain;
        const baseUrlProduct = `https://${domain}/`;
        const urlParameters = [];
        for (const variationId of variationIds) {
            urlParameters.push(variationId);
        }
        console.log(urlParameters);
        jQuery.get(PmdfData.restUrl + (PmdfData.restUrl.indexOf('?') === -1 ? '?' : '&') + 'variationIds=' +urlParameters.join(','), (data) => {
            let resultSet = data.data || {};
            try {
                resultSet = JSON.parse(data.contents).data;
            } catch (error) {
            }
            if (!resultSet.documents) {
                return;
            }

            for (let productData of resultSet.documents) {
                const variationId = productData.id;
                const $containers = jQuery(selector).find('[data-plenty-variation-id="' + variationId + '"]');
                $containers.each((index, container) => {
                    const $container = jQuery(container);
                    $container.find('[data-plenty-matching]').each((index, dataElement) => {
                        const $dataElement = jQuery(dataElement);
                        const dataElementConfiguration = $dataElement.data('plentyMatching');
                        for (const matchingConfiguration of dataElementConfiguration) {
                            let value = PlentymarketsDataFetcher.getPropertyFromPath(productData.data, matchingConfiguration.field) || '';
                            if (matchingConfiguration.field === 'url') {
                                value = baseUrlProduct + productData.data.item.id + '_' + variationId;
                            }
                            if (matchingConfiguration.target === 'html') {
                                $dataElement.html(value);
                            } else {
                                $dataElement.attr(matchingConfiguration.target, value);
                            }
                        }
                    });
                });
            }
        });
    },

    getPropertyFromPath: (obj, desc) => {
        const arr = desc.split(".");
        console.log(obj, arr);
        while (arr.length && (obj = obj[arr.shift()])) ;
        return obj;
    },
}
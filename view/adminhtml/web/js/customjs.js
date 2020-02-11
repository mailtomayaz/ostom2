/**
* Copyright © embraceit, Inc. All rights reserved.
* See COPYING.txt for license details.
* custom js functions and calls
*/
define([
    "jquery",
    "Magento_Ui/js/modal/modal",
    'accordion'
], function ($, modal) {
    "use strict";
    function main(config, element) {
        var $element = $(element);
        var databaseTestUrl = config.databaseTestUrl;
        var formKey = config.formKey;
        var chunkSize = config.chunkSize;
        var totalLimit = config.totalLimit;
        var catCount = config.catCount;
        var productCount = config.productCount;
        var customOptionCount = config.customOptionCount;
        var addCategoriesUrl = config.addCategoriesUrl;
        var ajaxBaseUrl = config.ajaxBaseUrl;
        var addProductUrl = config.addProductUrl;
        var addProductCustomOptionUrl = config.addProductCustomOptionUrl;
        var cleanDataUrl = config.cleanDataUrl;
        var message = '';
        var totalChunks;
        var numberOfChunks;
        var chunck;
        var realCounter;
        var i;
        $(document).ready(function () {
            var entityData = [];
            entityData['url'] = [];
            entityData['recordLimit'] = [];
            entityData['startAt'] = [];
            entityData['mainDiv'] = '';
            entityData['progressDiv'] = '';
            entityData['entityType'] = '';
            entityData['progresCounterController'] = '';
            entityData['progressBarDiv'] = '';
            entityData['barMessageDiv'] = '';
            entityData['statusMessage'] = '';
            //check database connection
            $('.checkConnection').click(function () {
                var customurl = databaseTestUrl;
                $.ajax({
                    url: customurl,
                    type: 'POST',
                    dataType: 'json',
                    showLoader: true,
                    data: {
                        form_key: formKey,
                    },
                    complete: function (response) {

                        message = response.responseJSON.messageConnection;
                        $('.dataimportdiv').css('display', 'block');
                        alert(message);
                    },
                    error: function (xhr, status, errorThrown) {
                        console.log('Error happens. Try again.');
                    }
                });
            });

            //import categories
            $('.importCategories').click(function () {

                var customurl = addCategoriesUrl;
                totalChunks = Number(catCount) / totalLimit;
                numberOfChunks = Math.round(totalChunks);
                chunck = Number(numberOfChunks);
                entityData['totalChunks'] = numberOfChunks;
                entityData['chunkSize'] = totalLimit;
                entityData['mainDiv'] = 'importCategories';
                entityData['progressDiv'] = 'progressdiv';
                entityData['entityType'] = 'category';
                entityData['progresCounterController'] = 'Getdata';
                entityData['progressBarDiv'] = 'progressbar';
                entityData['barMessageDiv'] = 'uk-progress-bar';
                entityData['statusMessage'] = 'status-progress-bar';
                entityData['totalCount'] = catCount;

                for (i = 0; i < numberOfChunks; i++) {
                    entityData['url'][i] = customurl;
                    entityData['recordLimit'][i] = Number(i * entityData['chunkSize'] );
                    entityData['startAt'][i] = totalLimit;
                }
                //recordLimit
                var index = 0;
                getAjaxEntityInformation(index, entityData);

            });

            //get information of entity
            function getAjaxEntityInformation(index, entityData) {
                if (Number(index + 1) == Number(entityData['totalChunks'])) {
                    //remaning items
                    chunkSize = Number(entityData['totalCount']) % Number(entityData['chunkSize']);
                }
                realCounter = progressCounterEntity(chunkSize, entityData['totalChunks'], index);
                if (typeof realCounter === 'undefined') {
                    realCounter = 0;
                }
                $.ajax({
                    url: entityData['url'][index],
                    data: {
                        form_key: formKey,
                        start_limit: entityData['recordLimit'][index],
                        total_limit: entityData['startAt'][index]
                    },
                    beforeSend: function () {
                        $('.' + entityData['mainDiv']).prop('disabled', true);
                        $('.' + entityData['progressDiv']).css('display', 'block');

                    },
                    success: function (response) {
                        if (response.importStatus != true) {
                            $('.' + entityData['progressDiv'][index]).css('display', 'block');
                            message = response.importStatus;
                            $(".categorystatus").html(message);
                            $("#openModel").trigger('click');
                            $('.' + entityData['mainDiv']).prop('disabled', false);
                            return;
                        }
                        index++;
                        if (entityData['url'][index] != undefined) {

                            getAjaxEntityInformation(index, entityData);

                        }
                        if (Number(index + 1) == Number(entityData['totalChunks'][index])) {
                            if (Number(realCounter) == chunkSize) {
                                message = 'Data has been added';
                                $(".categorystatus").html(message);
                                $("#openModel").trigger('click');
                                $('.' + entityData['mainDiv']).prop('disabled', false);
                            }
                        }

                    },
                    error: function (xhr, status, errorThrown) {
                        console.log('Error happens. Try again.');
                    }
                });
            }

            //progress counter
            function progressCounterEntity(chunkSize, totalChunks, index) {
                var barValue;
                var animate = setInterval(function () {
                    var customurl = ajaxBaseUrl + entityData['progresCounterController'];
                    var bar = document.getElementById(entityData['progressBarDiv']);
                    bar.max = chunkSize;
                    bar.value = 1; // completed Steps
                    $.ajax({
                        url: customurl,
                        type: 'POST',
                        dataType: 'json',
                        asyn: false,
                        data: {
                            form_key: formKey,
                        },
                        success: function (response) {
                            message = response.counter;
                            console.log(response.counter);
                            if (typeof response.counter == 'undefined') {
                                message = 0;
                            }
                            barValue = message;
                            var barmessage = $('.' + entityData['barMessageDiv']);
                            var statusmessage = $('.' + entityData['statusMessage']);
                            var percent = Math.floor((barValue / chunkSize) *
                                100);
                            statusmessage.text(barValue + ' out of ' + chunkSize +
                                ' (' + percent + ' %) Completed! Total Chunks ' +
                                Math.round(totalChunks) + ' Processing Chunk ' +
                                Number(index + 1));
                            barmessage.css({
                                'width': percent + '%',
                                'background': 'green'
                            });

                            if (barValue >= bar.max) {
                                clearInterval(animate);
                            }
                            //return barValue;
                            if (Number(index + 1) == Number(totalChunks) && Number(chunkSize) == Number(message)) {
                                message = entityData['entityType'] + " has been added";
                                $('.dataimportdiv').css('display', 'block');
                                $(".categorystatus").html(message);
                                $("#openModel").trigger('click');
                                $('.' + entityData['mainDiv']).prop('disabled', false);
                            }

                        },
                        error: function (xhr, status, errorThrown) {
                            console.log('Error happens. Try again.');
                        }
                    });
                    return barValue;
                }, 5000);
                //return barValue;
            }
            //import products
            $('.importProducts').click(function () {
                var customurl = addProductUrl;
                totalChunks = Number(productCount) / totalLimit;
                numberOfChunks = Math.round(totalChunks);
                chunck = Number(numberOfChunks);
                entityData['totalChunks'] = numberOfChunks;
                entityData['chunkSize'] = totalLimit;
                entityData['mainDiv'] = 'importProducts';
                entityData['progressDiv'] = 'progressdivproduct';
                entityData['entityType'] = 'product';
                entityData['progresCounterController'] = 'Getproductdata';
                entityData['progressBarDiv'] = 'progressbarproduct';
                entityData['barMessageDiv'] = 'product-progress-bar';
                entityData['statusMessage'] = 'product-status-progress-bar';
                entityData['totalCount'] = productCount;

                for (i = 0; i < numberOfChunks; i++) {
                    entityData['url'][i] = customurl;
                    entityData['recordLimit'][i] = Number(i * entityData['chunkSize'] );
                    entityData['startAt'][i] = totalLimit;
                }
                //recordLimit
                var index = 0;
                getAjaxEntityInformation(index, entityData)
            });
            //import custom options
            $('.importProductsCustomizeOptions').click(function () {
                var customurl = addProductCustomOptionUrl;
                totalChunks = Number(customOptionCount) / totalLimit;
                numberOfChunks = Math.round(totalChunks);
                chunck = Number(numberOfChunks);
                entityData['totalChunks'] = numberOfChunks;
                entityData['chunkSize'] = totalLimit;
                entityData['mainDiv'] = 'importProductsCustomizeOptions';
                entityData['progressDiv'] = 'progressdivoptions';
                entityData['entityType'] = 'Options';
                entityData['progresCounterController'] = 'Getdataoptions';
                entityData['progressBarDiv'] = 'progressbaroption';
                entityData['barMessageDiv'] = 'option-progress-bar';
                entityData['statusMessage'] = 'option-status-progress-bar';
                entityData['totalCount'] = customOptionCount;

                for (i = 0; i < numberOfChunks; i++) {
                    entityData['url'][i] = customurl;
                    entityData['recordLimit'][i] = Number(i * entityData['chunkSize']);
                    entityData['startAt'][i] = totalLimit;
                }
                //recordLimit
                var index = 0;
                getAjaxEntityInformation(index, entityData)
            });
            //clean data category

            $('.clean-category-data').click(function () {
                var url = cleanDataUrl;
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    showLoader: true,
                    data: {
                        form_key: formKey,
                        clean_type: 'category'
                    },
                    beforeSend: function () {
                    },
                    complete: function (response) {
                        message = response.responseJSON.message;
                        alert(message);

                    },
                    error: function (xhr, status, errorThrown) { },
                    success: function () { }
                });
            });
            //clean product data
            $('.clean-products-data').click(function () {
                var url = cleanDataUrl;
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    showLoader: true,
                    data: {
                        form_key: formKey,
                        clean_type: 'product'
                    },
                    beforeSend: function () {
                    },
                    complete: function (response) {
                        message = response.responseJSON.message;
                        alert(message);

                    },
                    error: function (xhr, status, errorThrown) { },
                    success: function () { }
                });

            });

            //accrodin initiaization
            $("#element").accordion();
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: 'Import Status',
                buttons: [{
                    text: $.mage.__('OK'),
                    class: '',
                    click: function () {
                        this.closeModal();
                    }
                }]
            };
            var popup = modal(options, $('#myModel'));
            $("#openModel").on("click", function () {
                $('#myModel').modal('openModal');
            });

        });


    };
    return main;
});
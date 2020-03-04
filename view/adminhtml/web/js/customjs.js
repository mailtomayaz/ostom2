/**
* Copyright © embraceit, Inc. All rights reserved.
* This file contians functions that use to send and recive data from magento
* custom js functions and calls
*/
define([
    "jquery",
    "Magento_Ui/js/modal/modal",
    'accordion'
], function ($, modal) {
    "use strict";
    function main(config)
    {
        //var $element = $(element);
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
        var message = '';
        var totalChunks;
        var numberOfChunks;
        var chunck;
        var realCounter;
        var i;
        var arrButtons = ['import-categories', 'clean-category-data', 'clean-products-data', 'import-products', 'import-products-options','checkConnection'];
        $(document).ready(function () {
            /**
             * check database connection
             * @param {string} customurl - url of the contorller to check database connection.
             * @return {string} message - databse connected or not.
             */
            $(document).on("click", ".checkConnection", function () {
            //$('.checkConnection').click(function () {
                $(this).closest(".collapsible-content-tab").addClass("heightlight-tab");
                //$(".collapsible-tab-accordian.allow").addClass("heightlight-tab");
                //collapsible-tab-accordian allow
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
                        $(".import-status").html(message);
                        $("#button-modal").trigger('click');
                        $(".collapsible-content-tab").removeClass("heightlight-tab");
                        //$(".collapsible-tab-accordian").removeClass("heightlight-tab");
                    },
                    error: function (xhr, status, errorThrown) {
                        console.log('Error happens. Try again.');
                        $(".collapsible-content-tab").removeClass("heightlight-tab");
                        //$(".collapsible-tab-accordian").removeClass("heightlight-tab");
                    }
                });
            });

            /**
             * import categories
             * @param {string} customurl - url of the contorller to check send ajax request
             * @param {Number} catCount - total number of categoires in oscommerce database
             * @param {Number} totalLimit - chunksize configured in backend.
             */
            $(document).on("click", ".import-categories", function () {
                $(this).closest(".collapsible-content-tab").addClass("heightlight-tab");
                var customurl = addCategoriesUrl;
                totalChunks = Number(catCount) / totalLimit;
                numberOfChunks = Math.round(totalChunks);
                chunck = Number(numberOfChunks);
                entityData['totalChunks'] = numberOfChunks;
                entityData['chunkSize'] = totalLimit;
                entityData['mainDiv'] = 'import-categories';
                entityData['progressDiv'] = 'progress-category';
                entityData['entityType'] = 'Categories';
                entityData['progresCounterController'] = 'Getdata';
                entityData['progressBarDiv'] = 'progress-bar-category';
                entityData['barMessageDiv'] = 'category-progress-bar';
                entityData['statusMessage'] = 'status-progress-bar';
                entityData['totalCount'] = catCount;
                //setup chunks
                for (i = 0; i < numberOfChunks; i++) {
                    entityData['url'][i] = customurl;
                    entityData['recordLimit'][i] = Number(i * entityData['chunkSize']);
                    entityData['startAt'][i] = totalLimit;
                }
                //start from chunk 0
                var index = 0;
                //process category import
                $('#chunk-size').html(entityData['chunkSize']);
                getAjaxEntityInformation(index, entityData);

            });

            /**
             * process import
             * @param {Number} index - chunk number
             * @param {array} entityData - contians neccessory data for import.
             */
            function getAjaxEntityInformation(index, entityData)
            {
                chunkSize = Number(entityData['chunkSize']);
                if (Number(index + 1) == Number(entityData['totalChunks'])) {
                    //remaning items
                    chunkSize = Number(entityData['totalCount']) % Number(entityData['chunkSize']);
                }
                //import progress status

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
                        //
                        $('#chunk-number').html(index);
                        $('#chunk-size').html(chunkSize);
                        //disable all other buttons
                        disableButtons();
                        realCounter = progressCounterEntity(chunkSize, entityData['totalChunks'], index);
                    },
                    success: function (response) {
                        if (response.importStatus != true) {
                            enableButtons();
                            $('.' + entityData['progressDiv'][index]).css('display', 'block');
                            message = response.importStatus;
                            $(".import-status").html(message);
                            $("#button-modal").trigger('click');
                            $('.' + entityData['mainDiv']).prop('disabled', false);
                            return;
                        }
                        index++;
                        if (entityData['url'][index] != undefined) {
                            //again process for next chunk of data
                            getAjaxEntityInformation(index, entityData);
                        } else {
                            enableButtons();
                            $(".collapsible-content-tab").removeClass("heightlight-tab");
                        }
                    },
                    error: function (xhr, status, errorThrown) {
                        console.log('Error happens. Try again.');
                        $(".collapsible-content-tab").removeClass("heightlight-tab");
                        xhr.abort();
                    }
                });
            }

            /**
             * progress counter
             * @param {Number} chunkSize - number or recoreds in each chunck
             * @param {Number} totalChunks - total chunks of data.
             * @param {Number} index - current chunk number
             */
            function progressCounterEntity(chunkSize, totalChunks, index)
            {
                var barValue;
                // var animate = setInterval(function () {
                var customurl = ajaxBaseUrl + entityData['progresCounterController'];
                var bar = document.getElementById(entityData['progressBarDiv']);
                bar.max = chunkSize;
                bar.value = 1; // completed Steps
                var isMessageDisplayed = false;
                $.ajax({
                    url: customurl,
                    type: 'POST',
                    dataType: 'json',
                    asyn: false,
                    data: {
                        form_key: formKey,
                    },
                    success: function (response, textStatus, jqXHR) {
                        message = response.counter;
                        //console.log(response.counter);
                        if (typeof response.counter == 'undefined') {
                            message = 0;
                        }
                        barValue = message;
                        var barmessage = $('.' + entityData['barMessageDiv']);
                        var statusmessage = $('.' + entityData['statusMessage']);
                        var percent = Math.floor((barValue / chunkSize) *
                            100);
                        $('#progress-counter').html(barValue);
                        statusmessage.text(barValue + ' out of ' + chunkSize +
                            ' (' + percent + ' %) Completed! Total Chunks ' +
                            Math.round(totalChunks) + ' Processing Chunk ' +
                            Number(index + 1));
                        barmessage.css({
                            'width': percent + '%',
                            'background': 'green'
                        });

                        //return barValue;
                        if (Number(index + 1) == Number(totalChunks) && Number(chunkSize) == Number(message)) {
                            setTimeout(function () {
                                message = entityData['entityType'] + " has been added";
                                $('.dataimportdiv').css('display', 'block');
                                $(".import-status").html(message);
                                $("#button-modal").trigger('click');
                                $('.' + entityData['mainDiv']).prop('disabled', false);
                            }, 5000);
                            jqXHR.abort();
                        } else {
                            index = $('#chunk-number').text();
                            chunkSize = $('#chunk-size').text();
                            progressCounterEntity(chunkSize, totalChunks, Number(index));
                        }

                        //return barValue;
                    },
                    error: function (xhr, status, errorThrown) {
                        console.log('Error happens. Try again.');
                        progressCounterEntity(chunkSize, totalChunks, index);
                    }
                });

            }
            /**
             * import products
             * @param {string} customurl - url of the contorller to check send ajax request
             * @param {Number} productCount - total number of products in oscommerce database
             * @param {Number} totalLimit - chunksize configured in backend.
             */
            $(document).on("click", ".import-products", function () {
                $(this).closest(".collapsible-content-tab").addClass("heightlight-tab");
                var customurl = addProductUrl;
                totalChunks = Number(productCount) / totalLimit;
                numberOfChunks = Math.round(totalChunks);
                chunck = Number(numberOfChunks);
                entityData['totalChunks'] = numberOfChunks;
                entityData['chunkSize'] = totalLimit;
                entityData['mainDiv'] = 'import-products';
                entityData['progressDiv'] = 'progress-product';
                entityData['entityType'] = 'Products';
                entityData['progresCounterController'] = 'Getproductdata';
                entityData['progressBarDiv'] = 'progress-bar-product';
                entityData['barMessageDiv'] = 'product-progress-bar';
                entityData['statusMessage'] = 'product-status-progress-bar';
                entityData['totalCount'] = productCount;

                //genrate chunks to process one by one
                for (i = 0; i < numberOfChunks; i++) {
                    entityData['url'][i] = customurl;
                    entityData['recordLimit'][i] = Number(i * entityData['chunkSize']);
                    entityData['startAt'][i] = totalLimit;
                }
                //start from chunk 0
                var index = 0;
                //process import
                $('#chunk-size').html(entityData['chunkSize']);
                getAjaxEntityInformation(index, entityData)
            });
            /**
            * import custom options
            * @param {string} customurl - url of the contorller to check send ajax request
            * @param {Number} customOptionCount - total number of custom option in oscommerce database
            * @param {Number} totalLimit - chunksize configured in backend.
            */
            $(document).on("click", ".import-products-options", function () {
                $(this).closest(".collapsible-content-tab").addClass("heightlight-tab");
                var customurl = addProductCustomOptionUrl;
                totalChunks = Number(customOptionCount) / totalLimit;
                numberOfChunks = Math.round(totalChunks);
                chunck = Number(numberOfChunks);
                entityData['totalChunks'] = numberOfChunks;
                entityData['chunkSize'] = totalLimit;
                entityData['mainDiv'] = 'import-products-options';
                entityData['progressDiv'] = 'progress-options';
                entityData['entityType'] = 'Options';
                entityData['progresCounterController'] = 'Getdataoptions';
                entityData['progressBarDiv'] = 'progress-bar-options';
                entityData['barMessageDiv'] = 'option-progress-bar';
                entityData['statusMessage'] = 'option-status-progress-bar';
                entityData['totalCount'] = customOptionCount;
                //setup chunks of data
                for (i = 0; i < numberOfChunks; i++) {
                    entityData['url'][i] = customurl;
                    entityData['recordLimit'][i] = Number(i * entityData['chunkSize']);
                    entityData['startAt'][i] = totalLimit;
                }
                //start from chunk 0
                var index = 0;
                //process import
                $('#chunk-size').html(entityData['chunkSize']);
                getAjaxEntityInformation(index, entityData)
            });

            /**
            * clean data category
            * @param {string} cleanDataUrl - url of the contorller to check send ajax request
            * @param {string} clean_type - type of data to clean
            * @param {string} form_key - magento form key for security
            */

            $('.clean-category-data').click(function () {
                var url = cleanDataUrl;
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    showLoader: true,
                    data: {
                        form_key: formKey,
                        clean_type: 'Categories'
                    },
                    beforeSend: function () {
                    },
                    complete: function (response) {
                        message = response.responseJSON.message;
                        $('.dataimportdiv').css('display', 'block');
                        $(".import-status").html(message);
                        $("#button-modal").trigger('click');

                    },
                    error: function (xhr, status, errorThrown) { },
                    success: function () { }
                });
            });

            /**
            * clean product data
            * @param {string} cleanDataUrl - url of the contorller to check send ajax request
            * @param {string} clean_type - type of data to clean
            * @param {string} form_key - magento form key for security
            */

            $('.clean-products-data').click(function () {
                var url = cleanDataUrl;
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    showLoader: true,
                    data: {
                        form_key: formKey,
                        clean_type: 'Products'
                    },
                    beforeSend: function () {
                    },
                    complete: function (response) {
                        message = response.responseJSON.message;
                        $('.dataimportdiv').css('display', 'block');
                        $(".import-status").html(message);
                        $("#button-modal").trigger('click');

                    },
                    error: function (xhr, status, errorThrown) { },
                    success: function () { }
                });

            });

            //accrodin initiaization
            $("#element").accordion();
            //configure accordion
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: 'Status',
                buttons: [{
                    text: $.mage.__('OK'),
                    class: '',
                    click: function () {
                        this.closeModal();
                    }
                }]
            };
            //model settings
            var popup = modal(options, $('#model-window'));
            $("#button-modal").on("click", function () {
                $('#model-window').modal('openModal');
            });
            //show message on refresh page
            window.onbeforeunload = function () {
                return "Data will be lost if you leave the page, are you sure?";
            };
            //disable other buttons in case already one process is running

            function disableButtons()
            {
                
                $.each(arrButtons, function (index, value) {
                    $('.' + value).prop('disabled', true);
                })

            }
            //enable buttons in case request is successfull
            function enableButtons()
            {
                
                $.each(arrButtons, function (index, value) {
                    $('.' + value).prop('disabled', false);
                })
            }
        });


    };
    return main;
});
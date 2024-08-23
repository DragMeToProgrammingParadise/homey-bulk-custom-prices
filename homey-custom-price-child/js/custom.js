jQuery(document).ready(function ($) {
    "use strict";

    if (typeof Homey_Listing !== "undefined") {

        var ajaxurl = Homey_Listing.ajaxURL;
        var process_loader_spinner = Homey_Listing.process_loader_spinner;
        var pricing_link = Homey_Listing.pricing_link;

        /* ------------------------------------------------------------------------ */
        /*  Custom Period Prices Bulk
        /* ------------------------------------------------------------------------ */

        $('#cus_btn_save_bulk').on('click', function (e) {
            e.preventDefault();
            var $this = $(this);

            var fileInput = document.getElementById('custom_period_bulk_prices');
            var file = fileInput.files[0];

            console.log(file)

            if (file) {
                readFileAsync(file)
                    .then(csvData => {
                        var processedData = processData(csvData);
                        return processedData;
                    })
                    .then(processedData => {
                        var listing_id = $('#listing_id_for_custom').val();

                        $.ajax({
                            type: 'post',
                            url: ajaxurl,
                            dataType: 'json',
                            data: {
                                'action': 'homey_add_custom_period_bulk',
                                'listing_id': listing_id,
                                'custom_prices': processedData //JSON.stringify(processedData)
                            },
                            beforeSend: function () {
                                $this.children('i').remove();
                                $this.prepend('<i class="fa-left ' + process_loader_spinner + '"></i>');
                            },
                            success: function (data) { //alert(data.success); return false;
                                if (data.success) {
                                    window.location.href = pricing_link;
                                    // alert('Testing')
                                } else {
                                    alert(data.message);
                                }
                            },
                            error: function (xhr, status, error) {
                                var err = eval("(" + xhr.responseText + ")");
                                console.log(err.Message);
                            },
                            complete: function () {
                                $this.children('i').removeClass(process_loader_spinner);
                            }

                        });

                    })
                    .catch(error => {
                        console.error(error);
                    });
            } else {
                alert('Please select a CSV file.');
            }

        });

        function readFileAsync(file) {
            return new Promise((resolve, reject) => {
                var reader = new FileReader();
                reader.onload = function (e) {
                    resolve(e.target.result);
                };
                reader.onerror = function (error) {
                    reject(error);
                };
                reader.readAsText(file);
            });
        }

        function processData(csvData) {
            var lines = csvData.split('\n');
            var processedData = [];

            for (var i = 1; i < lines.length; i++) {
                var line = lines[i].trim();
                if (line) {
                    var data = line.split(',');
                    if (data.length === 3) {
                        var item = {
                            start:  data[0].trim(),
                            end:    data[1].trim(),
                            price:  data[2].trim()
                        };
                        processedData.push(item);
                    } else {
                        console.log('Invalid data format at line ' + (i + 1) + '. Skipping...');
                    }
                }
            }

            // console.log(processedData);
            return processedData;
        }

        /* ------------------------------------------------------------------------ */
        /*  End Custom Period Prices Bulk
        /* ------------------------------------------------------------------------ */
    }
});
<style>
    .table-header th {
        font-size: 80%;
    }

    .table-body td {
        font-size: 80%;
    }

    #filter,
    #reset {
        width: 82px;
    }

    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type="number"] {
        -moz-appearance: textfield;
    }
</style>
<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-body">
                    <div class="row" id="form1">
                        <div class="col-md-6">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-info text-white" id="basic-addon1"><i class="fas fa-calendar-alt"></i></span>
                                </div>
                                <input type="text" name="from_date" class="form-control" id="from_date" placeholder="Start Date" readonly required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-info text-white" id="basic-addon1"><i class="fas fa-calendar-alt"></i></span>
                                </div>
                                <input type="text" name="end_date" class="form-control" id="end_date" placeholder="End Date" readonly required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="input-group mb-3">
                                <input type="number" class="form-control" id="sales_code_input" placeholder="Sales Code" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" id="getData" name="getData" class="btn btn-primary">Get Data</button>
                            <button type="button" id="reset" name="reset" class="btn btn-danger">Reset</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sales_code">Sales Code</label>
                                <input type="text" name="sales_code" class="form-control" id="sales_code" placeholder="Sales Code" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sales_name">Sales Name</label>
                                <input type="text" name="sales_code" class="form-control" id="sales_name" placeholder="Sales Name" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="total_incentive_big">Total Incentive Big</label>
                                <input type="text" name="total_incentive_big" class="form-control" id="incentive_big" placeholder="Total Incentive Big" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="total_incentive_percentage">Total Incentive Percentage</label>
                                <input type="text" class="form-control" id="incentive_percentage" placeholder="Incentive Percentage" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="total_incentive_premium">Total Incentive Premium</label>
                                <input type="text" class="form-control" id="incentive_premium" placeholder="Incentive Premium" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="grand_total">Grand Total</label>
                                <input type="text" class="form-control" id="grand_total" placeholder="Grand Total" disabled>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col-md-4">
            <div class="input-group mb-3">
                <input type="text" class="form-control" id="searchInput" placeholder="Search">
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <button id="exportToExcel" class="btn btn-success">Excel</button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-body">
                    <table id="example1" class="table table-responsive">
                        <thead class="table-header">
                            <tr>
                                <th>No</th>
                                <th>Billing_No</th>
                                <th>Billing_Item</th>
                                <th>Billing_Type</th>
                                <th>Char_Size</th>
                                <th>Brand</th>
                                <th>Article</th>
                                <th>Article_Desc</th>
                                <th>Soffice</th>
                                <th>Soffice_Desc</th>
                                <th>Pos_Inv</th>
                                <th>Billing_Date</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Sales_Cd</th>
                                <th>Sales_Name</th>
                                <th>Big</th>
                                <th>Percentage</th>
                                <th>Premium</th>
                            </tr>
                        </thead>
                        <tbody class="table-body" id="table_body">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        $("#example1").DataTable({
            "searching": false,
            "paging": false,
        });
    });
</script>

<script>
    $(function() {
        $("#from_date").datepicker({
            dateFormat: 'yymmdd',
            onSelect: function(selectedDate) {
                $("#end_date").datepicker("option", "minDate", selectedDate);
            }

        });
        $("#end_date").datepicker({
            dateFormat: 'yymmdd',
            onSelect: function(selectedDate) {
                $("#start_date").datepicker("option", "maxDate", selectedDate);
            }
        });

        $("#getData").on("click", function() {
            var fromDate = $("#from_date").val();
            var endDate = $("#end_date").val();
            var salesCode = $("#sales_code_input").val();

            // * From Date, End Date, Sales Code kosong
            if (fromDate === "" || endDate === "" || salesCode === "") {
                alert("From Date, End Date, dan Sales Code belum terisi");
                return;
            }

            $.ajax({
                url: "http://localhost:8056/sales-incentive/ControllerSalesIncentive/Summary",
                type: "POST",
                dataType: "json",
                data: {
                    salesCode: salesCode,
                    fromDate: fromDate,
                    endDate: endDate,
                },
                success: function(response) {
                    console.log("Response Backup 3");
                    console.log(response);
                    // if (response && response.si && response.si.length > 0) {
                    //     let table = response.si;
                    //     let tbodyContent = '';

                    //     $.each(table, function(i, data) {
                    //         // * Convert Date
                    //         var year = data.BILLING_DATE.substring(0, 4);
                    //         var month = data.BILLING_DATE.substring(4, 6);
                    //         var day = data.BILLING_DATE.substring(6, 8);
                    //         var dateFormat = year + '-' + month + '-' + day;

                    //         // * Convert Number Rupiah
                    //         let inc_big = 0;
                    //         let inc_perc = 0;
                    //         let inc_premium = 0;
                    //         let price = 0
                    //         inc_big = parseInt(data.INC_BIG);
                    //         inc_perc = parseInt(data.INC_PERC);
                    //         inc_premium = parseInt(data.INC_PREMIUM);
                    //         price = parseInt(data.PRICE);

                    //         Inc_Big = inc_big.toLocaleString('id-ID');
                    //         Inc_Perc = inc_perc.toLocaleString('id-ID');
                    //         Inc_Premium = inc_premium.toLocaleString('id-ID');
                    //         Price = price.toLocaleString('id-ID');
                    //         tbodyContent += `
                    //             <tr>
                    //                 <td>${i + 1}</td>
                    //                 <td>${data.BILLING_NO}</td>
                    //                 <td>${data.BILLING_ITEM}</td>
                    //                 <td>${data.BILLING_TYP}</td>
                    //                 <td>${data.CHAR_SIZE}</td>
                    //                 <td>${data.BRAND}</td>
                    //                 <td>${data.ARTICLE}</td>
                    //                 <td>${data.ARTICLE_DESC}</td>
                    //                 <td>${data.SOFFICE}</td>
                    //                 <td>${data.SOFFICE_DESC}</td>
                    //                 <td>${data.POS_INV}</td>
                    //                 <td>${dateFormat}</td>
                    //                 <td>${data.QTY}</td>
                    //                 <td>${Price}</td>
                    //                 <td>${data.SLS_CD}</td>
                    //                 <td>${data.SLS_NAME}</td>
                    //                 <td>${Inc_Big}</td>
                    //                 <td>${Inc_Perc}</td>
                    //                 <td>${Inc_Premium}</td>
                    //             </tr>
                    //         `;
                    //     });

                    //     $("#table_body").html(tbodyContent);
                    // } else {
                    //     alert("Sales code not found");
                    // }
                    // console.log(response.si);

                    // let totalIncBig = 0;
                    // let totalIncPerc = 0;
                    // let totalIncPremium = 0;
                    // for (let i = 0; i < response.si.length; i++) {
                    //     totalIncBig += parseFloat(response.si[i]['INC_BIG']);
                    //     totalIncPerc += parseFloat(response.si[i]['INC_PERC']);
                    //     totalIncPremium += parseFloat(response.si[i]['INC_PREMIUM']);
                    // };

                    // $("#sales_code").val(response.si[0]['SLS_CD']);
                    // $("#sales_name").val(response.si[0]['SLS_NAME']);
                    // $("#incentive_big").val("Rp " + totalIncBig.toLocaleString('id-ID'));
                    // $("#incentive_percentage").val("Rp " + totalIncPerc.toLocaleString('id-ID'));
                    // $("#incentive_premium").val("Rp " + totalIncPremium.toLocaleString('id-ID'));
                    // let grandTotal = totalIncBig + totalIncPerc + totalIncPremium;
                    // $("#grand_total").val("Rp " + grandTotal.toLocaleString('id-ID'));
                },
                error: function(xhr, status, error) {
                    alert("Error fetching data." + xhr.responseText);
                }
            });
        });

        $("#exportToExcel").on("click", function() {
            $("#example1").table2excel({
                exclude: ".noExl",
                name: "SalesIncentive",
                filename: "sales_incentive",
                fileext: ".xls",
                exclude_img: true,
                exclude_links: true,
                exclude_inputs: true
            });
        });

        $("#searchInput").on("input", function() {
            var searchTerm = $(this).val().toLowerCase();
            $("#table_body tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(searchTerm) > -1);
            });
        });

        $("#reset").on("click", function() {
            $("#from_date").val("");
            $("#end_date").val("");
            $("#sales_code_input").val("");
        });
    });
</script>
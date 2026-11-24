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
                                <input type="text" class="form-control" id="sales_code_input" placeholder="Sales Code" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" id="getData" name="getData" class="btn btn-primary">Get Data</button>
                            <button type="reset" id="reset" name="reset" class="btn btn-danger">Reset</button>
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
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-body">
                    <table id="table_sales" class="table table-responsive">
                        <thead class="table-header">
                            <tr>
                                <th>No</th>
                                <th>Billing No</th>
                                <th>Billing Item</th>
                                <th>Billing Type</th>
                                <th>Char Size</th>
                                <th>Brand</th>
                                <th>Article</th>
                                <th>Article Desc</th>
                                <th>Soffice</th>
                                <th>Soffice Desc</th>
                                <th>Pos Inv</th>
                                <th>Billing Date</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Sales Cd</th>
                                <th>Sales Name</th>
                                <th>Inc Big</th>
                                <th>Inc Perc</th>
                                <th>Inc Premium</th>
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
        $('#table_sales').DataTable({
            "searching": true,
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

            

                // url: "https://electronic-city.biz/sales-incentive/ControllerSalesIncentive/summary",

            console.log(fromDate);
            console.log(endDate);
            console.log(salesCode);
            $.ajax({
                url: "http://localhost:8012/sales-incentive/ControllerSalesIncentive/Summary",
                type: "POST",
                dataType: "json",
                data: {
                    salesCode: salesCode,
                    fromDate: fromDate,
                    endDate: endDate,
                },
                success: function(response) {
                    console.log("Response");
                    console.log(response);
                    // if (response) {
                    //     let table = response.si;
                    //     let tbodyContent = '';

                    //     $.each(table, function(i, data) {
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
                    //                 <td>${data.BILLING_DATE}</td>
                    //                 <td>${data.QTY}</td>
                    //                 <td>${data.PRICE}</td>
                    //                 <td>${data.SLS_CD}</td>
                    //                 <td>${data.SLS_NAME}</td>
                    //                 <td>${data.INC_BIG}</td>
                    //                 <td>${data.INC_PERC}</td>
                    //                 <td>${data.INC_PREMIUM}</td>
                    //             </tr>
                    //         `;
                    //     });

                    //     $("#table_body").html(tbodyContent);
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
    });
</script>
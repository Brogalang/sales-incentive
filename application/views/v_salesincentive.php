<style>

.ribbon1 {
    position: absolute;
    top: -6.1px;
    right: 10px;
}

.ribbon1:after {
    position: absolute;
    content: "";
    width: 0;
    height: 0;
    border-left: 53px solid transparent;
    border-right: 53px solid transparent;
    border-top: 7px solid #F8463F;
}

.ribbon1 span {
    position: relative;
    display: block;
    text-align: center;
    background: #F8463F;
    font-size: 14px;
    line-height: 1;
    padding: 12px 8px 10px;
    border-top-right-radius: 8px;
    width: 90px;
}

.ribbon1 span:before,
.ribbon1 span:after {
    position: absolute;
    content: "";
}

.ribbon1 span:before {
    height: 6px;
    width: 6px;
    left: -6px;
    top: 0;
    background: #F8463F;
}

.ribbon1 span:after {
    height: 6px;
    width: 8px;
    left: -8px;
    top: 0;
    border-radius: 8px 8px 0 0;
    background: #C02031;
}

/* Make layout wider - only addition */
.container {
    max-width: 1400px !important;
    padding-left: 30px !important;
    padding-right: 30px !important;
}

.card-body {
    padding: 1.75rem;
}

.table th, .table td {
    padding: 1rem 0.75rem;
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
                                <input type="text" name="from_date" class="form-control" id="from_date" autocomplete="off" placeholder="Start Date" readonly required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-info text-white" id="basic-addon1"><i class="fas fa-calendar-alt"></i></span>
                                </div>
                                <input type="text" name="end_date" class="form-control" id="end_date" autocomplete="off" placeholder="End Date" readonly required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group mb-3">
                                <select name="code" id="code" autocomplete="off" class="form-control">
                                    <option value="salescode">Sales Code</option>
                                    <option value="storecode">Store Code</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6" id="salesCodeInputContainer">
                            <div class="input-group mb-3">
                                <input type="number" class="form-control" id="sales_code_input" autocomplete="off" placeholder="Sales Code" value="<?php if (!is_null($salesData)) { echo html_escape($salesData['user_id']); } ?>"  maxlength="6"  <?php if (!is_null($salesData)) { echo 'disabled'; } ?>>
                            </div>
                        </div>
                        <div class="col-md-6" id="storeCodeInputContainer" style="display:none;">
                            <div class="input-group mb-3">
                                <input type="number" class="form-control" id="store_code_input" autocomplete="off" placeholder="Store Code" value="<?php if (!is_null($storeData)) { echo html_escape($storeData['store_code']); } ?>"  maxlength="4"  <?php if (!is_null($storeData)) { echo 'disabled'; } ?>>
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
                <div class="ribbon1" id="bagesTiering" >
                        <span style="color: white; font-weight: bold;" id="badges-storetiering"></span>
                    </div>

                    <div class="row">
                        <div class="col-md-6" id="salesCodeOutputContainer">
                            <div class="form-group">
                                <label for="sales_code">Sales Code</label>
                                <input type="text" name="sales_code" class="form-control" id="sales_code" autocomplete="off" placeholder="Sales Code" disabled>
                            </div>
                        </div>
                        <div class="col-md-6" id="salesNameOutputContainer">
                            <div class="form-group">
                                <label for="sales_name">Sales Name</label>
                                <input type="text" name="sales_name" class="form-control" id="sales_name" autocomplete="off" placeholder="Sales Name" disabled>
                            </div>
                        </div>
                        <div class="col-md-6" id="storeCodeOutputContainer" style="display:none;">
                            <div class="form-group">
                                <label for="store_code">Store Code</label>
                                <input type="text" name="store_code" class="form-control" id="store_code" autocomplete="off" placeholder="Store Code" disabled>
                            </div>
                        </div>
                        <div class="col-md-6" id="storeNameOutputContainer" style="display:none;">
                            <div class="form-group">
                                <label for="store_name">Store Name</label>
                                <input type="text" name="store_name" class="form-control" id="store_name" autocomplete="off" placeholder="Store Name" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="total_incentive_big">Total Incentive Big</label>
                                <input type="text" name="total_incentive_big" class="form-control" id="incentive_big" autocomplete="off" placeholder="Total Incentive Big" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="total_incentive_percentage">Total Incentive Percentage</label>
                                <input type="text" class="form-control" id="incentive_percentage" autocomplete="off" placeholder="Incentive Percentage" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="total_incentive_premium">Total Incentive Premium</label>
                                <input type="text" class="form-control" id="incentive_premium" autocomplete="off" placeholder="Incentive Premium" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="grand_total">Grand Total</label>
                                <input type="text" class="form-control" id="grand_total" autocomplete="off" placeholder="Grand Total" disabled>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-body">


                    <div class="row">
                        <div class="col-md-1 mb-3">
                            <select class="form-control" id="showEntries" autocomplete="off">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                        <div class="col-md-11 mb-3">
                            <button id="exportToExcel" class="btn btn-success">Excel</button>
                        </div>
                    </div>
                    <table id="example1" class="table table-responsive mb-3">
                        <thead class="table-header">
                            <tr>
                                <th>No</th>
                                <th>Billing_No</th>
                                <th>Billing_Item</th>
                                <th>Billing_Type</th>
                                <th>Billing_Date</th>
                                <th>Char_Size</th>
                                <th>Brand</th>
                                <th>Article</th>
                                <th>Article_Desc</th>
                                <th>Store_Code</th>
                                <th>Store_Name</th>
                                <th>Invoice</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Sales_Code</th>
                                <th>Sales_Name</th>
                                <th>Big</th>
                                <th>Percentage</th>
                                <th>Premium</th>
                            </tr>
                        </thead>
                        <tbody class="table-body" id="table_body">
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-md-12">
                            <button id="prevPage" class="btn btn-primary">Previous</button>
                            <button id="nextPage" class="btn btn-primary">Next</button>
                            <span id="pageInfo" class="ml-3 text-muted"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-1 mb-3">
                            <select class="form-control" id="showEntries">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                        <div class="col-md-7 mb-3">
                            <button id="exportToExcel" class="btn btn-success">Excel</button>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="searchInput" placeholder="Search">
                            </div>
                        </div>
                    </div>
                    <table id="example1" class="table table-responsive mb-3">
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
                    <div class="row">
                        <div class="col-md-12">
                            <button id="prevPage" class="btn btn-primary">Previous</button>
                            <button id="nextPage" class="btn btn-primary">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
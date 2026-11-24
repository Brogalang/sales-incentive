$(function () {
    // Get base_url dynamically from current location
    var base_url = window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '') + '/';

    $("#bagesTiering").hide();

    // * Validasi rentang tanggal: maksimal 1 bulan (From & End Date harus di bulan yang sama)
    function validateDateRange(fromDate, endDate) {
        var from = moment(fromDate, "YYYY-MM-DD", true);
        var end = moment(endDate, "YYYY-MM-DD", true);
        if (!from.isValid() || !end.isValid()) {
            return "Format tanggal tidak valid";
        }
        if (end.isBefore(from)) {
            return "End Date tidak boleh lebih kecil dari From Date";
        }
        if (!from.isSame(end, "month")) {
            return "Rentang tanggal maksimal 1 bulan.\nEnd Date harus di bulan yang sama dengan From Date (maksimal " + from.clone().endOf("month").format("YYYY-MM-DD") + ").";
        }
        return null;
    }

    // * Pagination (server-side): tabel hanya berisi 1 halaman dari server
    var rowsPerPage = parseInt($("#showEntries").val(), 10) || 10; // * Jumlah data per halaman (selalu ikut nilai dropdown)
    var currentPage = 1;  // * Halaman saat ini
    var totalRows = 0;    // * Total baris hasil query (dari server)
    var lastQuery = null; // * Parameter query terakhir yang berhasil dimuat
    var lastCtx = null;

    // * Token CSRF (dari meta tag di template) untuk setiap request POST
    function csrfData() {
        var d = {};
        d[$('meta[name="csrf-name"]').attr('content')] = $('meta[name="csrf-token"]').attr('content');
        return d;
    }

    function escapeHtml(v) {
        return String(v === null || v === undefined ? '' : v)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function formatNumber(v) {
        var n = parseInt(v);
        return (isNaN(n) ? 0 : n).toLocaleString('id-ID');
    }

    function updatePager() {
        var totalPages = Math.max(1, Math.ceil(totalRows / rowsPerPage));
        $("#pageInfo").text("Page " + currentPage + " of " + totalPages + " (" + totalRows.toLocaleString('id-ID') + " rows)");
        $("#prevPage").prop("disabled", currentPage <= 1);
        $("#nextPage").prop("disabled", currentPage >= totalPages);
    }

    function renderRows(rows) {
        var startNo = (currentPage - 1) * rowsPerPage;
        var html = '';
        $.each(rows, function (i, data) {
            var bd = String(data.BillingDate || '');
            var dateFormat = bd.substring(0, 4) + '-' + bd.substring(4, 6) + '-' + bd.substring(6, 8);

            html += '<tr>' +
                '<td>' + (startNo + i + 1) + '</td>' +
                '<td>' + escapeHtml(data.BillingNo) + '</td>' +
                '<td>' + escapeHtml(data.BillingItem) + '</td>' +
                '<td>' + escapeHtml(data.BillingTyp) + '</td>' +
                '<td>' + dateFormat + '</td>' +
                '<td>' + escapeHtml(data.CharSize) + '</td>' +
                '<td>' + escapeHtml(data.Brand) + '</td>' +
                '<td>' + escapeHtml(data.Article) + '</td>' +
                '<td>' + escapeHtml(data.ArticleDesc) + '</td>' +
                '<td>' + escapeHtml(data.Soffice) + '</td>' +
                '<td>' + escapeHtml(data.SofficeDesc) + '</td>' +
                '<td>' + escapeHtml(data.PosInv) + '</td>' +
                '<td>' + escapeHtml(data.Qty) + '</td>' +
                '<td>' + formatNumber(data.Price) + '</td>' +
                '<td>' + escapeHtml(data.SlsCd) + '</td>' +
                '<td>' + escapeHtml(data.SlsName) + '</td>' +
                '<td>' + formatNumber(data.IncBig) + '</td>' +
                '<td>' + formatNumber(data.IncPerc) + '</td>' +
                '<td>' + formatNumber(data.IncPremium) + '</td>' +
                '</tr>';
        });
        $("#table_body").html(html);
    }
    // * Date Format
    function getCurrentDateFormatted() {
        const currentDate = new Date();

        const day = currentDate.getDate().toString().padStart(2, '0');
        const month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
        const year = currentDate.getFullYear();

        const formattedDate = day + month + year;

        return formattedDate;
    }

    // * Pembulatan Desimal ke Integer
    function pembulatanRupiah(angka) {
        var bilanganBulat = Math.floor(angka);
        var duaDigitTerakhir = bilanganBulat % 100;
        var selisih = 100 - duaDigitTerakhir;
        //var hasilPembulatan = (selisih === 100) ? bilanganBulat : bilanganBulat + selisih;
        var hasilPembulatan = bilanganBulat;
        return hasilPembulatan;
    }

    // * Max Length 6 Sales Code
    document.getElementById('sales_code_input').addEventListener('input', function () {
        var maxLength = 6;
        if (this.value.length > maxLength) {
            this.value = this.value.slice(0, maxLength);
        }
    });

    // * Max Length 4 Store Code
    document.getElementById('store_code_input').addEventListener('input', function () {
        var maxLength = 4;
        if (this.value.length > maxLength) {
            this.value = this.value.slice(0, maxLength);
        }
    });

    // * From Date
    // var yesterday = new Date();
    // yesterday.setDate(yesterday.getDate() - 1);
    // var formattedYesterday = yesterday.toISOString().split('T')[0];
    // $("#from_date").val(formattedYesterday);
    // var yesterday = new Date();
    // yesterday.setDate(yesterday.getDate() - 1);
    $("#from_date").datepicker({
        maxDate: '-1',
        dateFormat: 'yy-mm-dd',
        onSelect: function (selectedDate) {
            $("#end_date").datepicker("option", "minDate", selectedDate);
            // Maksimal End Date = akhir bulan dari From Date (atau kemarin bila lebih kecil)
            var fromMoment = moment(selectedDate, "YYYY-MM-DD");
            var lastDay = fromMoment.clone().endOf("month");
            var yesterday = moment().subtract(1, "days").endOf("day");
            $("#end_date").datepicker("option", "maxDate", (lastDay.isBefore(yesterday) ? lastDay : yesterday).toDate());
            var curEnd = $("#end_date").val();
            if (curEnd && validateDateRange(selectedDate, curEnd)) {
                $("#end_date").val("");
            }
        }
    });

    // * End Date
    $("#end_date").datepicker({
        maxDate: '-1',
        dateFormat: 'yy-mm-dd',
        onSelect: function (selectedDate) {
            $("#start_date").datepicker("option", "maxDate", selectedDate);
        }
    });

    // * Pagination (Show Entries)
    $("#showEntries").on("change", function () {
        rowsPerPage = parseInt($(this).val());
        if (lastQuery) {
            loadPage(lastQuery, lastCtx, 1, false);
        }
    });
    // * Summary Output
    $("#code").on("change", function () {

        var selectedOption = $(this).val();
        if (selectedOption === "storecode") { // * Store Code Display, Sales Code Hide
            $("#bagesTiering").hide();
            $("#storeCodeOutputContainer").show();
            $("#storeNameOutputContainer").show();
            $("#salesCodeOutputContainer").hide();
            $("#salesNameOutputContainer").hide();
        } else if (selectedOption === "salescode") { // * Sales Code Display, Store Code Hide
            $("#bagesTiering").hide();
            $("#storeCodeOutputContainer").hide();
            $("#storeNameOutputContainer").hide();
            $("#salesCodeOutputContainer").show();
            $("#salesNameOutputContainer").show();
        } else {
            $("#bagesTiering").hide();
            $("#storeCodeOutputContainer").hide();
            $("#storeNameOutputContainer").hide();
            $("#salesCodeOutputContainer").hide();
            $("#salesNameOutputContainer").hide();
        }
    });

    // * Dropdown Filter Field Store Code & Sales Code
    $("#code").on("change", function () {
        var selectedOption = $(this).val();

        $("#store_code").val("");
        $("#store_name").val("");
        $("#sales_code").val("");
        $("#sales_name").val("");

        $("#incentive_big").val("");
        $("#incentive_percentage").val("");
        $("#incentive_premium").val("");
        $("#grand_total").val("");
        $("#badges-storetiering").html("");

        if (selectedOption === "storecode") {
            $("#bagesTiering").show();
            $("#storeCodeInputContainer").show();
            $("#salesCodeInputContainer").hide();
        } else if (selectedOption === "salescode") {
            $("#bagesTiering").hide();
            $("#storeCodeInputContainer").hide();
            $("#salesCodeInputContainer").show();
        } else {
            $("#bagesTiering").hide();
            $("#storeCodeInputContainer").hide();
            $("#salesCodeInputContainer").hide();
        }
    });

    // * Ajax
    function showAjaxError(xhr, error) {
        console.error("AJAX error:", xhr.status, xhr.statusText, error);

        var errorMsg = "Error fetching data.\n\n";
        if (xhr.status === 0) {
            errorMsg += "Network Error: Cannot reach server.\nCheck if API server (10.140.4.221:86) is accessible.";
        } else if (xhr.status === 401 || xhr.status === 403) {
            errorMsg += "Authentication Error: Please login again.\nYour session may have expired.";
        } else if (xhr.status === 404) {
            errorMsg += "Endpoint Not Found (404): Check if ControllerSalesIncentive/Summary exists.";
        } else if (xhr.status === 500) {
            errorMsg += "Server Error (500): " + xhr.responseText;
        } else {
            errorMsg += "Status: " + xhr.status + " " + xhr.statusText + "\n" + xhr.responseText;
        }
        alert(errorMsg);
    }

    // * Isi ringkasan (store/sales info + total) setelah Get Data
    function showSummary(response, ctx) {
        var rows = response.data.value;

        if (ctx.salesCode == "" && ctx.storeCode == "") {
            $("#store_code").val("--");
            $("#store_name").val("--");
            $("#sales_code").val("--");
            $("#sales_name").val("--");
        } else {
            $("#store_code").val(rows[0]['Soffice']);
            $("#store_name").val(rows[0]['SofficeDesc']);
            $("#sales_code").val(rows[0]['SlsCd']);
            $("#sales_name").val(rows[0]['SlsName']);
            $("#badges-storetiering").html(response.store_tiering);
        }

        var roundedIncBig = pembulatanRupiah(response.totals.IncBig);
        var roundedIncPerc = pembulatanRupiah(response.totals.IncPerc);
        var roundedIncPremium = pembulatanRupiah(response.totals.IncPremium);

        $("#incentive_big").val("Rp " + roundedIncBig.toLocaleString('id-ID'));
        $("#incentive_percentage").val("Rp " + roundedIncPerc.toLocaleString('id-ID'));
        $("#incentive_premium").val("Rp " + roundedIncPremium.toLocaleString('id-ID'));
        var grandTotal = roundedIncBig + roundedIncPerc + roundedIncPremium;
        $("#grand_total").val("Rp " + grandTotal.toLocaleString('id-ID'));
    }

    // * Ambil 1 halaman dari server. withTotals=true hanya saat Get Data (hitung total & jumlah baris).
    function loadPage(query, ctx, page, withTotals) {
        $("#loadingOverlay").show();

        $.ajax({
            url: base_url + "ControllerSalesIncentive/Summary",
            type: "POST",
            dataType: "json",
            data: $.extend({}, query, csrfData(), {
                page: page,
                pageSize: rowsPerPage,
                withTotals: withTotals ? 1 : 0
            }),
            success: function (response) {
                if (response.status != 200) {
                    $("#loadingOverlay").hide();
                    alert(response.message || "Gagal mengambil data");
                    return;
                }

                lastQuery = query;
                lastCtx = ctx;
                currentPage = response.page;

                if (withTotals) {
                    totalRows = response.total_rows;
                    showSummary(response, ctx);
                }

                renderRows(response.data.value);
                updatePager();
                $("#loadingOverlay").hide();
            },
            error: function (xhr, status, error) {
                $("#loadingOverlay").hide();
                showAjaxError(xhr, error);
            }
        });
    }

    $("#getData").on("click", function () {
        var fromDate = $("#from_date").val();
        var endDate = $("#end_date").val();
        var salesCode = $("#sales_code_input").val();
        var storeCode = $("#store_code_input").val();
        var selectedOption = $("#code").val();

        // Validate dates
        if (!fromDate || !endDate) {
            alert("Please select both From Date and End Date");
            return;
        }

        var rangeError = validateDateRange(fromDate, endDate);
        if (rangeError) {
            alert(rangeError);
            return;
        }

        var query;
        if (selectedOption === "storecode") { // * Store Code
            query = {
                storeCode: storeCode,
                fromDate: moment(fromDate, "YYYY-MM-DD").format("YYYYMMDD"),
                endDate: moment(endDate, "YYYY-MM-DD").format("YYYYMMDD")
            };
        } else if (selectedOption === "salescode") { // * Sales Code
            query = {
                salesCode: salesCode,
                fromDate: moment(fromDate, "YYYY-MM-DD").format("YYYYMMDD"),
                endDate: moment(endDate, "YYYY-MM-DD").format("YYYYMMDD")
            };
        } else {
            alert("Please select Store Code or Sales Code");
            return;
        }

        loadPage(query, { salesCode: salesCode, storeCode: storeCode }, 1, true);
    });

    // * Prev Page
    $("#prevPage").on("click", function () {
        if (lastQuery && currentPage > 1) {
            loadPage(lastQuery, lastCtx, currentPage - 1, false);
        }
    });

    // * Next Page
    $("#nextPage").on("click", function () {
        var totalPages = Math.ceil(totalRows / rowsPerPage);
        if (lastQuery && currentPage < totalPages) {
            loadPage(lastQuery, lastCtx, currentPage + 1, false);
        }
    });
    // * Export To Excel
    // Form dikirim ke iframe tersembunyi supaya halaman tidak berpindah. Loading ditahan sampai server
    // benar-benar mulai mengirim file (ditandai cookie "downloadToken"), bukan pakai timer tetap,
    // karena menyusun file untuk data besar bisa memakan waktu lebih dari satu menit.
    $("#exportToExcel").on("click", function () {
        var $btn = $(this);
        if ($btn.prop("disabled")) {
            return; // export sebelumnya masih berjalan
        }

        var fromDate = $("#from_date").val();
        var endDate = $("#end_date").val();
        var salesCode = $("#sales_code_input").val() || '';
        var storeCode = $("#store_code_input").val() || '';

        // Validate dates
        if (!fromDate || !endDate) {
            alert("Please select both From Date and End Date");
            return;
        }

        var rangeError = validateDateRange(fromDate, endDate);
        if (rangeError) {
            alert(rangeError);
            return;
        }

        var token = String(Date.now()) + String(Math.floor(Math.random() * 1000000));
        var frameName = "exportFrame_" + token;
        var startedAt = Date.now();
        var finished = false;
        var timer = null;

        $btn.prop("disabled", true);
        $("#loadingOverlay").show();

        var $iframe = $('<iframe>').attr('name', frameName).css('display', 'none').appendTo('body');

        var form = $('<form>')
            .attr('method', 'POST')
            .attr('action', base_url + 'ControllerSalesIncentive/ExportExcel')
            .attr('target', frameName)
            .append($('<input>').attr('type', 'hidden').attr('name', 'salesCode').val(salesCode))
            .append($('<input>').attr('type', 'hidden').attr('name', 'storeCode').val(storeCode))
            .append($('<input>').attr('type', 'hidden').attr('name', 'fromDate').val(moment(fromDate, "YYYY-MM-DD").format("YYYYMMDD")))
            .append($('<input>').attr('type', 'hidden').attr('name', 'endDate').val(moment(endDate, "YYYY-MM-DD").format("YYYYMMDD")))
            .append($('<input>').attr('type', 'hidden').attr('name', 'downloadToken').val(token))
            .append($('<input>').attr('type', 'hidden').attr('name', $('meta[name="csrf-name"]').attr('content')).val($('meta[name="csrf-token"]').attr('content')))
            .appendTo('body');

        function finish(message) {
            if (finished) {
                return;
            }
            finished = true;
            clearInterval(timer);
            document.cookie = "downloadToken=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";
            $("#loadingOverlay").hide();
            $btn.prop("disabled", false);
            setTimeout(function () {
                form.remove();
                $iframe.remove();
            }, 1000);
            if (message) {
                alert(message);
            }
        }

        // Download dimulai -> server mengirim cookie dengan token yang sama
        timer = setInterval(function () {
            if (document.cookie.indexOf("downloadToken=" + token) !== -1) {
                finish();
            } else if (Date.now() - startedAt > 10 * 60 * 1000) {
                finish("Export memakan waktu terlalu lama. Silakan coba lagi atau persempit rentang tanggal.");
            }
        }, 500);

        // Server menolak / gagal -> respons JSON dimuat di iframe (download file tidak memicu event ini)
        $iframe.on("load", function () {
            if (finished) {
                return;
            }
            try {
                if (this.contentWindow.location.href === "about:blank") {
                    return;
                }
                var res = JSON.parse(this.contentDocument.body.innerText);
                finish(res.message || "Export gagal.");
            } catch (e) {
                // bukan JSON (mis. file mulai diunduh) -> biarkan cookie yang menentukan
            }
        });

        form.submit();
    });

    // * Button Reset
    $("#reset").on("click", function () {
        $("#from_date").val("");
        $("#end_date").val("");
        // $("#sales_code_input").val("");
        // $("#store_code_input").val("");
    });

    // * Press Enter to trigger "Get Data" button for Sales Code input
    $("#sales_code_input").on("keydown", function (e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            $("#getData").trigger("click");
        }
    });

    // * Press Enter to trigger "Get Data" button for Store Code input
    $("#store_code_input").on("keydown", function (e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            $("#getData").trigger("click");
        }
    });
});

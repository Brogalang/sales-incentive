<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ControllerSalesIncentive extends CI_Controller
{
    public $result;

    public $url;
    // public $token;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');

        // Halaman setelah login jangan disimpan browser (cache / tombol Back setelah logout tidak menampilkan data lama)
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $this->output->set_header('Pragma: no-cache');
        $this->output->set_header('Expires: 0');

        // $token = $this->session->userdata('token');

        // var_dump($token);die;
    }


    public function headers () {
		header("Content-Type: application/json"	);
	}

    public function getCekUserStore ($user_id) {
        $token = $this->session->userdata('token');
        // var_dump($token);die;

		$this->headers();

		$endPoint = "UserStores?";   
		$filter   = "\$filter=".urlencode("User/Code eq '" . odata_quote($user_id) . "'")."&";
		$expand   = "\$expand=".urlencode("User(\$select=Code,Name,StoreRegional;\$expand=Role(\$select=Code, Name, RoleType, IsAdmin)),Store")."&";
		
		$url = $this->url.$endPoint.$filter.$expand;

		

		$response = parseExec($token, $url);
        if(empty($response->value)) {
            // var_dump("mASUK SINI");die;
            return null;
        } else {
            return reset($response->value);
        }



        // var_dump($response);die;
        // if(empty($response->value)) {
        //     return r
        //     $message = $response['data']->error;
        //     // $this->session->set_flashdata("message", "<div class='alert alert-danger' role='alert'> $message </div>");
        //     // redirect('auth');
        // } else {
        // }
    }

    public function setJson($data) {
		header('Content-Type: application/json');
		return json_encode($data);
	}

    public function getStoreByCode ($code) {
		$this->headers();
        $token = $this->session->userdata('token');

		$endPoint = "Stores?";   
		// $select   = "\$select=".urlencode("Code, Name, StoreRegional")."&";
		$filter   = "\$filter=".urlencode("Code eq '" . odata_quote($code) . "'")."&";
		
		$url = $this->url.$endPoint.$filter;
		
		$response = parseExec($token, $url);

        if(empty($response->value)) {
            // $message = $response['data']->error;
            // $this->session->set_flashdata("message", "<div class='alert alert-danger' role='alert'> $message </div>");
            // redirect('auth');
        } else {
            return reset($response->value);
            // return $response->value;
        }
    }


    
    /**
     * Normalisasi nama role: huruf kecil tanpa spasi/underscore/strip ("HQ Admin" == "hq_admin").
     */
    private function _normRole($value)
    {
        return strtolower(preg_replace('/[\s_\-]+/', '', (string) $value));
    }

    /**
     * Apakah role ini boleh melihat SEMUA toko? Ya bila is_admin dari API, atau role_type / role_name /
     * role_code-nya terdaftar di config 'all_store_roles'.
     */
    private function _hasAllStoreAccess($role_data)
    {
        if (empty($role_data)) {
            return false;
        }
        if (!empty($role_data['is_admin'])) {
            return true;
        }

        $allowed = array();
        foreach ((array) $this->config->item('all_store_roles') as $r) {
            $allowed[] = $this->_normRole($r);
        }
        foreach (array('role_type', 'role_name', 'role_code') as $key) {
            if (!isset($role_data[$key])) {
                continue;
            }
            $v = $this->_normRole($role_data[$key]);
            if ($v !== '' && in_array($v, $allowed, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Validasi hak akses terhadap store/sales yang diminta berdasarkan role.
     * Scope ditentukan server (session), bukan nilai dari form.
     * $storeCode & $salesCode bisa diubah (default scope untuk role non-admin).
     *
     * @return array|null  null jika boleh, atau ['status' => int, 'message' => string]
     */
    private function _authorizeScope(&$storeCode, &$salesCode)
    {
        $token = $this->session->userdata('token');
        $user_data = $this->session->userdata('user_data');
        $role_data = $this->session->userdata('role_data');
        $store_data = $this->session->userdata('store_data');

        if (!$token || empty($role_data)) {
            return ['status' => 401, 'message' => 'Sesi berakhir, silakan login kembali.'];
        }

        $roleType = $role_data['role_type'];

        if ($roleType == 'AreaManager') {
            if (!empty($storeCode)) {
                $found = false;
                foreach ($user_data['store_regional']['list_of_store'] as $store) {
                    if ($store->Code === $storeCode) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    return ['status' => 400, 'message' => 'Data Store tidak ditemukan di regional Anda.'];
                }
            } elseif (!empty($salesCode)) {
                $resp = $this->getCekUserStore($salesCode);
                if ($resp == null) {
                    return ['status' => 400, 'message' => 'Data Sales code ' . $salesCode . ', tidak ditemukan.'];
                }
                if ($user_data['store_regional']['store_regional_code'] != $resp->Store->StoreRegional->Code) {
                    return ['status' => 400, 'message' => 'Data Sales tidak ditemukan di regional Anda.'];
                }
            }
        } elseif ($roleType == 'StoreManager') {
            if (!empty($storeCode) && $storeCode !== $store_data['store_code']) {
                return ['status' => 400, 'message' => 'Anda tidak memiliki akses ke data store tersebut.'];
            }
            if (!empty($salesCode)) {
                $resp = $this->getCekUserStore($salesCode);
                if ($resp == null) {
                    return ['status' => 400, 'message' => 'Sales Code ' . $salesCode . ', tidak ditemukan.'];
                }
                if ($store_data['store_code'] != $resp->Store->Code) {
                    return ['status' => 400, 'message' => 'Data Sales code ' . $salesCode . '. tidak ditemukan di store Anda.'];
                }
            }
            if (empty($storeCode) && empty($salesCode)) {
                $storeCode = $store_data['store_code'];
            }
        } elseif ($roleType == 'Sales') {
            if (!empty($storeCode) && $storeCode !== $store_data['store_code']) {
                return ['status' => 400, 'message' => 'Anda tidak memiliki akses ke data store tersebut.'];
            }
            if (!empty($salesCode) && $salesCode !== $user_data['user_id']) {
                return ['status' => 400, 'message' => 'Anda hanya dapat melihat data incentive Anda sendiri.'];
            }
            if (empty($storeCode) && empty($salesCode)) {
                $salesCode = $user_data['user_id'];
            }
        } elseif (!$this->_hasAllStoreAccess($role_data)) {
            // Role yang tidak dikenal DITOLAK (bukan dianggap bebas akses). Role dengan akses semua toko
            // (Admin, HQ Admin, dst.) didaftarkan di config 'all_store_roles'.
            return ['status' => 403, 'message' => 'Role Anda tidak memiliki akses ke data ini.'];
        }

        return null;
    }

    /**
     * Kode store/sales dari form hanya boleh huruf, angka, titik, minus, underscore (maks 20 karakter).
     * Ini lapis pertama pencegah injection ke filter OData (lapis kedua: odata_quote()).
     *
     * @return array|null  ['status' => 400, 'message' => ...] atau null jika valid
     */
    private function _validateCodes($storeCode, $salesCode)
    {
        foreach (['Store Code' => $storeCode, 'Sales Code' => $salesCode] as $label => $value) {
            if ($value !== null && $value !== '' && !preg_match('/^[A-Za-z0-9._-]{1,20}$/', (string) $value)) {
                return ['status' => 400, 'message' => $label . ' tidak valid.'];
            }
        }
        return null;
    }

    // Kolom SalesIncentive yang dipakai tampilan & export. Hanya kolom ini yang ditarik dari API
    // supaya respons lebih kecil dan tidak menghabiskan memori.
    const SI_SELECT = 'BillingNo,BillingItem,BillingTyp,BillingDate,CharSize,Brand,Article,ArticleDesc,Soffice,SofficeDesc,PosInv,Qty,Price,SlsCd,SlsName,IncBig,IncPerc,IncPremium';

    /**
     * Rentang tanggal harus valid (YYYYMMDD), from <= end, dan berada dalam bulan kalender yang sama
     * (mis. mulai 2 Jan -> maksimal 31 Jan).
     *
     * @return string|null  pesan error, atau null jika valid
     */
    private function _validateDateRange($fromDate, $endDate)
    {
        $from = DateTime::createFromFormat('!Ymd', (string) $fromDate);
        $end  = DateTime::createFromFormat('!Ymd', (string) $endDate);

        if (!$from || !$end || $from->format('Ymd') !== (string) $fromDate || $end->format('Ymd') !== (string) $endDate) {
            return 'Format tanggal tidak valid.';
        }
        if ($end < $from) {
            return 'End Date tidak boleh lebih kecil dari From Date.';
        }
        if ($from->format('Ym') !== $end->format('Ym')) {
            return 'Rentang tanggal maksimal 1 bulan (From Date dan End Date harus di bulan yang sama).';
        }
        return null;
    }

    // Urutan tetap supaya paginasi ($skip/$top) stabil dan tidak ada baris yang dobel/terlewat
    const SI_ORDERBY = 'BillingDate,BillingNo,BillingItem';

    /**
     * Susun $filter OData untuk SalesIncentive (diakhiri '&'). Tanda kutip pada kode di-escape.
     */
    private function _buildIncentiveFilter($storeCode, $salesCode, $fromDate, $endDate)
    {
        $q = function ($v) { // escape tanda kutip OData
            return str_replace("'", "''", (string) $v);
        };
        $range = "BillingDate ge '" . $q($fromDate) . "' and BillingDate le '" . $q($endDate) . "'";

        if (!empty($storeCode)) {
            $cond = "Soffice eq '" . $q($storeCode) . "' and " . $range;
        } elseif (!empty($salesCode)) {
            $cond = "SlsCd eq '" . $q($salesCode) . "' and " . $range;
        } else {
            $cond = $range;
        }
        return "\$filter=" . urlencode($cond) . "&";
    }

    /**
     * Hitung jumlah baris & total Inc Big/Perc/Premium dengan membaca data per halaman
     * (hanya 3 kolom, baris tidak disimpan) sehingga memori tetap kecil.
     *
     * @return array|null  null jika ada halaman yang gagal diambil
     */
    private function _sumTotals($token, $baseUrl)
    {
        @set_time_limit(0);
        $pageSize = 5000;
        $maxPages = 1000;
        $sum = ['count' => 0, 'IncBig' => 0.0, 'IncPerc' => 0.0, 'IncPremium' => 0.0];

        $skip = 0;
        $done = false;
        for ($p = 0; $p < $maxPages; $p++) {
            $resp = parseExec($token, $baseUrl . '&$select=IncBig,IncPerc,IncPremium&$top=' . $pageSize . '&$skip=' . $skip);
            if (!is_object($resp) || !isset($resp->value) || !is_array($resp->value)) {
                return null;
            }
            $n = count($resp->value);
            if ($n === 0) {
                $done = true;
                break;
            }
            foreach ($resp->value as $r) {
                $sum['IncBig']     += isset($r->IncBig) ? (float) $r->IncBig : 0;
                $sum['IncPerc']    += isset($r->IncPerc) ? (float) $r->IncPerc : 0;
                $sum['IncPremium'] += isset($r->IncPremium) ? (float) $r->IncPremium : 0;
            }
            $sum['count'] += $n;
            unset($resp);

            // Maju sesuai jumlah baris yang benar-benar diterima (API bisa membatasi ukuran halaman),
            // dan berhenti hanya saat halaman kosong supaya tidak ada data yang terlewat.
            $skip += $n;
        }

        // Batas halaman tercapai sebelum data habis -> jangan tampilkan total yang tidak lengkap
        return $done ? $sum : null;
    }

    /**
     * Data tabel diambil per halaman (page, pageSize). Total & jumlah baris hanya dihitung
     * bila withTotals=1 (saat klik Get Data); pindah halaman tidak menghitung ulang.
     */
    public function Summary () {
        $this->headers();
        $this->url = "http://10.140.4.221:86/";
        $token = $this->session->userdata('token');

        $storeCode = $this->input->post('storeCode');
        $salesCode = $this->input->post('salesCode');
        $fromDate = $this->input->post('fromDate');
        $endDate = $this->input->post('endDate');

        $page = max(1, (int) $this->input->post('page'));
        $pageSize = (int) $this->input->post('pageSize');
        if (!in_array($pageSize, [10, 25, 50, 100], true)) {
            $pageSize = 10;
        }
        $withTotals = $this->input->post('withTotals') === '1';

        $error = $this->_validateCodes($storeCode, $salesCode);
        if ($error === null) {
            $error = $this->_authorizeScope($storeCode, $salesCode);
        }
        if ($error !== null) {
            echo json_encode([
                'status' => $error['status'],
                'message' => $error['message'],
                'data' => null
            ]);
            return;
        }

        $rangeError = $this->_validateDateRange($fromDate, $endDate);
        if ($rangeError !== null) {
            echo json_encode([
                'status' => 400,
                'message' => $rangeError,
                'data' => null
            ]);
            return;
        }

        $filter  = $this->_buildIncentiveFilter($storeCode, $salesCode, $fromDate, $endDate);
        $baseUrl = $this->url . 'SalesIncentive?' . $filter . '$orderby=' . urlencode(self::SI_ORDERBY);

        $response = parseExec($token, $baseUrl . '&$select=' . self::SI_SELECT . '&$top=' . $pageSize . '&$skip=' . (($page - 1) * $pageSize));

        if (!is_object($response) || !isset($response->value) || !is_array($response->value)) {
            echo json_encode([
                'status' => 502,
                'message' => 'Gagal mengambil data dari server API. Silakan coba lagi.',
                'data' => null,
                'store_tiering' => null
            ]);
            return;
        }

        if (empty($response->value)) {
            echo json_encode([
                'status' => 404,
                'message' => 'Data incentive tidak ditemukan',
                'data' => null,
                'store_tiering' => null
            ]);
            return;
        }

        $result = [
            'status' => 200,
            'message' => '',
            'data' => ['value' => $response->value],
            'page' => $page,
            'page_size' => $pageSize,
            'total_rows' => null,
            'totals' => null,
            'store_tiering' => null
        ];

        if ($withTotals) {
            $totals = $this->_sumTotals($token, $baseUrl);
            if ($totals === null) {
                echo json_encode([
                    'status' => 502,
                    'message' => 'Gagal menghitung total dari server API. Silakan coba lagi.',
                    'data' => null,
                    'store_tiering' => null
                ]);
                return;
            }
            $result['total_rows'] = $totals['count'];
            $result['totals'] = [
                'IncBig' => $totals['IncBig'],
                'IncPerc' => $totals['IncPerc'],
                'IncPremium' => $totals['IncPremium']
            ];

            $respGetStore = $this->getStoreByCode($response->value[0]->Soffice);
            $result['store_tiering'] = isset($respGetStore->StoreTiering->Name) ? $respGetStore->StoreTiering->Name : null;
        }

        echo json_encode($result);
    }
    public function ExportExcel()
    {
        $this->url = "http://10.140.4.221:86/";
        $token = $this->session->userdata('token');

        if (!$token) {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 401,
                'message' => 'Unauthorized: Please login first'
            ]);
            return;
        }

        $salesCode = $this->input->post('salesCode');
        $storeCode = $this->input->post('storeCode');
        $fromDate = $this->input->post('fromDate');
        $endDate = $this->input->post('endDate');

        // Validate required fields
        if (empty($fromDate) || empty($endDate)) {
            header('HTTP/1.1 400 Bad Request');
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 400,
                'message' => 'From Date and End Date are required'
            ]);
            return;
        }

        // Terapkan pembatasan akses berdasarkan role (sama seperti Summary)
        $error = $this->_validateCodes($storeCode, $salesCode);
        if ($error === null) {
            $error = $this->_authorizeScope($storeCode, $salesCode);
        }
        if ($error !== null) {
            header('HTTP/1.1 ' . ($error['status'] == 401 ? '401 Unauthorized' : '403 Forbidden'));
            header('Content-Type: application/json');
            echo json_encode([
                'status' => $error['status'],
                'message' => $error['message']
            ]);
            return;
        }

        $rangeError = $this->_validateDateRange($fromDate, $endDate);
        if ($rangeError !== null) {
            header('HTTP/1.1 400 Bad Request');
            header('Content-Type: application/json');
            echo json_encode(['status' => 400, 'message' => $rangeError]);
            return;
        }

        // Semua data session sudah dibaca. Lepas kunci session selama file disusun (bisa lama),
        // supaya request lain dari user yang sama (mis. buka halaman lain) tidak ikut tertahan.
        session_write_close();

        // Token dari browser, dikembalikan lewat cookie saat download dimulai (untuk menutup loading)
        $dlToken = preg_replace('/[^A-Za-z0-9]/', '', (string) $this->input->post('downloadToken'));

        $filter = $this->_buildIncentiveFilter($storeCode, $salesCode, $fromDate, $endDate);

        // Data ditarik per halaman ($top/$skip) dan langsung ditulis ke file sementara di disk,
        // sehingga memori yang dipakai kecil berapapun jumlah barisnya.
        @set_time_limit(0);
        $pageSize = 2000;
        $maxPages = 5000;
        $baseUrl  = $this->url . 'SalesIncentive?' . $filter
                  . '$select=' . self::SI_SELECT
                  . '&$orderby=' . urlencode('BillingDate,BillingNo,BillingItem');

        $esc = function ($v) {
            return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        };

        $headers = ['No', 'Billing No', 'Billing Item', 'Billing Type', 'Date', 'Char Size', 'Brand', 'Article',
                    'Article Desc', 'Store Code', 'Store Name', 'Pos Inv', 'Qty', 'Price', 'Sales Code', 'Sales Name',
                    'Inc Big', 'Inc Perc', 'Inc Premium'];

        // Kolom yang harus tetap berupa teks (kode berawalan 0 / angka panjang tidak boleh diubah Excel)
        $textCols = ['Billing No', 'Billing Item', 'Billing Type', 'Article', 'Store Code', 'Pos Inv', 'Sales Code'];

        $tmpFile = tempnam(sys_get_temp_dir(), 'si_');
        $fh = $tmpFile ? fopen($tmpFile, 'wb') : false;
        if (!$fh) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: application/json');
            echo json_encode(['status' => 500, 'message' => 'Tidak dapat membuat file sementara untuk export.']);
            return;
        }

        $out  = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        $out .= '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
        $out .= '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Sales Incentive</x:Name>';
        $out .= '<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        $out .= '<style>td,th{border:1px solid #999;} th{background:#d9e1f2;font-weight:bold;} .t{mso-number-format:"\@";}</style>';
        $out .= '</head><body><table><tr>';
        foreach ($headers as $h) {
            $out .= '<th>' . $esc($h) . '</th>';
        }
        $out .= '</tr>';
        fwrite($fh, $out);

        $rowNum = 1;
        $failed = false;
        $finished = false;
        $skip = 0;
        for ($page = 0; $page < $maxPages; $page++) {
            $response = parseExec($token, $baseUrl . '&$top=' . $pageSize . '&$skip=' . $skip);

            // Respons gagal / bukan format OData -> batalkan, jangan kirim file yang tidak lengkap
            if (!is_object($response) || !isset($response->value) || !is_array($response->value)) {
                $failed = true;
                break;
            }
            if (count($response->value) === 0) {
                $finished = true;
                break;
            }

            $buf = '';
            foreach ($response->value as $row) {
                $bd = isset($row->BillingDate) ? (string) $row->BillingDate : '';
                $dateFormat = substr($bd, 0, 4) . '-' . substr($bd, 4, 2) . '-' . substr($bd, 6, 2);

                $data = [
                    $rowNum++,
                    isset($row->BillingNo) ? $row->BillingNo : '',
                    isset($row->BillingItem) ? $row->BillingItem : '',
                    isset($row->BillingTyp) ? $row->BillingTyp : '',
                    $dateFormat,
                    isset($row->CharSize) ? $row->CharSize : '',
                    isset($row->Brand) ? $row->Brand : '',
                    isset($row->Article) ? $row->Article : '',
                    isset($row->ArticleDesc) ? $row->ArticleDesc : '',
                    isset($row->Soffice) ? $row->Soffice : '',
                    isset($row->SofficeDesc) ? $row->SofficeDesc : '',
                    isset($row->PosInv) ? $row->PosInv : '',
                    isset($row->Qty) ? $row->Qty : '',
                    isset($row->Price) ? $row->Price : '',
                    isset($row->SlsCd) ? $row->SlsCd : '',
                    isset($row->SlsName) ? $row->SlsName : '',
                    isset($row->IncBig) ? $row->IncBig : '',
                    isset($row->IncPerc) ? $row->IncPerc : '',
                    isset($row->IncPremium) ? $row->IncPremium : ''
                ];

                $buf .= '<tr>';
                foreach ($data as $i => $d) {
                    $class = in_array($headers[$i], $textCols, true) ? ' class="t"' : '';
                    $buf .= '<td' . $class . '>' . $esc($d) . '</td>';
                }
                $buf .= '</tr>';
            }
            fwrite($fh, $buf);

            // Maju sesuai jumlah baris yang benar-benar diterima (API bisa membatasi ukuran halaman),
            // dan berhenti hanya saat halaman kosong supaya tidak ada data yang terlewat.
            $skip += count($response->value);
            unset($response, $buf);   // bebaskan memori sebelum halaman berikutnya
        }

        fwrite($fh, '</table></body></html>');
        fclose($fh);

        // Batas halaman tercapai sebelum data habis -> jangan kirim file yang tidak lengkap
        if (!$finished) {
            $failed = true;
        }

        if ($failed) {
            @unlink($tmpFile);
            header('HTTP/1.1 502 Bad Gateway');
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 502,
                'message' => 'Gagal mengambil data dari server API. Silakan coba lagi.'
            ]);
            return;
        }

        if ($rowNum === 1) {
            @unlink($tmpFile);
            header('HTTP/1.1 404 Not Found');
            header('Content-Type: application/json');
            echo json_encode(['status' => 404, 'message' => 'No data found']);
            return;
        }

        $filename = 'sales_incentive_' . date('YmdHis') . '.xls';
        if ($dlToken !== '') {
            // Sinyal ke browser bahwa file siap dan download dimulai (dibaca JS untuk menutup loading)
            setcookie('downloadToken', $dlToken, 0, '/');
        }
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmpFile));
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        readfile($tmpFile);
        @unlink($tmpFile);
        exit();
    }

    public function Index()
    {
        if (!$this->session->userdata('token')) {
            redirect('auth');
        }

        $user_data = $this->session->userdata('user_data');
        $store_data = $this->session->userdata('store_data');
        $role_data = $this->session->userdata('role_data');

        $data = [];
        if ($this->_hasAllStoreAccess($role_data)) {
            $data = [
                'judul' => 'Sales Incentive',
                'menu' => 'SalesIncentive',
                'navbar' => 'SalesIncentive',
                'page' => 'v_salesincentive',
                'salesData' => null,
                'storeData' => null
            ];   
        } else if ($role_data['role_type'] == 'AreaManager') {
            $data = [
                'judul' => 'Sales Incentive',
                'menu' => 'SalesIncentive',
                'navbar' => 'SalesIncentive',
                'page' => 'v_salesincentive',
                'salesData' => null,
                'storeData' => null
            ];                
        } else if ($role_data['role_type'] == 'StoreManager') {
            $data = [
                'judul' => 'Sales Incentive',
                'menu' => 'SalesIncentive',
                'navbar' => 'SalesIncentive',
                'page' => 'v_salesincentive',
                'salesData' => null,
                'storeData' => $store_data
            ];    
        } else if ($role_data['role_type'] == 'Sales') {
            $data = [
                'judul' => 'Sales Incentive',
                'menu' => 'SalesIncentive',
                'navbar' => 'SalesIncentive',
                'page' => 'v_salesincentive',
                'salesData' => $user_data,
                'storeData' => $store_data
            ];    
        } else {
            // Role tidak dikenal / tidak terdaftar: jangan beri akses, kembali ke login
            $this->session->unset_userdata(array('token', 'user_data', 'role_data', 'store_data'));
            $this->session->set_flashdata('message', "<div class='alert alert-danger' role='alert'> Role Anda tidak memiliki akses ke aplikasi ini. </div>");
            redirect('auth');
        }

        $newData = [
            'profile_name' => $user_data['user_name'],
            'profile_role' => $role_data['role_type']
        ];

        $data = array_merge($data, $newData);


        // print_r($data['profile_name']);die;

        $this->load->view('v_template', $data);
    }




}

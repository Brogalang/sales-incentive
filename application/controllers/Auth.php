<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public $url;
	public $token;
	public $innerToken;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');

        // Halaman login tidak di-cache supaya selalu mendapat captcha dan token CSRF yang baru
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $this->output->set_header('Pragma: no-cache');
        

    }

    public function headers () {
		header("Content-Type: application/json"	);
	}

    	
	/**
     * Simpan pesan error ke flashdata. Pesan di-escape karena bisa berasal dari respons API.
     */
    private function _flashError($message)
    {
        $this->session->set_flashdata("message", "<div class='alert alert-danger' role='alert'> " . html_escape($message) . " </div>");
    }

	public function setJson($data) {
		header('Content-Type: application/json');
		return json_encode($data);
	}
    
    /**
     * Gambar captcha (PNG) dibuat langsung dengan GD. Kode disimpan di session dan
     * dihapus setelah dipakai (sekali pakai), jadi tidak ada file yang perlu dibersihkan.
     */
    public function captcha()
    {
        // Tanpa karakter yang mirip (0/O, 1/I/L)
        $chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        $word = '';
        for ($i = 0; $i < 5; $i++) {
            $word .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $this->session->set_userdata('captcha_word', $word);

        $w = 150; $h = 44; $scale = 3; // font bawaan GD kecil, jadi digambar lalu diperbesar
        $small = imagecreatetruecolor((int) ($w / $scale), (int) ($h / $scale));
        $bg = imagecolorallocate($small, 245, 245, 245);
        imagefill($small, 0, 0, $bg);

        $sw = imagesx($small); $sh = imagesy($small);
        for ($i = 0; $i < 25; $i++) { // noise titik
            imagesetpixel($small, random_int(0, $sw - 1), random_int(0, $sh - 1), imagecolorallocate($small, random_int(120, 200), random_int(120, 200), random_int(120, 200)));
        }
        $x = 4;
        for ($i = 0; $i < strlen($word); $i++) {
            $color = imagecolorallocate($small, random_int(0, 110), random_int(0, 110), random_int(0, 110));
            imagechar($small, 5, $x, random_int(0, max(0, $sh - 16)), $word[$i], $color);
            $x += 9 + random_int(0, 1);
        }

        $img = imagecreatetruecolor($w, $h);
        imagecopyresampled($img, $small, 0, 0, 0, 0, $w, $h, $sw, $sh);
        for ($i = 0; $i < 4; $i++) { // noise garis
            imageline($img, random_int(0, $w), random_int(0, $h), random_int(0, $w), random_int(0, $h), imagecolorallocate($img, random_int(80, 190), random_int(80, 190), random_int(80, 190)));
        }

        header('Content-Type: image/png');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        imagepng($img);
        exit;
    }

    public function index()
    {
        $this->form_validation->set_rules('user_id', 'User ID', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required');

        // Captcha dicek paling akhir; kode langsung dihapus (sekali pakai) agar tidak bisa dipakai ulang
        $session = $this->session;
        $this->form_validation->set_rules('captcha', 'Captcha', [
            'required',
            ['captcha_check', function ($input) use ($session) {
                $word = $session->userdata('captcha_word');
                $session->unset_userdata('captcha_word');
                return is_string($word) && $word !== '' && hash_equals(strtolower($word), strtolower(trim((string) $input)));
            }]
        ], ['captcha_check' => 'Kode captcha salah.']);

        if ($this->form_validation->run() == false) {
            //gagal
            $this->load->view('auth/login');
        } else {
            //berhasil
            $this->_login();
        }
    }

    private function getMyToken ($user_id,  $password) {
        $this->url = "http://10.140.4.221:86/";
        $this->innerToken = getToken($this->url, $user_id, $password);

		if($this->innerToken['status'] == 'failed') {
            // 'data' can be a curl error string, a decoded JSON object, or NULL
            $errData = $this->innerToken['data'];
            if (is_string($errData)) {
                // Error koneksi curl: jangan tampilkan detail (berisi host/port internal), cukup dicatat di log
                log_message('error', 'Login API connection error: ' . $errData);
                $message = 'Login gagal. Tidak dapat terhubung ke server API.';
            } elseif (isset($errData->error_description)) {
                $message = $errData->error_description;
            } elseif (isset($errData->error)) {
                $message = $errData->error;
            } else {
                $message = 'Login gagal. Tidak dapat terhubung ke server API.';
            }
            $this->_flashError($message);
            redirect('auth');
		} elseif (empty($this->innerToken['data']->access_token)) {
            $this->_flashError('Login gagal. Respons server tidak valid.');
            redirect('auth');
        } else {
			$this->token = $this->innerToken['data']->access_token;
            return $this->token;
		}
    }

    public function getCekUserStore ($user_id) {
		$this->headers();

		$endPoint = "UserStores?";   
		$filter   = "\$filter=".urlencode("User/Code eq '" . odata_quote($user_id) . "'")."&";
		$expand   = "\$expand=".urlencode("User(\$select=Code,Name,StoreRegional;\$expand=Role(\$select=Code, Name, RoleType, IsAdmin)),Store")."&";
		
		$url = $this->url.$endPoint.$filter.$expand;
		
		$response = parseExec($this->token, $url);

        // var_dump($response);die;
        if(empty($response->value)) {
            $this->_flashError('Data user store tidak ditemukan.');
            redirect('auth');
        } else {
            return reset($response->value);
        }
    }

    public function getStore ($regional_code) {
		$this->headers();

		$endPoint = "Stores?";   
		$select   = "\$select=".urlencode("Code, Name, StoreRegional")."&";
		$filter   = "\$filter=".urlencode("StoreRegional/Code eq '" . odata_quote($regional_code) . "'")."&";
		
		$url = $this->url.$endPoint.$select.$filter;
		
		$response = parseExec($this->token, $url);

        if(empty($response->value)) {
            $this->_flashError('Data store regional tidak ditemukan.');
            redirect('auth');
        } else {
            return $response->value;
        }
    }



    private function _login()
    {
        $user_id = $this->input->post('user_id');
        $password = $this->input->post('password');
        
        $this->token  = $this->getMyToken($user_id, $password);
        $respCekUserStore = $this->getCekUserStore($user_id);

        $storeRegional = null;
        if($respCekUserStore->User->StoreRegional == null) {
            $storeRegional = null;
        } else {
            
            $listOfStore = $this->getStore($respCekUserStore->User->StoreRegional->Code);

            $storeRegional = [
                'store_regional_code' => $respCekUserStore->User->StoreRegional->Code,
                'store_regional_name' => $respCekUserStore->User->StoreRegional->Name,
                'list_of_store' => $listOfStore                
            ];


        }

        $user_data = [
            'user_id' => $user_id,
            'user_name' => $respCekUserStore->User->Name,
            'store_regional' => $storeRegional
        ];
        

        $role_data = [
            'role_code' => $respCekUserStore->User->Role->Code,
            'role_name' => $respCekUserStore->User->Role->Name,
            'role_type' => $respCekUserStore->User->Role->RoleType,
            'is_admin' => $respCekUserStore->User->Role->IsAdmin
        ];

        
        $store_data = [
            'store_code' => $respCekUserStore->Store->Code,
            'store_name' => $respCekUserStore->Store->Name,
            'store_tiering' => isset($respCekUserStore->Store->StoreTiering->Name) ? $respCekUserStore->Store->StoreTiering->Name : null
        ];
        
        $data = [
            'user_data' => $user_data,
            'role_data' => $role_data,
            'store_data' => $store_data,
            'token' => $this->token
        ];

        // Ganti session ID setelah login berhasil (mencegah session fixation)
        $this->session->sess_regenerate(TRUE);

        $this->session->set_userdata($data);

        redirect('/Si');
    }

    // Logout hanya lewat POST (dengan token CSRF) supaya tidak bisa dipicu dari link/gambar di situs lain
    public function logout()
    {
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Pragma: no-cache");

        if ($this->input->method() !== 'post') {
            redirect('/Si');
        }

        $this->session->sess_destroy();

        redirect('auth');
    }
}

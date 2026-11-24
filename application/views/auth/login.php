<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">


    <title>Electronic City | Report Sales Incentive</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom fonts for this template-->
    <link href="<?= base_url('assets') ?>/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="<?= base_url('assets') ?>/css/sb-admin-2.min.css" rel="stylesheet">

</head>

<body>

    <div class="container">
        <!-- Outer Row -->
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <!-- <div class="col-lg-6 d-none d-lg-block bg-login-image"></div> -->
                            <div class="col-lg">
                                <div class="p-5">
                                    <div class="text-center">
                                        <a href="#" class="navbar-brand">
                                            <img src="<?= base_url() ?>/assets/img/eci.png" alt="ECI" width="200px">
                                        </a>
                                    </div>
                                    <br>
                                    <h1 class="h4 text-gray-900 mb-4 text-center">Report Sales Incentive</h1>

                                    <?= $this->session->flashdata('message'); ?>

                                    <form class="user" method="post" action="<?= base_url('auth') ?>">
                                        <input type="hidden" name="<?= html_escape($this->security->get_csrf_token_name()) ?>" value="<?= html_escape($this->security->get_csrf_hash()) ?>">
                                        <div class="form-group">
                                            <input type="text" name="user_id" class="form-control form-control-user" id="user_id" placeholder="User ID" value="<?= set_value('user_id') ?>" maxlength="8">
                                            <?= form_error('user_id', '<small class="text-danger pl-3">', '</small>') ?>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" name="password" class="form-control form-control-user" id="password" placeholder="Password" value="<?= set_value('password') ?>" maxlength="22">
                                            <?= form_error('password', '<small class="text-danger pl-3">', '</small>') ?>
                                        </div>
                                        <div class="form-group">
                                            <div class="d-flex align-items-center mb-2">
                                                <img src="<?= base_url('auth/captcha') ?>?t=<?= time() ?>" id="captchaImage" alt="Captcha" width="150" height="44" style="border:1px solid #d1d3e2;border-radius:4px;">
                                                <a href="#" id="refreshCaptcha" class="ml-2 small" title="Ganti kode">Refresh</a>
                                            </div>
                                            <input type="text" name="captcha" class="form-control form-control-user" id="captcha" placeholder="Masukkan kode di atas" maxlength="5" autocomplete="off">
                                            <?= form_error('captcha', '<small class="text-danger pl-3">', '</small>') ?>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="<?= base_url('assets') ?>/vendor/jquery/jquery.min.js"></script>
    <script src="<?= base_url('assets') ?>/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="<?= base_url('assets') ?>/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="<?= base_url('assets') ?>/js/sb-admin-2.min.js"></script>

    <script>
        $('#refreshCaptcha').on('click', function(e) {
            e.preventDefault();
            $('#captchaImage').attr('src', '<?= base_url('auth/captcha') ?>?t=' + Date.now());
            $('#captcha').val('').focus();
        });
    </script>

</body>

</html>
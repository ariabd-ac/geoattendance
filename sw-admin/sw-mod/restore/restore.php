<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<?php
if (empty($connection)) {
    header('location:../../');
} else {
    include_once 'sw-mod/sw-panel.php';
    require_once '../sw-library/phpqrcode/qrlib.php';

    echo '
  <div class="content-wrapper">';
    switch (@$_GET['op']) {
        default:
            echo '
<section class="content-header">
  <h1> Backup and Restore Data</h1>
    <ol class="breadcrumb">
      <li><a href="./"><i class="fa fa-dashboard"></i> Beranda</a></li>
      <li class="active">Backup and Restore Data</li>
    </ol>
</section>';
            echo '
            <section class="content">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="box box-solid d-flex justify-content-center align-items-center">
                            <div class="d-flex justify-content-center">
                            <form method="POST" action="sw-mod/restore/proses.php">
                                <input type="hidden" value="backup" name="action">
                                <button type="submit" value="backup" class="btn btn-success"><i class="fa fa-download"></i> Backup Data</button>
                            </form>
                            <form method="POST" action="sw-mod/restore/proses.php" enctype="multipart/form-data">
                                <input type="hidden" value="restore" name="action">
                                <input class="form-control" type="file" name="file">
                                <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Restore Data</button>
                            </form>
                            </div> 
                        </div>
                    </div>
                </div>
            </section>';

            if (isset($_SESSION['flash'])) {

                echo "  <script>
                        $(document).ready(function() {
                            swal('" . $_SESSION['flash']['message'] . "','','" . $_SESSION['flash']['type'] . "');
                        });
                        </script>";
                unset($_SESSION['flash']);
            }
            break;
    } ?>
    </div>

<?php } ?>
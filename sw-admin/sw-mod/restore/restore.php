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
                                <button type="button" class="btn btn-success"><i class="fa fa-download"></i> Backup Data</button>
                                <button type="button" class="btn btn-primary"><i class="fa fa-upload"></i> Restore Data</button>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </section>';
            break;
    } ?>

    </div>
<?php } ?>
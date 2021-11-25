<?php session_start();
error_reporting(0);
require_once '../../../sw-library/sw-config.php';
require_once '../../../sw-library/sw-function.php';
include_once '../../../sw-library/vendor/autoload.php';
if (empty($_SESSION['SESSION_USER']) && empty($_SESSION['SESSION_ID'])) {
  //Kondisi tidak login
  header('location:../login/');
} else {
  //kondisi login
  switch (@$_GET['action']) {
      /* -------  CETAK PDF-----------------------------------------------*/
    case 'pdf':
      if (empty($_GET['id'])) {
        $error[] = 'ID tidak boleh kosong';
      } else {
        $id = mysqli_real_escape_string($connection, $_GET['id']);
      }

      if (empty($error)) {
        $query = "SELECT employees.id,employees.employees_name,employees.position_id,position.position_name,employees.shift_id FROM employees,position WHERE employees.position_id=position.position_id AND employees.id='$id'";
        $result = $connection->query($query);

        if ($result->num_rows > 0) {
          $row            = $result->fetch_assoc();
          $employees_name = $row['employees_name'];

          if (isset($_GET['from']) or isset($_GET['to'])) {
            $bulan   = date($_GET['from']);
          } else {
            $bulan  = date("m");
          }
          $hari       = date("d");
          $tahun      = date("Y");
          $jumlahhari = date("t", mktime(0, 0, 0, $bulan, $hari, $tahun));
          $s          = date("w", mktime(0, 0, 0, $bulan, 1, $tahun));
          $mpdf = new \Mpdf\Mpdf();
          ob_start();

          $mpdf->SetHTMLFooter('
      <table width="100%" style="border-top:solid 1px #333;font-size:11px;">
          <tr>
              <td width="60%" style="text-align:left;">Simpanlah lembar Absensi ini.</td>
              <td width="35%" style="text-align: right;">Dicetak tanggal ' . tgl_indo($date) . '</td>
          </tr>
      </table>');
          echo '<!DOCTYPE html>
<html>
<head>
    <title>Cetak Data Absensi ' . $employees_name . '</title>
    <style>
    body{font-family:Arial,Helvetica,sans-serif}.container_box{position:relative}.container_box .row h3{line-height:25px;font-size:20px;margin:0px 0 10px;text-transform:uppercase}.container_box .text-center{text-align:center}.container_box .content_box{position:relative}.container_box .content_box .des_info{margin:20px 0;text-align:right}.container_box h3{font-size:30px}table.customTable{width:100%;background-color:#fff;border-collapse:collapse;border-width:1px;border-color:#b3b3b3;border-style:solid;color:#000}table.customTable td,table.customTable th{border-width:1px;border-color:#b3b3b3;border-style:solid;padding:5px;text-align:left}table.customTable thead{background-color:#f6f3f8}.text-center{text-align:center}
    .label {display: inline;padding: .2em .6em .3em;font-size: 75%;font-weight: 700;line-height: 1;color: #fff;text-align: center;white-space: nowrap; vertical-align: baseline;border-radius: .25em;}
    .label-success{background-color: #00a65a !important;}.label-warning {background-color: #f0ad4e;}.label-info {background-color: #5bc0de;}.label-danger{background-color: #dd4b39 !important;}
    p{line-height:20px;padding:0px;margin: 5px;}.pull-right{float:right}
    </style>
</head>
<body>';
          echo '
    <section class="container_box">
      <div class="row">';
          if (isset($_GET['from']) or isset($_GET['to'])) {
            // echo '<img src="../../../sw-content/kop.PNG">';
            echo '<h3 class="text-center">LAPORAN DETAIL HARIAN<br>PERIODE WAKTU ' . tanggal_indo($_GET['from']) . ' - ' . $_GET['to'] . '</h3>';
          } else {
            echo '<h3 class="text-center">LAPORAN DETAIL BULAN<br>' . tanggal_indo($month) . ' - ' . $year . '</h3>';
          }
          echo '
        <p>Nama   : ' . $row['employees_name'] . '</p>
        <p>Jabatan : ' . $row['position_name'] . '</p><br>
      <div class="content_box">
        <table class="customTable">
          <thead>
            <tr>
              <th class="text-center">No.</th>
              <th>Tanggal</th>
              <th class="text-center">Jam Masuk</th>
              <th class="text-center">Scan Masuk</th>
              <th>Terlambat</th>
              <th class="text-center">Jam Pulang</th>
              <th class="text-center">Scan Pulang</th>
              <th class="text-center">Pulang Cepat</th>
              <th>Durasi</th>
              <th>Lembur</th>
              <th>Status</th>
              <th>Keterangan</th>
            </tr>
          </thead>
        <tbody>';
          for ($d = 1; $d <= $jumlahhari; $d++) {
            $warna      = '';
            $background = '';
            $status     = 'Tidak Hadir';
            if (date("l", mktime(0, 0, 0, $bulan, $d, $tahun)) == "Sunday") {
              $warna = 'white';
              $background = '#FF0000';
              $status = 'Libur Akhir Pekan';
            }
            $date_month_year = '' . $year . '-' . $bulan . '-' . $d . '';

            if (isset($_GET['from']) or isset($_GET['to'])) {
              $month = $_GET['from'];
              $year  = $_GET['to'];
              $filter = "employees_id='$id' AND presence_date='$date_month_year' AND MONTH(presence_date)='$month' AND year(presence_date)='$year'";
            } else {
              $filter = "employees_id='$id' AND  presence_date='$date_month_year' AND MONTH(presence_date) ='$month'";
            }


            $query_shift = "SELECT time_in,time_out FROM shift WHERE shift_id='$row[shift_id]'";
            $result_shift = $connection->query($query_shift);
            $row_shift = $result_shift->fetch_assoc();
            $shift_time_in = $row_shift['time_in'];
            $shift_time_out = $row_shift['time_out'];
            $newtimestamp = strtotime('' . $shift_time_in . ' + 05 minute');
            $newtimestamp = date('H:i:s', $newtimestamp);

            $query_absen = "SELECT presence_id,presence_date,time_in,time_out,picture_in,picture_out,present_id,latitude_longtitude_in,information,TIMEDIFF(TIME(time_in),'$shift_time_in') AS selisih,if (time_in>'$shift_time_in','Telat',if(time_in='00:00:00','Tidak Masuk','Tepat Waktu')) AS status,TIMEDIFF(TIME(time_out),'$shift_time_out') AS selisih_out FROM presence WHERE $filter ORDER BY presence_id DESC";
            $result_absen = $connection->query($query_absen);
            $row_absen = $result_absen->fetch_assoc();

            $querya = "SELECT present_id,present_name FROM present_status WHERE present_id='$row_absen[present_id]'";
            $resulta = $connection->query($querya);
            $rowa =  $resulta->fetch_assoc();

            if ($row_absen['time_in'] == NULL) {
              if (date("l", mktime(0, 0, 0, $bulan, $d, $tahun)) == "Sunday") {
                $status = 'Libur Akhir Pekan';
              } else {
                $status = '<span class="label label-danger">Tidak Hadir</span>';
              }
              $time_in = $row_absen['time_in'];
            } else {
              $status = $rowa['present_name'];
              $time_in = $row_absen['time_in'];
            }

            // Status Absensi Jam Masuk
            if ($row_absen['status'] == 'Telat') {
              $status_time_in = '<label class="label label-danger pull-right">' . $row_absen['status'] . '</label>';
            } elseif ($row_absen['status'] == 'Tepat Waktu') {
              $status_time_in = '<label class="label label-info pull-right">' . $row_absen['status'] . '</label>';
              $terlamat   = '';
            } else {
              $status_time_in = '';
              $terlamat   = '';
            }

            // DURASI KERJA  =========================================
            $durasi_kerja_start = strtotime('' . $date_month_year . ' ' . $row_absen['time_in'] . '');
            $durasi_kerja_end   = strtotime('' . $date_month_year . ' ' . $row_absen['time_out'] . '');
            $diff          = $durasi_kerja_end - $durasi_kerja_start;
            $durasi_jam       = floor($diff / (60 * 60));
            $durasi_menit     = $diff - ($durasi_jam * (60 * 60));
            $durasi_detik     = $diff % 60;
            $durasi_kerja     = '' . $durasi_jam . ' jam, ' . floor($durasi_menit / 60) . ' menit';

            // JAM LEMBUR =========================================
            if ($row_absen['time_out'] > $shift_time_out) {
              $lembur_kerja_start = strtotime('' . $date_month_year . ' ' . $shift_time_out . '');
              $lembur_kerja_end   = strtotime('' . $date_month_year . ' ' . $row_absen['time_out'] . '');
              $diff          = $lembur_kerja_end - $lembur_kerja_start;
              $lembur_jam       = floor($diff / (60 * 60));
              $lembur_menit     = $diff - ($lembur_jam * (60 * 60));
              $lembur       = '' . $lembur_jam . ' jam, ' . floor($lembur_menit / 60) . ' menit';
            } else {
              $lembur = '';
            }
            echo '
         <tr style="background:' . $background . ';color:' . $warna . '">
            <td class="text-center">' . $d . '</td>
            <td>' . format_hari_tanggal($date_month_year) . '</td>';
            if (date("l", mktime(0, 0, 0, $bulan, $d, $tahun)) == "Sunday") {
              if ($row_absen['time_in'] == '') {
                echo '
                <td class="text-center" colspan="3">Libur Akhir Pekan</td>';
              } else {
                echo '
                <td class="text-center">' . $row_absen['time_in'] . '</td>
                <td class="text-center">' . $row_absen['time_in'] . '</td>
              	<td class="text-center">' . $row_absen['selisih'] . '</td>';
              }
            } else {
              echo '
              <td class="text-center">' . $shift_time_in . '</td>
              <td class="text-center">' . $row_absen['time_in'] . '</td>
              <td class="text-center">' . $row_absen['selisih'] . '</td>';
            }

            if (date("l", mktime(0, 0, 0, $bulan, $d, $tahun)) == "Sunday") {
              if ($row_absen['time_out'] == '') {
                echo '
                <td class="text-center" colspan="3">Libur Akhir Pekan</td>';
              } else {
                echo '
                <td class="text-center">' . $row_shift['time_out'] . '</td>
              	<td class="text-center">' . $row_absen['time_out'] . '</td>
              	<td class="text-center">' . $row_absen['selisih_out'] . '</td>';
              }
            } else {
              echo '
              <td class="text-center">' . $row_shift['time_out'] . '</td>
              <td class="text-center">' . $row_absen['time_out'] . '</td>
              <td class="text-center">' . $row_absen['selisih_out'] . '</td>';
            }
            echo '
              <td>' . $durasi_kerja . '</td>
              <td>' . $lembur . '</td>
              <td>' . $status . ' ' . $status_time_in . '</td>
              <td>' . $row_absen['information'] . '</td>
          </tr>';
          }

          echo '<tbody>
      </table>';
          if (isset($_GET['from']) or isset($_GET['to'])) {
            $month = $_GET['from'];
            $year  = $_GET['to'];
            $filter = "employees_id='$id' AND MONTH(presence_date)='$month' AND year(presence_date)='$year' AND employees_id='$id'";
          } else {
            $filter = "employees_id='$id' AND MONTH(presence_date) ='$month' AND employees_id='$id'";
          }

          $query_hadir = "SELECT presence_id FROM presence WHERE $filter AND present_id='1' ORDER BY presence_id DESC";
          $hadir = $connection->query($query_hadir);

          $query_sakit = "SELECT presence_id FROM presence WHERE $filter AND present_id='2' ORDER BY presence_id";
          $sakit = $connection->query($query_sakit);

          $query_izin = "SELECT presence_id FROM presence WHERE $filter AND present_id='3' ORDER BY presence_id";
          $izin = $connection->query($query_izin);

          $query_telat = "SELECT presence_id FROM presence WHERE $filter AND time_in>'$shift_time_in'";
          $telat = $connection->query($query_telat);

          echo '<p>Hadir : <span class="label label-success">' . $hadir->num_rows . '</span></p>
          <p>Telat : <span class="label label-danger">' . $telat->num_rows . '</span></p>
          <p>Sakit : <span class="label label-warning">' . $sakit->num_rows . '</span></p>
          <p>Izin : <span class="label label-info">' . $izin->num_rows . '</span></p>

      </div>
    </div>
  </section>
</body>
</html>';
          $html = ob_get_contents();
          ob_end_clean();
          $mpdf->WriteHTML(utf8_encode($html));
          $mpdf->Output("Absensi-$date.pdf", 'I');
        } else {
          echo '<center><h3>Data Tidak Ditemukan</h3></center>';
        }
      } else {
        echo 'Data tidak boleh ada yang kosong!';
      }

      //Explore to Excel -------------------------------------------------------
      break;
    case 'excel':

      if (empty($_GET['id'])) {
        $error[] = 'ID tidak boleh kosong';
      } else {
        $id = mysqli_real_escape_string($connection, $_GET['id']);
      }

      if (empty($error)) {
        $query = "SELECT employees.id,employees.employees_name,employees.shift_id,employees.position_id,position.position_name FROM employees,position WHERE employees.position_id=position.position_id AND employees.id='$id'";
        $result = $connection->query($query);

        if ($result->num_rows > 0) {
          $row            = $result->fetch_assoc();
          $employees_name = $row['employees_name'];

          if (isset($_GET['from']) or isset($_GET['to'])) {
            $bulan   = date($_GET['from']);
          } else {
            $bulan  = date("m");
          }

          $hari       = date("d");
          $tahun      = date("Y");
          $jumlahhari = date("t", mktime(0, 0, 0, $bulan, $hari, $tahun));
          $s          = date("w", mktime(0, 0, 0, $bulan, 1, $tahun));
          $mpdf = new \Mpdf\Mpdf();
          ob_start();

          if (empty($_GET['print'])) {
            header("Content-type: application/vnd-ms-excel");
            header("Content-Disposition: attachment; filename=Data-Absensi-$employees_name-$date.xls");
          } else {
            echo '<script>
      window.print();
    </script>';
          }


          echo '<!DOCTYPE html>
<html>
<head>
    <title>Cetak Data Absensi ' . $employees_name . '</title>
    <style>
    body{font-family:Arial,Helvetica,sans-serif}.container_box{position:relative}.container_box .row h3{line-height:25px;font-size:20px;margin:0px 0 10px;text-transform:uppercase}.container_box .text-center{text-align:center}.container_box .content_box{position:relative}.container_box .content_box .des_info{margin:20px 0;text-align:right}.container_box h3{font-size:30px}table.customTable{width:100%;background-color:#fff;border-collapse:collapse;border-width:1px;border-color:#b3b3b3;border-style:solid;color:#000}table.customTable td,table.customTable th{border-width:1px;border-color:#b3b3b3;border-style:solid;padding:5px;text-align:left}table.customTable thead{background-color:#f6f3f8}.text-center{text-align:center}
    .label {display: inline;padding: .2em .6em .3em;font-size: 75%;font-weight: 700;line-height: 1;color: #fff;text-align: center;white-space: nowrap; vertical-align: baseline;border-radius: .25em;}
    .label-success{background-color: #00a65a !important;}.label-warning {background-color: #f0ad4e;}.label-info {background-color: #5bc0de;}.label-danger{background-color: #dd4b39 !important;}
    p{line-height:20px;padding:0px;margin: 5px;}.pull-right{float:right}
    </style>
  <script>
     window.print();
  </script>
</head>
<body>';
          echo '
    <section class="container_box">
      <div class="row">';
          if (isset($_GET['from']) or isset($_GET['to'])) {
            echo '<img src="../../../sw-content/kop.PNG">"';
            echo '<h3 class="text-center">LAPORAN DETAIL HARIAN<br>PERIODE WAKTU ' . tanggal_indo($_GET['from']) . ' - ' . $_GET['to'] . '</h3>';
          } else {
            echo '<h3 class="text-center">LAPORAN DETAIL BULAN<br>' . tanggal_indo($month) . ' - ' . $year . '</h3>';
          }
          echo '
        <p>Nama   : ' . $row['employees_name'] . '</p>
        <p>Jabatan : ' . $row['position_name'] . '</p><br>
        <div class="content_box">
        <table class="customTable">
          <thead>
            <tr>
              <th class="text-center">No.</th>
              <th>Tanggal</th>
              <th class="text-center">Jam Masuk</th>
              <th class="text-center">Scan Masuk</th>
              <th>Terlambat</th>
              <th class="text-center">Jam Pulang</th>
              <th class="text-center">Scan Pulang</th>
              <th class="text-center">Pulang Cepat</th>
              <th>Durasi</th>
              <th>Lembur</th>
              <th>Status</th>
              <th>Keterangan</th>
            </tr>
          </thead>
        <tbody>';
          for ($d = 1; $d <= $jumlahhari; $d++) {
            $warna      = '';
            $background = '';
            $status     = 'Tidak Hadir';
            if (date("l", mktime(0, 0, 0, $bulan, $d, $tahun)) == "Sunday") {
              $warna = 'white';
              $background = '#FF0000';
              $status = 'Libur Akhir Pekan';
            }
            $date_month_year = '' . $year . '-' . $bulan . '-' . $d . '';

            if (isset($_GET['from']) or isset($_GET['to'])) {
              $month = $_GET['from'];
              $year  = $_GET['to'];
              $filter = "employees_id='$id' AND presence_date='$date_month_year' AND MONTH(presence_date)='$month' AND year(presence_date)='$year'";
            } else {
              $filter = "employees_id='$id' AND  presence_date='$date_month_year' AND MONTH(presence_date) ='$month'";
            }


            $query_shift = "SELECT time_in,time_out FROM shift WHERE shift_id='$row[shift_id]'";
            $result_shift = $connection->query($query_shift);
            $row_shift = $result_shift->fetch_assoc();
            $shift_time_in = $row_shift['time_in'];
            $shift_time_out = $row_shift['time_out'];
            $newtimestamp = strtotime('' . $shift_time_in . ' + 05 minute');
            $newtimestamp = date('H:i:s', $newtimestamp);

            $query_absen = "SELECT presence_id,presence_date,time_in,time_out,picture_in,picture_out,present_id,latitude_longtitude_in,information,TIMEDIFF(TIME(time_in),'$shift_time_in') AS selisih,if (time_in>'$shift_time_in','Telat',if(time_in='00:00:00','Tidak Masuk','Tepat Waktu')) AS status,TIMEDIFF(TIME(time_out),'$shift_time_out') AS selisih_out FROM presence WHERE $filter ORDER BY presence_id DESC";
            $result_absen = $connection->query($query_absen);
            $row_absen = $result_absen->fetch_assoc();

            $querya = "SELECT present_id,present_name FROM present_status WHERE present_id='$row_absen[present_id]'";
            $resulta = $connection->query($querya);
            $rowa =  $resulta->fetch_assoc();

            if ($row_absen['time_in'] == NULL) {
              if (date("l", mktime(0, 0, 0, $bulan, $d, $tahun)) == "Sunday") {
                $status = 'Libur Akhir Pekan';
              } else {
                $status = '<span class="label label-danger">Tidak Hadir</span>';
              }
              $time_in = $row_absen['time_in'];
            } else {
              $status = $rowa['present_name'];
              $time_in = $row_absen['time_in'];
            }

            // Status Absensi Jam Masuk
            if ($row_absen['status'] == 'Telat') {
              $status_time_in = '<label class="label label-danger pull-right">' . $row_absen['status'] . '</label>';
              /*$waktu_kerja  = strtotime(''.$date_month_year.' '.$shift_time_in.'');
          $waktu_absen  = strtotime(''.$date_month_year.' '.$row_absen['time_in'].'');
          $diff    		= $waktu_absen - $waktu_kerja;
          $terlambat_jam	= floor($diff / (60 * 60));
          $terlambat_menit	= $diff - $terlambat_jam * (60 * 60);
          $terlamat 	= ''.$terlambat_jam.' jam '.floor($terlambat_menit/60).' menit';*/
            } elseif ($row_absen['status'] == 'Tepat Waktu') {
              $status_time_in = '<label class="label label-info pull-right">' . $row_absen['status'] . '</label>';
              $terlamat   = '';
            } else {
              $status_time_in = '';
              $terlamat   = '';
            }

            // DURASI KERJA  =========================================
            $durasi_kerja_start = strtotime('' . $date_month_year . ' ' . $row_absen['time_in'] . '');
            $durasi_kerja_end   = strtotime('' . $date_month_year . ' ' . $row_absen['time_out'] . '');
            $diff          = $durasi_kerja_end - $durasi_kerja_start;
            $durasi_jam       = floor($diff / (60 * 60));
            $durasi_menit     = $diff - ($durasi_jam * (60 * 60));
            $durasi_detik     = $diff % 60;
            $durasi_kerja     = '' . $durasi_jam . ' jam, ' . floor($durasi_menit / 60) . ' menit';

            // JAM LEMBUR =========================================
            if ($row_absen['time_out'] > $shift_time_out) {
              $lembur_kerja_start = strtotime('' . $date_month_year . ' ' . $shift_time_out . '');
              $lembur_kerja_end   = strtotime('' . $date_month_year . ' ' . $row_absen['time_out'] . '');
              $diff          = $lembur_kerja_end - $lembur_kerja_start;
              $lembur_jam       = floor($diff / (60 * 60));
              $lembur_menit     = $diff - ($lembur_jam * (60 * 60));
              $lembur       = '' . $lembur_jam . ' jam, ' . floor($lembur_menit / 60) . ' menit';
            } else {
              $lembur = '';
            }
            echo '
         <tr style="background:' . $background . ';color:' . $warna . '">
            <td class="text-center">' . $d . '</td>
            <td>' . format_hari_tanggal($date_month_year) . '</td>';
            if (date("l", mktime(0, 0, 0, $bulan, $d, $tahun)) == "Sunday") {
              if ($row_absen['time_in'] == '') {
                echo '
                <td class="text-center" colspan="3">Libur Akhir Pekan</td>';
              } else {
                echo '
                <td class="text-center">' . $row_absen['time_in'] . '</td>
                <td class="text-center">' . $row_absen['time_in'] . '</td>
              	<td class="text-center">Terlambat</td>';
              }
            } else {
              echo '
              <td class="text-center">' . $shift_time_in . '</td>
              <td class="text-center">' . $row_absen['time_in'] . '</td>
              <td class="text-center">' . $row_absen['selisih'] . '</td>';
            }

            if (date("l", mktime(0, 0, 0, $bulan, $d, $tahun)) == "Sunday") {
              if ($row_absen['time_out'] == '') {
                echo '
                <td class="text-center" colspan="3">Libur Akhir Pekan</td>';
              } else {
                echo '
                <td class="text-center">' . $row_shift['time_out'] . '</td>
              	<td class="text-center">' . $row_absen['time_out'] . '</td>
              	<td class="text-center">' . $row_absen['selisih_out'] . '</td>';
              }
            } else {
              echo '
              <td class="text-center">' . $row_shift['time_out'] . '</td>
              <td class="text-center">' . $row_absen['time_out'] . '</td>
              <td class="text-center">' . $row_absen['selisih_out'] . '</td>';
            }
            echo '
              <td>' . $durasi_kerja . '</td>
              <td>' . $lembur . '</td>
              <td>' . $status . ' ' . $status_time_in . '</td>
              <td>' . $row_absen['information'] . '</td>
          </tr>';
          }

          echo '<tbody>
      </table>';
          if (isset($_GET['from']) or isset($_GET['to'])) {
            $month = $_GET['from'];
            $year  = $_GET['to'];
            $filter = "employees_id='$id' AND MONTH(presence_date)='$month' AND year(presence_date)='$year' AND employees_id='$id'";
          } else {
            $filter = "employees_id='$id' AND MONTH(presence_date) ='$month' AND employees_id='$id'";
          }

          $query_hadir = "SELECT presence_id FROM presence WHERE $filter AND present_id='1' ORDER BY presence_id DESC";
          $hadir = $connection->query($query_hadir);

          $query_sakit = "SELECT presence_id FROM presence WHERE $filter AND present_id='2' ORDER BY presence_id";
          $sakit = $connection->query($query_sakit);

          $query_izin = "SELECT presence_id FROM presence WHERE $filter AND present_id='3' ORDER BY presence_id";
          $izin = $connection->query($query_izin);

          $query_telat = "SELECT presence_id FROM presence WHERE $filter AND time_in>'$shift_time_in'";
          $telat = $connection->query($query_telat);

          echo '<p>Hadir : <span class="label label-success">' . $hadir->num_rows . '</span></p>
          <p>Telat : <span class="label label-danger">' . $telat->num_rows . '</span></p>
          <p>Sakit : <span class="label label-warning">' . $sakit->num_rows . '</span></p>
          <p>Izin : <span class="label label-info">' . $izin->num_rows . '</span></p>

        </div>
      </div>
    </section>
</body>
</html>';
        } else {
          echo '<center><h3>Data Tidak Ditemukan</h3></center>';
        }
      } else {
        echo 'Data tidak boleh ada yang kosong!';
      }

      break;
      /* -------  CETAK ALL Karyawan PDF-----------------------------------------------*/
    case 'allpdf':
      $query = "SELECT employees.id,employees.employees_name,employees.position_id,position.position_name,shift.time_in,shift.time_out FROM employees,position,shift WHERE employees.position_id=position.position_id AND employees.shift_id=shift.shift_id ORDER BY employees.id DESC";
      $result = $connection->query($query);
      if ($result->num_rows > 0) {

        $mpdf = new \Mpdf\Mpdf();
        ob_start();
        /*$mpdf->SetHTMLFooter('
      <table width="100%" style="border-top:solid 1px #333;font-size:11px;">
          <tr>
              <td width="60%" style="text-align:left;">Simpanlah lembar Absensi ini.</td>
              <td width="35%" style="text-align: right;">Dicetak tanggal '.tgl_indo($date).'</td>
          </tr>
      </table>');*/
        echo '<!DOCTYPE html>
<html>
<head>
    <title>Cetak Data Absensi</title>
    <style>
    body{font-family:Arial,Helvetica,sans-serif}.container_box{position:relative}.container_box .row h3{line-height:25px;font-size:20px;margin:0px 0 10px;text-transform:uppercase}.container_box .text-center{text-align:center}.container_box .content_box{position:relative}.container_box .content_box .des_info{margin:20px 0;text-align:right}.container_box h3{font-size:30px}table.customTable{width:100%;background-color:#fff;border-collapse:collapse;border-width:1px;border-color:#b3b3b3;border-style:solid;color:#000}table.customTable td,table.customTable th{border-width:1px;border-color:#b3b3b3;border-style:solid;padding:5px;text-align:left}table.customTable thead{background-color:#f6f3f8}.text-center{text-align:center}
    .label {display: inline;padding: .2em .6em .3em;font-size: 75%;font-weight: 700;line-height: 1;color: #fff;text-align: center;white-space: nowrap; vertical-align: baseline;border-radius: .25em;}
    .label-success{background-color: #00a65a !important;}.label-warning {background-color: #f0ad4e;}.label-info {background-color: #5bc0de;}.label-danger{background-color: #dd4b39 !important;}
    p{line-height:20px;padding:0px;margin: 5px;}.pull-right{float:right}
    </style>
</head>
<body>';
        while ($row = $result->fetch_assoc()) {
          $employees_name = $row['employees_name'];
          $id             = $row['id'];

          if (isset($_GET['from']) or isset($_GET['to'])) {
            $bulan   = date($_GET['from']);
          } else {
            $bulan  = date("m");
          }
          $hari       = date("d");
          $tahun      = date("Y");
          $jumlahhari = date("t", mktime(0, 0, 0, $bulan, $hari, $tahun));
          $s          = date("w", mktime(0, 0, 0, $bulan, 1, $tahun));


          $shift_time_in  = $row['time_in'];
          $newtimestamp   = strtotime('' . $shift_time_in . ' + 05 minute');
          $newtimestamp   = date('H:i:s', $newtimestamp);
          echo '
    <section class="container_box">
      <div class="row">';
          if (isset($_GET['from']) or isset($_GET['to'])) {
            echo '<h3>DATA ABSENSI BULAN ' . tanggal_indo($_GET['from']) . ' - ' . $_GET['to'] . '</h3>';
          } else {
            echo '<h3>DATA ABSENSI BULAN ' . tanggal_indo($month) . ' - ' . $year . '</h3>';
          }
          echo '
        <p>Nama   : ' . $row['employees_name'] . '</p>
        <p>Jabatan : ' . $row['position_name'] . '</p><br>
      <div class="content_box">
        <table class="customTable">
          <thead>
            <tr>
              <th class="text-center">No.</th>
              <th>Tanggal</th>
              <th>Waktu Masuk</th>
              <th>Waktu Pulang</th>
              <th>Status</th>
              <th>Keterangan</th>
            </tr>
          </thead>
        <tbody>';
          for ($d = 1; $d <= $jumlahhari; $d++) {
            $warna      = '';
            $background = '';
            $status     = 'Tidak Hadir';
            if (date("l", mktime(0, 0, 0, $bulan, $d, $tahun)) == "Sunday") {
              $warna = 'white';
              $background = '#FF0000';
              $status = 'Libur Akhir Pekan';
            }
            $date_month_year = '' . $year . '-' . $bulan . '-' . $d . '';

            if (isset($_GET['from']) or isset($_GET['to'])) {
              $month = $_GET['from'];
              $year  = $_GET['to'];
              $filter = "employees_id='$id' AND presence_date='$date_month_year' AND MONTH(presence_date)='$month' AND year(presence_date)='$year'";
            } else {
              $filter = "employees_id='$id' AND presence_date='$date_month_year' AND MONTH(presence_date) ='$month'";
            }


            $query_absen = "SELECT presence_id,presence_date,time_in,time_out,picture_in,picture_out,present_id,latitude_longtitude_in,information,TIMEDIFF(TIME(time_in),'$shift_time_in') AS selisih,if (time_in>'$shift_time_in','Telat',if(time_in='00:00:00','Tidak Masuk','Tepat Waktu')) AS status FROM presence WHERE $filter";
            $result_absen = $connection->query($query_absen);
            $row_absen = $result_absen->fetch_assoc();

            $querya = "SELECT present_id,present_name FROM present_status WHERE present_id='$row_absen[present_id]'";
            $resulta = $connection->query($querya);
            $rowa =  $resulta->fetch_assoc();

            if ($row_absen['time_in'] == NULL) {
              if (date("l", mktime(0, 0, 0, $bulan, $d, $tahun)) == "Sunday") {
                $status = 'Libur Akhir Pekan';
              } else {
                $status = '<span class="label label-danger">Tidak Hadir</span>';
              }
              $time_in = $row_absen['time_in'];
            } else {
              $status = $rowa['present_name'];
              $time_in = $row_absen['time_in'];
            }


            if ($row_absen['status'] == 'Telat') {
              $status_time_in = '<label class="label label-danger pull-right">' . $row_absen['status'] . '</label>';
            } elseif ($row_absen['status'] == 'Tepat Waktu') {
              $status_time_in = '<label class="label label-info pull-right">' . $row_absen['status'] . '</label>';
            } else {
              $status_time_in = '';
            }


            echo '
         <tr style="background:' . $background . ';color:' . $warna . '">
            <td class="text-center">' . $d . '</td>
            <td>' . format_hari_tanggal($date_month_year) . '</td>';
            if (date("l", mktime(0, 0, 0, $bulan, $d, $tahun)) == "Sunday") {
              if ($row_absen['time_in'] == '') {
                echo '
                <td class="text-center">Libur Akhir Pekan</td>';
              } else {
                echo '
                <td>' . $row_absen['time_in'] . '</td>';
              }
            } else {
              echo '
              <td>' . $row_absen['time_in'] . '</td>';
            }

            if (date("l", mktime(0, 0, 0, $bulan, $d, $tahun)) == "Sunday") {
              if ($row_absen['time_out'] == '') {
                echo '
                <td class="text-center">Libur Akhir Pekan</td>';
              } else {
                echo '
                  <td>' . $row_absen['time_out'] . '</td>';
              }
            } else {
              echo '
              <td>' . $row_absen['time_out'] . '</td>';
            }
            echo '
              <td>' . $status . ' ' . $status_time_in . '</td>
              <td>' . $row_absen['information'] . '</td>
          </tr>';
          }

          echo '<tbody>
      </table>';
          if (isset($_GET['from']) or isset($_GET['to'])) {
            $month = $_GET['from'];
            $year  = $_GET['to'];
            $filter = "employees_id='$id' AND MONTH(presence_date)='$month' AND year(presence_date)='$year' AND employees_id='$id'";
          } else {
            $filter = "employees_id='$id' AND MONTH(presence_date) ='$month' AND employees_id='$id'";
          }

          $query_hadir = "SELECT presence_id FROM presence WHERE $filter AND present_id='1' ORDER BY presence_id DESC";
          $hadir = $connection->query($query_hadir);

          $query_sakit = "SELECT presence_id FROM presence WHERE $filter AND present_id='2' ORDER BY presence_id";
          $sakit = $connection->query($query_sakit);

          $query_izin = "SELECT presence_id FROM presence WHERE $filter AND present_id='3' ORDER BY presence_id";
          $izin = $connection->query($query_izin);

          $query_telat = "SELECT presence_id FROM presence WHERE $filter AND time_in>'$shift_time_in'";
          $telat = $connection->query($query_telat);

          echo '<p>Hadir : <span class="label label-success">' . $hadir->num_rows . '</span></p>
          <p>Telat : <span class="label label-danger">' . $telat->num_rows . '</span></p>
          <p>Sakit : <span class="label label-warning">' . $sakit->num_rows . '</span></p>
          <p>Izin : <span class="label label-info">' . $izin->num_rows . '</span></p>

      </div>
    </div>
  </section>';
        }
        echo '
</body>
</html>';
        $html = ob_get_contents();
        ob_end_clean();
        $mpdf->WriteHTML(utf8_encode($html));
        $mpdf->Output("Absensi-All-$date.pdf", 'I');
      } else {
        echo '<center><h3>Data Tidak Ditemukan</h3></center>';
      }
      break;


      /* -------  CETAK ALL EXCEL-----------------------------------------------*/
    case 'allexcel':
      $query = "SELECT employees.id,employees.employees_name,employees.position_id,position.position_name,shift.time_in,shift.time_out FROM employees,position,shift WHERE employees.position_id=position.position_id AND employees.shift_id=shift.shift_id ORDER BY employees.id DESC";
      $result = $connection->query($query);
      if ($result->num_rows > 0) {

        if (empty($_GET['print'])) {
          header("Content-type: application/vnd-ms-excel");
          header("Content-Disposition: attachment; filename=Data-Absensi-$date.xls");
        } else {
          echo '<script>
          window.print();
          </script>';
        }

        echo '<!DOCTYPE html>
<html>
<head>
    <title>Cetak Data Absensi ' . $employees_name . '</title>
    <style>
    body{font-family:Arial,Helvetica,sans-serif}.container_box{position:relative}.container_box .row h3{line-height:25px;font-size:20px;margin:0px 0 10px;text-transform:uppercase}.container_box .text-center{text-align:center}.container_box .content_box{position:relative}.container_box .content_box .des_info{margin:20px 0;text-align:right}.container_box h3{font-size:30px}table.customTable{width:100%;background-color:#fff;border-collapse:collapse;border-width:1px;border-color:#b3b3b3;border-style:solid;color:#000}table.customTable td,table.customTable th{border-width:1px;border-color:#b3b3b3;border-style:solid;padding:5px;text-align:left}table.customTable thead{background-color:#f6f3f8}.text-center{text-align:center}
    .label {display: inline;padding: .2em .6em .3em;font-size: 75%;font-weight: 700;line-height: 1;color: #fff;text-align: center;white-space: nowrap; vertical-align: baseline;border-radius: .25em;}
    .label-success{background-color: #00a65a !important;}.label-warning {background-color: #f0ad4e;}.label-info {background-color: #5bc0de;}.label-danger{background-color: #dd4b39 !important;}
    p{line-height:20px;padding:0px;margin: 5px;}.pull-right{float:right}
    </style>
</head>
<body>

';
$list_month=["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
echo '
    <section class="container_box">
      <div class="row">';
          echo '<h3 style="text-align:center;">REKAPITULASI PRESENSI GURU DAN KARYAWAN SMK N 1 KALIWUNGU</h3>'; 
          if (isset($_GET['from']) or isset($_GET['to'])) {

            echo '<h3 style="text-align:center;">BULAN ' . $list_month[$_GET['from']-1] . ' TAHUN ' . $_GET['to'] . '</h3>';
          } else {
            echo '<h3 style="text-align:center;">BULAN ' . tanggal_indo($month) . ' TAHUN ' . $year . '</h3>';
          }
          echo '
      <div class="content_box">
        <table class="customTable">
          <thead>
            <tr>
              <th class="text-center">No.</th>
              <th class="text-center">Nama</th>
              <th>KWK(menit)</th>
              <th class="text-center">Hadir</th>
              <th class="text-center">WFH</th>
              <th>Alpha</th>
              <th class="text-center">Ijin</th>
              <th class="text-center">Sakit</th>
              <th class="text-center">Dinas Luar (DL)</th>
              <th>Diklat (Dk)</th>
              <th>Cuti Alan Penting (CAR)</th>
              <th>Tidak Absen Masuk (TAM)</th>
              <th>Tidak Absen Pulang (TAP)</th>
              <th>Terlambat</th>
              <th>Pulang Cepat</th>
              <th>Libur Hari Besar (HR)</th>
              <th>Libur</th>
            </tr>
          </thead>
        <tbody>';
        $no=0;
        while ($row = $result->fetch_assoc()) {
          $no++;
          $employees_name = $row['employees_name'];
          $id             = $row['id'];

          if (isset($_GET['from']) or isset($_GET['to'])) {
            $bulan   = date($_GET['from']);
          } else {
            $bulan  = date("m");
          }
          $hari       = date("d");
          $tahun      = date("Y");
          $jumlahhari = date("t", mktime(0, 0, 0, $bulan, $hari, $tahun));
          $s          = date("w", mktime(0, 0, 0, $bulan, 1, $tahun));
          $mpdf = new \Mpdf\Mpdf();
          ob_start();

          $shift_time_in  = $row['time_in'];
          $newtimestamp   = strtotime('' . $shift_time_in . ' + 05 minute');
          $newtimestamp   = date('H:i:s', $newtimestamp);
          if (isset($_GET['from']) or isset($_GET['to'])) {
            $month = $_GET['from'];
            $year  = $_GET['to'];
            $filter = "employees_id='$id' AND MONTH(presence_date)='$month' AND year(presence_date)='$year' AND employees_id='$id'";
          } else {
            $filter = "employees_id='$id' AND MONTH(presence_date) ='$month' AND employees_id='$id'";
          }

          $query_hadir = "SELECT presence_id FROM presence WHERE $filter AND present_id='1' ORDER BY presence_id DESC";
          $hadir = $connection->query($query_hadir);

          $query_sakit = "SELECT presence_id FROM presence WHERE $filter AND present_id='2' ORDER BY presence_id";
          $sakit = $connection->query($query_sakit);

          $query_izin = "SELECT presence_id FROM presence WHERE $filter AND present_id='3' ORDER BY presence_id";
          $izin = $connection->query($query_izin);

          $query_telat = "SELECT presence_id FROM presence WHERE $filter AND time_in>'$shift_time_in'";
          $telat = $connection->query($query_telat);
          $kwk=1;
          $wfh=1;
          $alpha=$jumlahhari - $hadir->num_rows;
          $dinas_luar=1;
          $diklat=1;
          $cuti_alasan_penting=1;
          
          $query_pulang_cepat = "SELECT presence_id FROM presence WHERE $filter AND time_in=NULL AND time_out NOT NULL";
          $tidak_absen_masuk=1;
          
          $query_pulang_cepat = "SELECT presence_id FROM presence WHERE $filter AND time_in>'$shift_time_out'";
          $tidak_absen_keluar=1;

          $query_pulang_cepat = "SELECT presence_id FROM presence WHERE $filter AND time_out<'$shift_time_out'";
          
          $pulang_cepat=$connection->query($query_pulang_cepat)->num_rows;
          
          $libur_hari_besar=1;
          
          $libur=1;
          
          // echo '<p>Hadir : <span class="label label-success">' . $hadir->num_rows . '</span></p>
          // <p>Telat : <span class="label label-danger">' . $telat->num_rows . '</span></p>
          // <p>Sakit : <span class="label label-warning">' . $sakit->num_rows . '</span></p>
          // <p>Izin : <span class="label label-info">' . $izin->num_rows . '</span></p>
          // start tbody
          echo '
            <tr>
              <td class="text-center">'.$no.'</td>
              <td nowrap>'.$employees_name.'</td>
              <td class="text-center">'.$kwk.'</td>
              <td class="text-center">'.$hadir->num_rows.'</td>
              <td class="text-center">'.$wfh.'</td>
              <td class="text-center">'.$alpha.'</td>
              <td class="text-center">'.$izin->num_rows.'</td>
              <td class="text-center">'.$sakit->num_rows.'</td>
              <td class="text-center">'.$dinas_luar.'</td>
              <td class="text-center">'.$diklat.'</td>
              <td class="text-center">'.$cuti_alasan_penting.'</td>
              <td class="text-center">'.$tidak_absen_masuk.'</td>
              <td class="text-center">'.$tidak_absen_keluar.'</td>
              <td class="text-center">'.$telat->num_rows.'</td>
              <td class="text-center">'.$pulang_cepat.'</td>
              <td class="text-center">'.$libur_hari_besar.'</td>
              <td class="text-center">'.$libur.'</td>
            </tr>
          ';

          
        }
        echo '<tbody>
      </table>
         

      </div>
    </div>
  </section>';
        echo '
</body>
</html>';
      } else {
        echo '<center><h3>Data Tidak Ditemukan</h3></center>';
      }

      break;
  }
}

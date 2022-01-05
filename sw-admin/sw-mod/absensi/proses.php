<?php session_start();
error_reporting(0);

// function calculate distance
function getDistanceBetweenPoints($lat1, $lon1, $lat2, $lon2)
{
  $theta = $lon1 - $lon2;
  $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
  $miles = acos($miles);
  $miles = rad2deg($miles);
  $miles = $miles * 60 * 1.1515;
  $feet = $miles * 5280;
  $yards = $feet / 3;
  $kilometers = $miles * 1.609;
  $meters = $kilometers * 1000;
  return compact('miles', 'feet', 'yards', 'kilometers', 'meters');
}

function rad($x)
{
  return $x * M_PI / 180;
}
function distHaversine($coord_a, $coord_b)
{
  # jarak kilometer dimensi (mean radius) bumi
  $R = 6371;
  $coord_a = explode(",", $coord_a);
  $coord_b = explode(",", $coord_b);
  $dLat = rad(($coord_b[0]) - ($coord_a[0]));
  $dLong = rad($coord_b[1] - $coord_a[1]);
  $a = sin($dLat / 2) * sin($dLat / 2) + cos(rad($coord_a[0])) * cos(rad($coord_b[0])) * sin($dLong / 2) * sin($dLong / 2);
  $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
  $d = $R * $c;
  # hasil akhir dalam satuan kilometer
  return number_format($d, 0, '.', ',');
}
// end function calculate distance


if (empty($_SESSION['SESSION_USER']) && empty($_SESSION['SESSION_ID'])) {
  header('location:../../login/');
  exit;
} else {
  require_once '../../../sw-library/sw-config.php';
  require_once '../../login/login_session.php';
  include('../../../sw-library/sw-function.php');

  switch (@$_GET['action']) {
      /* -------  LOAD DATA ABSENSI----------*/
    case 'absensi':
      $error = array();

      if (empty($_GET['id'])) {
        $error[] = 'ID tidak boleh kosong';
      } else {
        $id = mysqli_real_escape_string($connection, $_GET['id']);
      }

      if (isset($_POST['month']) or isset($_POST['year'])) {
        $bulan   = date($_POST['month']);
      } else {
        $bulan  = date("m");
      }

      $hari       = date("d");
      //$bulan      = date ("m");
      $tahun      = date("Y");
      $jumlahhari = date("t", mktime(0, 0, 0, $bulan, $hari, $tahun));
      $s          = date("w", mktime(0, 0, 0, $bulan, 1, $tahun));
      if (empty($error)) {
        echo '
<div class="table-responsive">
<table class="table table-bordered table-hover" id="swdatatable">
        <thead>
            <tr>
                <th class="align-middle" width="20">No</th>
                <th class="align-middle">Tanggal</th>
                <th class="align-middle text-center"><i class="fa fa-picture-o" aria-hidden="true"></i></th>
                <th class="align-middle text-center">Scan Masuk</th>
                <th class="align-middle text-center">Terlambat</th>
                <th class="align-middle text-center"><i class="fa fa-picture-o" aria-hidden="true"></i></th>
                <th class="align-middle text-center">Scan Pulang</th>
                <th class="align-middle text-center">Pulang Cepat</th>
                <th class="align-middle">Status</th>
                <th class="align-middle">Jarak (KM)</th>
                <th class="align-middle text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>';
        for ($d = 1; $d <= $jumlahhari; $d++) {
          $warna      = '';
          $background = '';
          $status_hadir     = 'Tidak Hadir';
          if (date("l", mktime(0, 0, 0, $bulan, $d, $tahun)) == "Sunday" || date("l", mktime(0, 0, 0, $bulan, $d, $tahun)) == "Saturday") {
            $warna = '#ffffff';
            $background = '#FF0000';
            $status_hadir = 'Libur Akhir Pekan';
          }
          $date_month_year = '' . $year . '-' . $bulan . '-' . $d . '';

          if (isset($_POST['month']) or isset($_POST['year'])) {
            $month = $_POST['month'];
            $year  = $_POST['year'];
            $filter = "employees_id='$id' AND presence_date='$date_month_year' AND MONTH(presence_date)='$month' AND year(presence_date)='$year' AND employees_id='$id'";
          } else {
            $filter = "employees_id='$id' AND presence_date='$date_month_year' AND MONTH(presence_date) ='$month' AND employees_id='$id'";
          }

          $query = "SELECT employees.id,shift.shift_id,shift.time_in,shift.time_out FROM employees,shift WHERE employees.shift_id=shift.shift_id AND employees.id='$id'";
          $result = $connection->query($query);
          $row    = $result->fetch_assoc();


          // get lokasi kantor 

          $query_get_kantor = "SELECT lat_building, long_building FROM building WHERE building_id = '6'";
          $result_lokasi_kantor = $connection->query($query_get_kantor);
          $row_lokasi_kantor = $result_lokasi_kantor->fetch_assoc();
          $lat_buliding = $row_lokasi_kantor['lat_building'];
          $long_building = $row_lokasi_kantor['long_building'];



          // end get lokasi kantor


          $query_shift = "SELECT time_in,time_out FROM shift WHERE shift_id='$row[shift_id]'";
          $result_shift = $connection->query($query_shift);
          $row_shift = $result_shift->fetch_assoc();
          $shift_time_in = $row_shift['time_in'];
          $shift_time_out = $row_shift['time_out'];
          $newtimestamp = strtotime('' . $shift_time_in . ' + 05 minute');
          $newtimestamp = date('H:i:s', $newtimestamp);

          $query_absen = "SELECT presence_id,presence_date,time_in,time_out,picture_in,picture_out,present_id, latitude_longtitude_in,latitude_longtitude_out,information,TIMEDIFF(TIME(time_in),'$shift_time_in') AS selisih,if (time_in>'$shift_time_in','Telat',if(time_in='00:00:00','Tidak Masuk','Tepat Waktu')) AS status, TIMEDIFF(TIME(time_out),'$shift_time_out') AS selisih_out FROM presence WHERE $filter ORDER BY presence_id DESC";
          $result_absen = $connection->query($query_absen);
          $row_absen = $result_absen->fetch_assoc();
          $lat_long_presence_outs = $row_absen['latitude_longtitude_out'];

          // Status Kehadiran
          $querya = "SELECT present_id,present_name FROM present_status WHERE present_id='$row_absen[present_id]'";
          $resulta = $connection->query($querya);
          $rowa =  $resulta->fetch_assoc();
          // Status Kehadiran
          if ($row_absen['time_in'] == NULL) {
            if (date("l", mktime(0, 0, 0, $bulan, $d, $tahun)) == "Sunday" || date("l", mktime(0, 0, 0, $bulan, $d, $tahun)) == "Saturday") {
              $status_hadir = 'Libur Akhir Pekan';
            } else {
              $status_hadir = '<span class="label label-danger">Tidak Hadir</span>';
            }
            $time_in = $row_absen['time_in'];
          } else {
            $status_hadir = '<label class="label label-warning">' . $rowa['present_name'] . '</label>';
            $time_in = $row_absen['time_in'];
          }

          // Status Absensi Jam Masuk
          if ($row_absen['status'] == 'Telat') {
            $status_time_in = '<label class="label label-danger">Terlambat</label>';
          } elseif ($row_absen['status'] == 'Tepat Waktu') {
            $status_time_in = '<label class="label label-info">' . $row_absen['status'] . '</label>';
          } else {
            $status_time_in = '<label class="label label-danger">' . $row_absen['status'] . '</label>';
          }



          if ($row_absen['time_out'] > $shift_time_out) {
            $selisih_out = '';
          } else {
            $selisih_out = $row_absen['selisih_out'];
          }
          list($latitude,  $longitude) = explode(',', $row_absen['latitude_longtitude_in']);
          list($latitude_out,  $longitude_out) = explode(',', $row_absen['latitude_longtitude_out']);

          // calculating a distance


          $pointOffice = array("lat" => -6.9956675, "long" => 109.1257462);
          // -6.9952841,109.1105971 // kantor bupati tegal
          // -6.9956675,109.1257462

          // smk 1 kaliwungu
          // lokasi -7.463889, 110.615290
          // $officeLatLong = array("lat" => $lat_buliding, "long" => $long_building);
          $officeLatLong =  "$lat_buliding, $long_building";

          $distance_in  = getDistanceBetweenPoints($pointOffice['lat'], $pointOffice['long'], $latitude, $longitude);
          $distance_out = getDistanceBetweenPoints($pointOffice['lat'], $pointOffice['long'], $latitude_out, $longitude_out);

          $distance_in2 = distHaversine($row_absen['latitude_longtitude_in'], '-6.9956675, 109.1257462');
          $distance_out2 = distHaversine('-6.894316, 109.215563', '-7.463889, 110.615290');



          if ($lat_long_presence_outs == "") {
            $resultDistance_out2 = 'Absen keluar dulu';
          } else {
            $resultDistance_out2 = distHaversine("$lat_long_presence_outs", $officeLatLong);
          }
          // var_dump($resultDistance_out2);
          // die;


          // calculating a distance
          echo '
        <tr style="background:' . $background . ';color:' . $warna . '">
          <td class="text-center">' . $d . '</td>
          <td>' . format_hari_tanggal($date_month_year) . '</td>
          <td class="text-center picture">';
          if ($row_absen['picture_in'] == NULL) {
            echo '<img src="../timthumb?src=' . $site_url . '/sw-content/avatar.jpg&h=40&w=40">';
          } else {
            echo '<a class="image-link" href="' . $site_url . '/sw-content/absent/' . $row_absen['picture_in'] . '">
              <img src="../timthumb?src=' . $site_url . '/sw-content/absent/' . $row_absen['picture_in'] . '&h=40&w=40"></a>';
          }
          echo '
          </td>
          <td class="text-center">' . $row_absen['time_in'] . ' </td>
          <td class="text-center">' . $row_absen['selisih'] . '</td>
          <td class="text-center picture">';
          if ($row_absen['picture_out'] == NULL) {
            echo '<img src="../timthumb?src=' . $site_url . '/sw-content/avatar.jpg&h=40&w=40">';
          } else {
            echo '<a class="image-link" href="' . $site_url . '/sw-content/absent/' . $row_absen['picture_out'] . '">
                      <img src="../timthumb?src=' . $site_url . '/sw-content/absent/' . $row_absen['picture_out'] . '&h=40&w=40"></a>';
          }
          echo '</td>
          <td class="text-center">' . $row_absen['time_out'] . '</td>
          <td class="text-center">' . $selisih_out . '</td>
          <td>' . $status_hadir . '<br>' . $row_absen['information'] . '</td>
          <td class="text-center">' . $resultDistance_out2 . '</td>
          <td class="text-right">
              <button type="button" class="btn btn-warning btn-xs btn-modal enable-tooltip" title="Lokasi" data-latitude="' . $latitude . '" data-longitude="' . $longitude . '"><i class="fa fa-map-marker"></i> Masuk</button>
              <button type="button" class="btn btn-warning btn-xs btn-modal enable-tooltip" title="Lokasi" data-latitude="' . $latitude_out . '" data-longitude="' . $longitude_out . '"><i class="fa fa-map-marker"></i> Pulang</button></td>
          </tr>';
        }
        echo '
        </tbody>
      </table>
  </div>';
        if (isset($_POST['month']) or isset($_POST['year'])) {
          $month = $_POST['month'];
          $year  = $_POST['year'];
          $filter = "employees_id='$id' AND MONTH(presence_date)='$month' AND year(presence_date)='$year' AND employees_id='$id'";
        } else {
          $filter = "employees_id='$id' AND MONTH(presence_date) ='$month' and employees_id='$id'";
        }

        $query_hadir = "SELECT presence_id FROM presence WHERE $filter AND present_id='1' ORDER BY presence_id DESC";
        $hadir = $connection->query($query_hadir);

        $query_sakit = "SELECT presence_id FROM presence WHERE $filter AND present_id='2' ORDER BY presence_id";
        $sakit = $connection->query($query_sakit);

        $query_izin = "SELECT presence_id FROM presence WHERE $filter AND present_id='3' ORDER BY presence_id";
        $izin = $connection->query($query_izin);


        $query_telat = "SELECT presence_id FROM presence WHERE $filter AND time_in>'$shift_time_in'";
        $telat = $connection->query($query_telat);

        echo '<hr>
      <div class="row">
        <div class="col-md-3">
          <p>Hadir : <span class="label label-success">' . $hadir->num_rows . '</span></p>
        </div>

        <div class="col-md-3">
          <p>Terlambat : <span class="label label-danger">' . $telat->num_rows . '</span></p>
        </div>

        <div class="col-md-3">
          <p>Sakit : <span class="label label-warning">' . $sakit->num_rows . '</span></p>
        </div>

        <div class="col-md-3">
          <p>Izin : <span class="label label-info">' . $izin->num_rows . '</span></p>
        </div>

      </div>';
        echo '
<script>
  $("#swdatatable").dataTable({
      "iDisplayLength":35,
      "aLengthMenu": [[35, 40, 50, -1], [35, 40, 50, "All"]]
  });
 $(".image-link").magnificPopup({type:"image"});
</script>'; ?>
        <script type="text/javascript">
          $(function() {
            $('[data-toggle="tooltip"]').tooltip()
          })
        </script>
<?php
      } else {
        echo 'Data tidak ditemukan';
      }

      break;
  }
}

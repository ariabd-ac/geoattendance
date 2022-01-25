<?php 

if ($mod ==''){
    header('location:../404');
    echo'kosong';
}else{
    include_once 'sw-mod/sw-header.php';
if(!isset($_COOKIE['COOKIES_MEMBER']) && !isset($_COOKIE['COOKIES_COOKIES'])){
        setcookie('COOKIES_MEMBER', '', 0, '/');
        setcookie('COOKIES_COOKIES', '', 0, '/');
        // Login tidak ditemukan
        setcookie("COOKIES_MEMBER", "", time()-$expired_cookie);
        setcookie("COOKIES_COOKIES", "", time()-$expired_cookie);
        session_destroy();
        header("location:./"); 
}else{

  echo'<!-- App Capsule -->
    <div id="appCapsule">
        <!-- Wallet Card -->
        <div class="section wallet-card-section pt-1">
            <div class="wallet-card">
                <div class="balance">
                    <div class="left">
                        <span class="title"> Selamat '.$salam.'</span>
                        <h4>'.ucfirst($row_user['employees_name']).'</h4>
                    </div>
                    <div class="right">
                        <span class="title">'.tgl_ind($date).' </span>
                        <h4><span class="clock"></span></h4>
                    </div>

                </div>
                <!-- * Balance -->
                <div class="text-center">
                <!--<h3>'.tgl_ind($date).' - <span class="clock"></span></h3>-->
                <p>Lat-Long: <span class="latitude" id="latitude"></span></p>
                </div>
                <span id="shiftid" style="display:none;">'.$row_user['shift_id'].'</span>
                <span id="distance"></span>
                <div class="wallet-footer text-center">
                    <div class="webcam-capture-body text-center">
                        <div class="webcam-capture"></div>
                        <div class="form-group basic">
                            ';
                            if($result_absent->num_rows > 0){
                                echo'
                                <button class="btn btn-success btn-lg btn-block btn-action-absent" onClick="captureimage(0,2)"><ion-icon name="camera-outline"></ion-icon>Absen Pulang</button>';
                            }else{
                                echo'
                                <button class="btn btn-success btn-lg btn-block btn-action-absent" onClick="captureimage(0,1)"><ion-icon name="camera-outline"></ion-icon>Absen Masuk</button>
                                <button class="btn btn-success btn-lg btn-block btn-action-absent" onClick="captureimage(0,2)"><ion-icon name="camera-outline"></ion-icon>Absen Pulang</button>';
                            }
                        echo'
                        </div>';
                echo'
                    </div>
                </div>
                <!-- * Wallet Footer -->
            </div>
        </div>
        <!-- Card -->
    </div>
    <!-- * App Capsule -->
';

  }
  include_once 'sw-mod/sw-footer.php';
}

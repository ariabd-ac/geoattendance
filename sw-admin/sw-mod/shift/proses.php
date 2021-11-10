<?php
session_start();
if(empty($_SESSION['SESSION_USER']) && empty($_SESSION['SESSION_ID'])){
    header('location:../../login/');
 exit;}
else {
require_once'../../../sw-library/sw-config.php';
require_once'../../login/login_session.php';
include('../../../sw-library/sw-function.php');

switch (@$_GET['action']){

case 'add':
  $error = array();
  
  if (empty($_POST['shift_name'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $shift_name= mysqli_real_escape_string($connection, $_POST['shift_name']);
  }

  if (empty($_POST['time_in'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $time_in= mysqli_real_escape_string($connection, $_POST['time_in']);
  }


  if (empty($_POST['time_out'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $time_out = mysqli_real_escape_string($connection, $_POST['time_out']);
  }

  if (empty($error)) { 
    $add ="INSERT INTO  shift (shift_name,time_in,time_out) values('$shift_name','$time_in','$time_out')"; 
    if($connection->query($add) === false) { 
        die($connection->error.__LINE__); 
        echo'Data tidak berhasil disimpan!';
    } else{
        echo'success';
    }}
    else{           
        echo'Bidang inputan masih ada yang kosong..!';
    }
break;

/* ------------------------------
    Update
---------------------------------*/
case 'update':
 $error = array();
   if (empty($_POST['id'])) {
      $error[] = 'ID tidak boleh kosong';
    } else {
      $id = mysqli_real_escape_string($connection, $_POST['id']);
  }

  if (empty($_POST['shift_name'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $shift_name= mysqli_real_escape_string($connection, $_POST['shift_name']);
  }

  if (empty($_POST['time_in'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $time_in= mysqli_real_escape_string($connection, $_POST['time_in']);
  }


  if (empty($_POST['time_out'])) {
      $error[] = 'tidak boleh kosong';
    } else {
      $time_out = mysqli_real_escape_string($connection, $_POST['time_out']);
  }

  if (empty($error)) { 
    $update="UPDATE shift SET shift_name='$shift_name',
            time_in='$time_in',
            time_out='$time_out' WHERE shift_id='$id'"; 
    if($connection->query($update) === false) { 
        die($connection->error.__LINE__); 
        echo'Data tidak berhasil disimpan!';
    } else{
        echo'success';
    }}
    else{           
        echo'Bidang inputan tidak boleh ada yang kosong..!';
    }

break;
/* --------------- Delete ------------*/
case 'delete':
  $id       = mysqli_real_escape_string($connection,epm_decode($_POST['id']));
  $query ="SELECT shift.shift_id,employees.shift_id FROM shift,employees WHERE shift.shift_id=employees.shift_id AND employees.shift_id='$id'";
  $result = $connection->query($query);
  if(!$result->num_rows > 0){
     $deleted  = "DELETE FROM shift WHERE shift_id='$id'";
        if($connection->query($deleted) === true) {
            echo'success';
          } else { 
            //tidak berhasil
            echo'Data tidak berhasil dihapus.!';
            die($connection->error.__LINE__);
    }
  }else{
      echo'Lokasi digunakan, Data tidak dapat dihapus.!';
  }


break;
// get data karyawan
case 'getdatakaryawan':
  $idShift=$_GET['idshift'];
  $query="SELECT * FROM employees";
  $result = $connection->query($query);
  echo '<table class="table rounded" id="swdatatable">
    <thead>
        <tr>
            <th scope="col" class="align-middle text-center" width="10">No</th>
            <th scope="col" class="align-middle">Employee Name</th>
            <th scope="col" class="align-middle">Aksi</th>
        </tr>
    </thead>
    <tbody>';
  if($result->num_rows > 0){
    $i=1;
    while($row = $result->fetch_assoc()){
      echo '<tr>';
      echo      '<td>'.$i++.'</td>';
      echo      '<td>'.$row['employees_name'].'</td>';
      if($row['shift_id'] == $idShift){
        echo      '<td><input type="checkbox" class="check-shift-karyawan" data-employeeid="'.$row['id'].'" data-shiftid="'.$idShift.'" name="checkname" checked></td>';
      }else{
        echo      '<td><input type="checkbox" class="check-shift-karyawan" data-employeeid="'.$row['id'].'" data-shiftid="'.$idShift.'" name="checkname"></td>';
      }
      echo  '</tr>';
    }
  }else{
    echo "GAGAL".$connection->error.__LINE__;
  }
break;
// update sift karyawan
case 'updateshiftkaryawan':
  $data=json_decode($_POST['data']);
  // $query="SELECT * FROM employees";
  
  $shift_id=$data[0]->shiftId;
  $idEmployeSet="";
  $idEmployeUnSet="";
  
  for ($i=0; $i < count($data) ; $i++) {
    $shiftId=$data[$i]->shiftId;
    $employee_id=$data[$i]->employeId;



    if($shiftId != null){
      $querySet.=$employee_id. ($i < (count($data) - 1) ? "," : "");
      $idEmployeSet.=$employee_id. ($i < (count($data) - 1) ? "," : "");
    }else{
      $queryUnset.=$employee_id. ($i < (count($data) - 1) ? "," : "");
      $idEmployeUnSet.=$employee_id. ($i < (count($data) - 1) ? "," : "");
    }
  }


  if($idEmployeSet != ""){
    $querySet="UPDATE employees SET shift_id=$shift_id WHERE id IN ($idEmployeSet);";
    $result = $connection->query($querySet);
  }
  if($idEmployeUnSet != ""){
    $queryUnset="UPDATE employees SET shift_id=0 WHERE id IN ($idEmployeUnSet);";
    $result = $connection->query($queryUnset);
  }

  echo "success";
break;

}

}

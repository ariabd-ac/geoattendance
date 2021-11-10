$(document).ready(function() {
$('#swdatatable').dataTable({
    "iDisplayLength": 20,
    "aLengthMenu": [[20, 30, 50, -1], [20, 30, 50, "All"]]
});

//Timepicker
$('.timepicker').timepicker({
    showInputs: false,
    showMeridian: false,
    use24hours: true,
    format :'HH:mm'
})


function loading(){
    $(".loading").show();
    $(".loading").delay(1500).fadeOut(500);
}

/* ----------- Add ------------*/
$('.add-shift').submit(function (e) {
    if($('input[type=text]').val()==''){    
        swal({title:'Oops!', text: 'Harap bidang inputan tidak boleh ada yang kosong.!', icon: 'error', timer: 1500,});
        return false;
        loading();
    }
    else{
        loading();
        e.preventDefault();
        $.ajax({
            url:"sw-mod/shift/proses.php?action=add",
            type: "POST",
            data: new FormData(this),
            processData: false,
            contentType: false,
            cache: false,
            async: false,
            beforeSend: function () { 
              loading();
            },
            success: function (data) {
                if (data == 'success') {
                    swal({title: 'Berhasil!', text: 'Data Shift  berhasil disimpan.!', icon: 'success', timer: 1500,});
                   $('#modalAdd').modal('hide');
                   setTimeout(function(){ location.reload(); }, 1500);
                } else {
                    swal({title: 'Oops!', text: data, icon: 'error', timer: 1500,});
                }

            },
            complete: function () {
                $(".loading").hide();
            },
        });
    }
  });

/* -------------------- Edit ------------------- */
$('.update-shift').submit(function (e) {
    if($('#txtname').val()==''){    
         swal({title: 'Oops!', text: 'Harap bidang inputan tidak boleh ada yang kosong.!', icon: 'error', timer: 1500,});
         loading();
        return false;
    }
    else{
        loading();
        e.preventDefault();
        $.ajax({
            url:"sw-mod/shift/proses.php?action=update",
            type: "POST",
            data: new FormData(this),
            processData: false,
            contentType: false,
            cache: false,
            async: false,
            beforeSend: function () { 
                loading();
            },
            success: function (data) {
                if (data == 'success') {
                    swal({title: 'Berhasil!', text: 'Jabatan berhasil disimpan.!', icon: 'success', timer: 1500,});
                   $('#modalEdit').modal('hide');
                   setTimeout(function(){ location.reload(); }, 1500);

                } else {
                    swal({title: 'Oops!', text: data, icon: 'error', timer: 1500,});
                }

            },
            complete: function () {
                $(".loading").hide();
            },
        });
    }
  });

//   ----------- DETAIL KARYAWAN -------
$('.btn-detail-shift').on('click',function(e){
    var shiftId=$(this).data('shiftid');
    $.ajax({
        url:"sw-mod/shift/proses.php?action=getdatakaryawan&idshift="+shiftId,
        type: "GET",
        // data: new FormData(this),
        processData: false,
        contentType: false,
        cache: false,
        async: false,
        beforeSend: function () { 
            loading();
        },
        success: function (data) {
            $('#tbody-detail-karyawan').html(data)
            // if (data == 'success') {
            //     swal({title: 'Berhasil!', text: 'Jabatan berhasil disimpan.!', icon: 'success', timer: 1500,});
            //    $('#modalEdit').modal('hide');
            //    setTimeout(function(){ location.reload(); }, 1500);

            // } else {
            //     swal({title: 'Oops!', text: data, icon: 'error', timer: 1500,});
            // }

        },
        complete: function () {
            $(".loading").hide();
        },
    });
})

//   ----------- DETAIL KARYAWAN -------

const dataShiftKaryawan=[];
// handle checkbox shift karyawan
$(document).on('change','.check-shift-karyawan',function(){
    console.log("CHANGE")
    let employeId=$(this).data('employeeid');
    let shiftId=$(this).data('shiftid');
    let data={
        employeId:employeId,
        shiftId : this.checked ? shiftId : null
    };
    let indexIfExist=null;
    for (let index = 0; index < dataShiftKaryawan.length; index++) {
        const dt = dataShiftKaryawan[index];
        if(employeId == dt.employeId){
            indexIfExist = index
        }
    }

    if(indexIfExist != null){
        dataShiftKaryawan[indexIfExist].shiftId = data.shiftId;
    }else{
        dataShiftKaryawan.push(data);
    }

})
// handle checkbox shift karyawan

// handle submit shift karyawan
$(document).on('click','#submit-detail-shift',function(e){
    e.preventDefault()
    loading();
    console.log(dataShiftKaryawan,"Data SHIFT KAR")
    const formData=new FormData();
    formData.append("data",JSON.stringify(dataShiftKaryawan));
    $.ajax({
        url:"sw-mod/shift/proses.php?action=updateshiftkaryawan",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        cache: false,
        async: false,
        beforeSend: function () { 
            loading();
        },
        success: function (data) {
            console.log(data)
            if (data == 'success') {
                swal({title: 'Berhasil!', text: 'Berhasil Update Sift Karyawan', icon: 'success', timer: 1500,});
                // $('#modalEdit').modal('hide');
                setTimeout(function(){ location.reload(); }, 1000);

            } else {
                swal({title: 'Oops!', text: data, icon: 'error', timer: 1500,});
            }

        },
        complete: function () {
            $(".loading").hide();
        },
    });
})
// handle submit shift karyawan


/*------------ Delete -------------*/
 $(document).on('click', '.delete', function(){ 
        var id = $(this).attr("data-id");
          swal({
            text: "Anda yakin menghapus data ini?",
            icon: "warning",
              buttons: {
                cancel: true,
                confirm: true,
              },
            value: "delete",
          })

          .then((value) => {
            if(value) {
                loading();
                $.ajax({  
                     url:"sw-mod/shift/proses.php?action=delete",
                     type:'POST',    
                     data:{id:id},  
                    success:function(data){ 
                        if (data == 'success') {
                            swal({title: 'Berhasil!', text: 'Data berhasil dihapus.!', icon: 'success', timer: 1500,});
                            setTimeout(function(){ location.reload(); }, 1500);
                        } else {
                            swal({title: 'Gagal!', text: data, icon: 'error', timer: 1500,});
                            
                        }
                     }  
                });  
           } else{  
            return false;
        }  
    });
}); 

});
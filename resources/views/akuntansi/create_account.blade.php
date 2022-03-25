@extends('layouts.app')

@section('title', 'Create Journal')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Buat Akun</h1>
</section>

<!-- Main content -->
<section class="content">
{!! Form::open(['url' => action('AkuntanController@storeAkun'), 'method' => 'post', 'id' => 'user_add_form' ]) !!}
  <div class="row">
    <div class="col-md-12">
  @component('components.widget')
            <br>
          <div class="col-sm-12">
              <div class="col-sm-2">
                  <label>Level</label>    
              </div>
              <div class="col-sm-10">
                  <select class="form-control select2" name="level" required onchange="setLevel()" id="level">
                  <option value="0">0</option>
                  <option value="1">1</option>
                  <option value="2">2</option>
                  <option value="3">3</option>
              </select>
              </div>
          </div> 
          <div class="col-sm-12" id="parent" hidden>
          <br>
              <div class="col-sm-2">
                  {!! Form::label('Akun:*') !!}
              </div>
              <div class="col-sm-10">
                  {!! Form::select('id_parent', $data['parent_option'], null, ['id' => 'id_parent', 'class' => 'form-control select2', 'onchange'=>'cekParent()' , 'id' => 'id_parent', 'required']); !!}
              </div>
          </div> 
          <div class="col-sm-12" id="level2" hidden>
          <br>
              <div class="col-sm-2">
                  <label>Sub Akun Parent</label>    
              </div>
              <div class="col-sm-10">
                  <select class="form-control select2" name="level2"  onchange="cekSubLevel1()" id="sublevel1" width="100%">
                  </select>
              </div>
          </div> 
          <div class="col-sm-12" id="level3" style="display:none">
          <br>
              <div class="col-sm-2">
                  <label>Sub Sub Akun Parent</label>    
              </div>
              <div class="col-sm-10">
                  <select class="form-control select2" name="level3"  onchange="cekSubLevel2()" id="sublevel2">
                  </select>
              </div>
          </div> 
          <!-- <div class="col-sm-12">
          <br>
              <div class="col-sm-2">
                  <label>Kode Parent</label>    
              </div>
              <div class="col-sm-10">
                  <input class="form-control" name="kode_parent"  id="kode_parent" readonly="">
              </div>
          </div>  -->
          <div class="col-sm-12">
          <br>
              <div class="col-sm-2">
                  <label>Nama Akun</label>    
              </div>
              <div class="col-sm-10">
                  <input class="form-control" name="nama_akun" required >
              </div>
          </div> 
          <div class="col-sm-12">
          <br>
              <div class="col-sm-2">
                  <label>Kode Akun</label>    
              </div>
              <div class="col-sm-10">
                  <input class="form-control" name="no_akun" id="no_akun" required readonly="">
              </div>
          </div> 
          <div class="col-sm-12">
          <br>
              <div class="col-sm-2">
                  
              </div>
              <div class="col-sm-10">
                  <button type="submit" class="btn btn-success"><i class="fa fa-floppy-o"></i> Create</button> 
              </div>
          </div> 
  @endcomponent
  </div>
  </div>
{!! Form::close() !!}
  @stop
@section('javascript')
<script src="https://code.jquery.com/jquery-1.10.2.js"></script>
<script type="text/javascript">
  var level=1;

    function setLevel(){
        level=parseInt($('#level').val());
        if (level == 0) {
            $('#parent').hide();
            $('#level2').hide();
            $('#level3').hide();
            $('#kode_parent').val('');
        }else if(level == 1){
            $('#parent').show();
            $('#level2').hide();
            $('#level3').hide();
            $('#kode_parent').val('');
            $('#sublevel1').empty();
        }else if(level == 2){
            $('#parent').show();
            $('#level2').show();
            $('#level3').hide();
            $('#kode_parent').val('');
            $('#sublevel2').empty();
        }else if(level == 3){
            $('#parent').show();
            $('#level2').show();
            $('#level3').show();
            $('#kode_parent').val('');
        }
    }
    var id_parent=0;
    function cekParent(){
        var id=$('#id_parent').val();
        if (level == 1) {
            cekKodeAkun(id);
        }else if(level > 1){
            $('#sublevel1').empty();
            var subAkun=$('#sublevel1');
            $.ajax({
                url : 'getLevel/'+id, 
                type : 'GET',
                dataType : 'json',
                success: function(response){
                    // console.log(response);
                    arrData=response;
                    var option = '<option value="">Pilih Akun</option>';
                    for (var i = 0; i < arrData.length; i++) {
                        option+='<option value="'+arrData[i]['id_akun']+'">'+arrData[i]['nama_akun']+'</option>';
                    }
                    subAkun.append(option);
                }
            });
        }
        var parent=@php echo $data['parent_option_js'] @endphp;
        for (var i = 0; i < parent.length; i++) {
            if (parent[i]['label'] == id) {
                id_parent=parent[i]['value'];
            }
        }
    }
    function cekSubLevel1(){
        var id=$('#sublevel1').val();
        if (level == 2) {
            cekKodeAkun(id);
        }else if(level > 2){
            $('#sublevel2').empty();
            var subAkun=$('#sublevel2');
            $.ajax({
                url : 'getLevel/'+id, 
                type : 'GET',
                dataType : 'json',
                success: function(response){
                    // console.log(response);
                    arrData=response;
                    var option = '<option value="">Pilih Akun</option>';
                    for (var i = 0; i < arrData.length; i++) {
                        option+='<option value="'+arrData[i]['id_akun']+'">'+arrData[i]['nama_akun']+'</option>';
                    }
                    // console.log(option);
                    subAkun.append(option);
                }
            });
        }
    }
    function cekSubLevel2(){
        var id=$('#sublevel2').val();
        if (level == 3) {
            cekKodeAkun(id);
        }
    }
    var dataAkun=0;
    function cekKodeAkun(id){
        $.ajax({
            url : "{{URL::to('akuntansi/getNoAkun')}}"+'/'+id, 
            type : 'GET',
            dataType : 'json',
            "success": function(response){
                dataAkun=response;
                var nomor=pecahAkun(dataAkun['no_akun']);
                if (level == 1) {
                    var iterate=parseInt(dataAkun['total'])+1;
                    $('#no_akun').val(dataAkun['no_akun_main']+'.'+iterate+'.0.0');
                }else if (level == 2) {
                    var iterate=parseInt(dataAkun['total'])+1;
                    var pecah=dataAkun['no_akun_main'].split('.');
                    $('#no_akun').val(pecah[0]+'.'+pecah[1]+'.'+iterate+'.0');
                }
                else if (level == 3) {
                    var iterate=parseInt(dataAkun['total'])+1;
                    var pecah=dataAkun['no_akun_main'].split('.');
                    $('#no_akun').val(pecah[0]+'.'+pecah[1]+'.'+pecah[2]+'.'+iterate);
                }
                else if (level == 4) {
                    var iterate=parseInt(dataAkun['total'])+1;
                    var pecah=dataAkun['no_akun_main'].split('.');
                    $('#no_akun').val(pecah[0]+'.'+pecah[1]+'.'+pecah[2]+'.'+pecah[3]+'.'+iterate);
                }
            }
        });
    }

    function pecahAkun(val){
        if (val != null) {
            var a=val.split('.');
        }else{
            var a=[0,0,0,0];
        }
        return a;
    }
</script>
@endsection

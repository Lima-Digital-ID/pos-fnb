@extends('layouts.app')

@section('title', 'Create Journal')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Buat Jurnal</h1>
</section>

<!-- Main content -->
<section class="content">
{!! Form::open(['url' => action('AkuntanController@storeJurnal'), 'method' => 'post', 'id' => 'user_add_form' ]) !!}
  <div class="row">
    <div class="col-md-12">
  @component('components.widget')
      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('Tanggal : *') !!}
            {!! Form::date('tanggal', date('Y-m-d'), ['class' => 'form-control', 'required', 'placeholder' => 'Tanggal' ]); !!}
        </div>
        @if($user->role == 1)
        <div class="form-group">
          {!! Form::label('Lokasi : *') !!}
            {!! Form::select('location_id', 
                        $business_locations, $location_id, ['class' => 'form-control select2', 'placeholder' => 'Pilih Lokasi', 'required' => 'required']); !!}
        </div>
        @endif
      </div>
      <div class="col-md-9">
        <div class="form-group">
          {!! Form::label('Deskripsi :*') !!}
            {!! Form::textarea('deskripsi', null, ['class' => 'form-control', 'required', 'placeholder' => 'Keterangan Jurnal' ]); !!}
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('Akun:*') !!}
          {!! Form::select('akun', $data['akun_option'], null, ['id' => 'akun', 'class' => 'form-control select2' , 'id' => 'akun', 'required']); !!}
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('Jumlah:*') !!}
          {!! Form::number('jumlah_akun', null, ['id' => 'jumlah_akun', 'class' => 'form-control', 'required', 'placeholder' => 'Jumlah', 'onkeyup'=>'cekJumlahLawan()' ]); !!}
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('Tipe:*') !!}
          {!! Form::select('tipe_akun', array(1 => 'Debit', 0 => 'Kredit'), null, ['class' => 'form-control select2', 
                    'id' => 'tipe_akun', 
                    'required']); !!}
        </div>
      </div>
      <div class="col-sm-12">
        <hr>
      </div>
    <div id="input_lawan_akun">
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('Lawan Akun:*') !!}
          {!! Form::select('lawan_akun[]', $data['akun_option'], null, ['id' => 'lawan_akun[]', 'class' => 'form-control select2', 'onchange'=>'cekLawan()']); !!}
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('Jumlah:*') !!}
          {!! Form::number('jumlah_lawan_akun[]', null, ['id' => 'jumlah_lawan_akun[]', 'class' => 'form-control', 'required', 'placeholder' => 'Jumlah' , 'onkeyup'=>'cekJumlahLawan()']); !!}
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('Tipe:*') !!}
          {!! Form::select('tipe_lawan_akun[]', array(1 => 'Debit', 0 => 'Kredit'), null, ['id' => 'tipe_lawan_akun[]', 'class' => 'form-control select2', 
                    'id' => 'tipe_lawan_akun', 
                    'required']); !!}
        </div>
      </div>
    </div>
      <div class="col-sm-12">
          <div align="right">
              <br>
              <button id="add_lawan_akun"><i class="fa fa-plus"></i>Tambah</button>
          </div>
      </div>
      <div class="col-md-12">
        <br>
        <button type="submit" class="btn btn-primary pull-right" id="submit">@lang( 'messages.save' )</button>
      </div>
  @endcomponent
  </div>
  </div>
{!! Form::close() !!}
  @stop
@section('javascript')
<script src="https://code.jquery.com/jquery-1.10.2.js"></script>
<script type="text/javascript">
  $(document).ready(function(){
        var max_input=10;
        var add_lawan_akun=$('#add_lawan_akun');
        var input_lawan_akun=$('#input_lawan_akun');
        var y = 1; //initlal text box count
        $(add_lawan_akun).click(function(e){
            e.preventDefault();
            var akun_option='<option value="">Pilih Akun / No Akun</option>';
            var akun_lawan=@php echo $data['akun_option_js'] @endphp;
            console.log(akun_lawan);
            for (var i = 0; i < akun_lawan.length; i++) {
                akun_option+='<option value="'+akun_lawan[i].label+'">'+akun_lawan[i].value+'</option>';
            }

            var input_akun = '<select id="lawan_akun[]" name="lawan_akun[]" required class="form-control select2">'+akun_option+'</select>';
            var input_jumlah = '<input class="form-control" name="jumlah_lawan[]" id="jumlah_lawan[]" required type="number" onkeyup="cekJumlahLawan()">';
            var input_tipe = '<select class="form-control" name="tipe_akun_lawan[]"><option value="1">Debit</option><option value="0">Kredit</option></select>';
            if (y < max_input) {
                y++;
                $(input_lawan_akun).append('<div class="form-group"><br><div class="col-sm-4"><br><div class="input-group"><span class="input-group-addon"><a href="#" class="remove_field_obat" id="remove_field_obat">X</a></span>'+input_akun+'</div></div><div class="col-sm-4"><br>'+input_jumlah+'</div><div class="col-sm-4"><br>'+input_tipe+'</div>'); //add input box
            }
            $('select').select2({
                // dropdownAutoWidth : false,
                width: '100%'
            });
        });
        $(input_lawan_akun).on("click","#remove_field_obat", function(e){ //user click on remove text
            e.preventDefault(); 
            $(this).closest('.form-group').remove(); y--;
            // get_obat(null);
        });
    });
    function cekJumlahLawan(selectObject = null, isCheckJml = false) {
        var jumlah_length = $("[id^=jumlah_lawan]").length;
        var jumlah_akun = $("[id^=jumlah_akun]").val();
        if (jumlah_akun == '') {
            alert('jumlah akun belum terisi');
            $('#jumlah_akun').focus();
        }
        // console.log(jumlah_length);
        var total=0;
        for (var x = 0; x < jumlah_length; x++) {
            var lawan = $("[id^=jumlah_lawan]").eq(x).val() != '' ? parseInt($("[id^=jumlah_lawan]").eq(x).val()) : 0;
            total+=lawan;
            console.log(lawan);
        }
        if (total == parseInt(jumlah_akun)) {
            $('#submit').attr('disabled', false);
        }else{
            $('#submit').attr('disabled', true);
        }
    }
    function cekLawan(){
        var lawan_length = $("[id^=lawan_akun]").length;
        var jumlah=$("[id^=jumlah_akun]").val()
        for (var x = 0; x < lawan_length; x++) {
            if ($("[id^=lawan_akun]").eq(x).val() == 15) {
                console.log($("[id^=jumlah_lawan]").eq(x).val());
                $('#add_lawan_akun').attr('disabled', true);
                $("[id^=jumlah_lawan]").eq(x).val(jumlah);
                $('#submit').attr('disabled', false);
            }else{
                $('#add_lawan_akun').attr('disabled', false);
            }
        };
    }
  
</script>
@endsection

<div class="row">
  <div class="col-md-10 col-md-offset-1 col-xs-12">
    <div class="table-responsive">
      <table class="table table-condensed bg-gray">
        <tr>
          <th>Sumber dana</th>
          <th>Total</th>
        </tr>
        @if(!empty($details[0]))
          @foreach( $details as $detail )
            <tr>
              <td>{{ $detail->sumber}}</td>
              <td>
                Rp. {{ number_format($detail->total, 0, '.', '.') }}
              </td>
            </tr>
          @endforeach
        @else
          <tr>
            <td colspan="4" class="text-center">
              -
            </td>
          </tr>
        @endif
        
      </table>
    </div>
    @if($details[0]->bukti_setor != null)
    <h4>Bukti Setoran</h4>
    <a href="#" data-toggle="modal" onclick="sendUri(this)" data-href="{{ url('uploads/bukti_setor').'/'. $details[0]->bukti_setor}}" data-target="#exampleModal"><img src="{{ url('uploads/bukti_setor').'/'. $details[0]->bukti_setor}}" width="150px"></a>
    @endif
  </div>
</div>
@extends('layouts.app')
@section('title', 'Gestor SPRI - Información campañas')
@section('maintitle')
    <h1>{!! trans('messages.info_campanas') !!}</h2>
@stop
@section('content')
  <div class="row">
    <div class="col-12 p-0">
      <table class="table table-striped">
        <thead class="table-dark">
          <tr>
            <th colspan="11">
              <form id="search" class="row m-0">
                <div class="col-auto"><input class="form-control" type="text" name="search" value="" /></div>
                <div class="col-auto"><button class="btn btn-secondary">Buscar</button></div>
                <div class="col-auto"><a href="./csv.php" class="btn btn-secondary">Exportar a CSV</a></div>
              </form>
            </th>
          </tr>
          <tr>
            <th scope="col">Nombre</th>
            <th scope="col">Fecha</th>
            <th scope="col">Título</th>
            <th scope="col">Enviados</th>
            <th scope="col">Aperturas únicas</th>
            <th scope="col">Porcentaje de aperturas únicas</th>
            <th scope="col">Aperturas</th>
            <th scope="col">Clicks únicos</th>
            <th scope="col">Porcentaje de clicks únicos</th>
            <th scope="col">Clicks totales</th>
            <th scope="col">Bajas</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
    <div class="col-12 p-3">
      <div id="loading" style="display: none;">Cargando ...</div>
      <button id="loadmore" class="btn btn-primary" style="display: none;">Cargar más</button>
    </div>      
  </div>
@stop
@section('footer')
<script>
var offset = 0;
var limit = {{ env('AC_API_LIMIT') }};
jQuery(document).ready(function() {
  loadMore();
  jQuery("#loadmore").click(function() { loadMore(); });
  jQuery("#search").submit(function(e) {
    e.preventDefault();
    offset = 0;
    jQuery("table tbody").empty();
    if (jQuery("input[name=search]").val() != '') {
      searchCampaign();
     } else {
      loadMore();
    }
  });
});

function generateTable(json) {
  json.forEach(function(data, index) {
    jQuery("table tbody").append("<tr>"+
      "<td><a href='{{ route('info.campanas') }}/view/"+data.id+(data.type == 'split' ? "&showtestab=yes" : "")+"' target='_blank'>"+data.name+"</a>"+
        (data.segment_name != '' ? "<br/><span style='font-size: 10px;'>"+data.segment_name+"</span>" : "")+
        (data.type == 'split' ? "<br/><span style='font-size: 12px; font-weight: bold;'><a href='{{ route('info.campanas') }}/view/"+data.id+(data.type == 'split' ? "&showtestab=yes&testab=yes" : "")+"' target='_blank'>TEST B</a> | <a href='./compare.php?campaign_id="+data.id+"' target='_blank'>COMPARAR</a></span>" : "")+
      "<div class='image'><img src='"+data.image+"' /></div></td>"+
      "<td>"+data.date+"</td>"+
      "<td>"+data.subject+"</td>"+
      "<td>"+data.send_amt+
        (data.type == 'split' && data.testab ? "<br/><small>A: "+data.testab[0].send_amt+"<br/>B: "+data.testab[1].send_amt : "")+"</td>"+
      "<td>"+data.uniqueopens+
        (data.type == 'split' && data.testab ? "<br/><small>A: "+data.testab[0].uniqueopens+"<br/>B: "+data.testab[1].uniqueopens : "")+"</td>"+
      "<td>"+data.uniqueopens_percent+"%"+
        (data.type == 'split' && data.testab ? "<br/><small>A: "+data.testab[0].uniqueopens_percent+"<br/>B: "+data.testab[1].uniqueopens_percent : "")+"</td>"+
      "<td>"+data.opens+
        (data.type == 'split' && data.testab ? "<br/><small>A: "+data.testab[0].opens+"<br/>B: "+data.testab[1].opens : "")+"</td>"+
      "<td>"+data.uniquelinkclicks+
        (data.type == 'split' && data.testab ? "<br/><small>A: "+data.testab[0].uniquelinkclicks+"<br/>B: "+data.testab[1].uniquelinkclicks : "")+"</td>"+
      "<td>"+data.uniquelinkclicks_percent+"%"+
        (data.type == 'split' && data.testab ? "<br/><small>A: "+data.testab[0].uniquelinkclicks_percent+"<br/>B: "+data.testab[1].uniquelinkclicks_percent : "")+"</td>"+
      "<td>"+data.linkclicks+
        (data.type == 'split' && data.testab ? "<br/><small>A: "+data.testab[0].linkclicks+"<br/>B: "+data.testab[1].linkclicks : "")+"</td>"+
      "<td>"+data.unsubscribes+
        (data.type == 'split' && data.testab ? "<br/><small>A: "+data.testab[0].unsubscribes+"<br/>B: "+data.testab[1].unsubscribes : "")+"</td>"+
    "</tr>")
  });
}

function loadMore() {
  jQuery.ajax({
    url : '{{ route('info.ajax') }}',
    data : { offset: offset },
    type : 'GET',
    dataType : 'json',
    beforeSend: function () {
      jQuery("#loading").css("display", "block");
      jQuery("#loadmore").css("display", "none");
    },
    success : function(json) {
      generateTable(json);
      offset = offset + limit;
    },
    error : function(xhr, status) {
        alert('Disculpe, existió un problema');
    },
    complete : function(xhr, status) {
      jQuery("#loading").css("display", "none");
      jQuery("#loadmore").css("display", "block");
    }
  });
}

function searchCampaign() {
  jQuery.ajax({
    url : './ajax.php',
    data : { search: jQuery("#search input[name=search]").val() },
    type : 'GET',
    dataType : 'json',
    beforeSend: function () {
      jQuery("#loading").css("display", "block");
      jQuery("#loadmore").css("display", "none");
    },
    success : function(json) {
      generateTable(json)
    },
    error : function(xhr, status) {
        alert('Disculpe, existió un problema');
    },
    complete : function(xhr, status) {
      jQuery("#loading").css("display", "none");
    }
  });
}
</script>
<style>
td {
  position: relative;
}

td > div.image {
  display: none;
  position: absolute;
  top: 100%;
  left: 40%;
  height: 300px;
  overflow: hidden;
  z-index: 10;
  border: 1px solid #000;
  min-width: 250px;
}

td:hover > div.image {
  display: block;
}

td > div.image img {
  width: 100%;
}
</style>
@stop
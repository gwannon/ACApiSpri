@extends('layouts.app')
@section('title', 'Gestor SPRI - Información LeadScorings')
@section('maintitle')
    <h1>{!! trans('messages.info_usuarios_nuevos') !!} ({{ $days }} días)</h2>
@stop
@section('content')
  <div class="row">
    <div class="col-12 p-0">
      <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
      <div id="chart_div" style="width: 100%; min-height: 300px;"></div>
      <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);
        function drawChart() {
          var data = google.visualization.arrayToDataTable([
            ['Fecha', 'Usuarios nuevos'],
            @foreach($data as $date => $total)
              ['{{ $date }}', {{ $total }}],
            @endforeach
          ]);

          var options = {
            //title: 'Usuarios nuevos',
            //hAxis: {title: 'Year',  titleTextStyle: {color: '#333'}},
            vAxis: {minValue: 0}
          };
          var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
          chart.draw(data, options);
        }
      </script>

    </div>
  </div>
@stop

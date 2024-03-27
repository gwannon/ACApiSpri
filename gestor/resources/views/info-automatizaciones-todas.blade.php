@extends('layouts.app')
@section('title', 'Gestor SPRI - Información automatizaciones')
@section('maintitle')
    <h1>{!! trans('messages.info_automatizaciones_todas') !!}</h2>
@stop
@section('content')
  <div class="row">
    <div class="col-12 p-0">
      <table class="table table-striped">
        <thead class="table-dark">
          <tr>
            <th scope="col" width="10%">ID</th>
            <th scope="col" width="40%">Automatización</th>
            <th scope="col" width="10%">Ejecutada/Finalizada</th>
            <th scope="col" width="10%">Estado</th>
          </tr>
        </thead>
        <tbody>
          @foreach($data as $autom_id => $autom)
            <tr>
              <td>{{$autom_id}}</td>
              <td><a href="{{$autom['screenshot']}}" target="_blank">{{$autom['name']}}</a></td>
              <td>{{$autom['entered']}}/{{$autom['exited']}}</td>
              @if ($autom['status'] == 1)
                <td><span style='color: green;'>Activa</span></td>
              @else
                <td><span style='color: red;'>No activa</span></td>
              @endif
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@stop

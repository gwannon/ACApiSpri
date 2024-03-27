@extends('layouts.app')
@section('title', 'Gestor SPRI - Información automatizaciones')
@section('maintitle')
    <h1>{!! trans('messages.info_automatizaciones') !!}</h2>
@stop
@section('content')
  <div class="row">
    <div class="col-12 p-0">
      <table class="table table-striped">
        <thead class="table-dark">
          <tr>
            <th scope="col" width="10%">ID</th>
            <th scope="col" width="40%">Automatización</th>
            <th scope="col" width="10%">Veces ejecutada</th>
            <th scope="col" width="10%">Puntos asignados</th>
          </tr>
        </thead>
        <tbody>
          @foreach($data as $label => $automs)
            <tr>
              <th colspan="4" class="text-center"><h3>{{ $label }}</h3></th>
            </tr>
            @foreach($automs as $autom_id => $autom)
              <tr>
                <td>{{$autom_id}}</td>
                <td>{{$autom['name']}}</td>
                <td>{{$autom['exited']}}</td>
                <td>{{$autom['total']}}</td>
              </tr>
            @endforeach
            
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@stop

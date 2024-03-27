@extends('layouts.app')
@section('title', 'Gestor SPRI - Apuntados boletines')
@section('maintitle')
    <h1>{!! trans('messages.info_apuntados-boletines') !!}</h2>
@stop
@section('content')
  <div class="row">
    <div class="col-12 p-0">
      <table class="table table-striped">
        <thead class="table-dark">
          <tr>
            <th scope="col" width="10%">Boletín</th>
            <th scope="col" width="40%">Usuarios apuntados</th>
          </tr>
        </thead>
        <tbody>
          @foreach($data as $newsltter)
            <tr>
              <td>{{$newsltter['name']}}</td>
              <td>{{$newsltter['total']}}</a></td>
            </tr>
          @endforeach
          <tr>
            <th>Total:</th>
            <td>{{$total}}</a></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
@stop

@extends('layouts.app')
@section('title', 'Gestor SPRI - Información LeadScorings')
@section('maintitle')
    <h1>{!! trans('messages.info_leadscorings_'.$type) !!}</h2>
@stop
@section('content')
  <div class="row">
    <div class="col-12 p-0">
      <table class="table table-striped">
        <thead class="table-dark">
          <tr>
            <th scope="col" width="10%">ID</th>
            <th scope="col" width="40%">Leadscoring > 0</th>
            <th scope="col" width="10%">Número de contactos</th>
          </tr>
        </thead>
        <tbody>
            @foreach($data as $leadscoring_id => $leadscoring)
              <tr>
                <td>{{$leadscoring_id}}</td>
                <td>{{$leadscoring['name']}}</td>
                <td>{{$leadscoring['susbcribers']}}</td>
              </tr>
            @endforeach
        </tbody>
      </table>
    </div>
  </div>
@stop

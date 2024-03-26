@extends('layouts.app')
@section('title', 'Gestor SPRI - Información etiquetas')
@section('maintitle')
    <h1>{!! trans('messages.info_tags') !!}</h2>
@stop
@section('content')
  <div class="row">
    <div class="col-12 p-0">
      <table class="table table-striped">
        <thead class="table-dark">
          <tr>
            <th scope="col" width="10%">ID</th>
            <th scope="col" width="40%">Etiqueta</th>
            <th scope="col" width="10%">Contactos</th>
            <th scope="col" width="40%">Última actividad</th>
          </tr>
        </thead>
        <tbody>
          @foreach($data as $label => $tags)
            <tr>
              <th colspan="4" class="text-center"><h3>{{ $label }}</h3></th>
            </tr>
            @foreach($tags as $tag_id => $tag)
              <tr>
                <td>{{$tag_id}}</td>
                <td>{{$tag['tag']}}</td>
                <td>{{$tag['susbcribers']}}</td>
                <td>{{$tag['date']}}</td>
              </tr>
            @endforeach
            
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@stop

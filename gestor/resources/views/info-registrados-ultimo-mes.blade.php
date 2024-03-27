@extends('layouts.app')
@section('title', 'Gestor SPRI - Registrados último mes')
@section('maintitle')
    <h1>{!! trans('messages.info_registrados-ultimo-mes') !!}</h2>
@stop
@section('content')
  <div class="row">
    <div class="col-12 p-0">
      <table class="table table-striped">
        <thead class="table-dark">
          <tr>
            <th scope="col" width="10%">Tag</th>
            <th scope="col" width="40%">Total</th>
          </tr>
        </thead>
        <tbody>
          @foreach($data as $tag)
            <tr>
              <td>{{$tag['name']}}</td>
              <td>{{$tag['total']}}</a></td>
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

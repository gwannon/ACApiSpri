@extends('layouts.app')
 
@section('title', 'Admin')
@section('maintitle')
    <h1>{!! trans('messages.panel_de_gestion') !!}</h2>
@stop
@section('content')
    <h2 id="links" class="mt-3">{!! trans('messages.otrospaneleslinks') !!}</h2>
    @if (request()->get('delete') == 'ok')
        <div class="alert alert-success" role="alert">
        {!! trans('messages.deletelinkok') !!}
        </div>
    @endif
    @if (count($links) > 0)
        <div style="overflow-x:auto;">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">{!! trans('messages.title') !!}</th>
                        <th scope="col">{!! trans('messages.url') !!}</th>
                        <th scope="col" colspan="3">{!! trans('messages.actions') !!}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($links as $link)
                        <tr>
                            <th class="align-middle" scope="row">{{ $link['title'] }}</th>
                            <td class="align-middle"><a href="{{ $link['url'] }}">{{ $link['url'] }}</a></td>
                            <td class="align-middle">
                                <a class="btn btn-danger btn-sm delete_link" href="{{ route('admin.otrospaneles.borrar', $link['id']) }}">{!! trans('messages.delete') !!}</a>
                            </td>
                        </tr> 
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p>{!! trans('messages.nolinks') !!}</p>
    @endif
    <h2 id="newlink">{!! trans('messages.createlink') !!}</h2>
    @if (isset($link_created) && $link_created)
        <div class="alert alert-success" role="alert">
        {!! trans('messages.createlinkok') !!}
        </div>
    @elseif (isset($link_created) && !$link_created)
        <div class="alert alert-danger" role="alert">
        {!! trans('messages.linkexists') !!}
        </div>
    @endif
    <form method="post" action="{{ route('admin.otrospaneles.crear') }}">
        @csrf <!-- {{ csrf_field() }} -->
        <div class="row mb-3">
            <div class="col-md-6 form-group">
                <label for="inputTitle">{!! trans('messages.title') !!} *</label>
                <input type="text" class="form-control" id="inputTitle" name="linktitle" value="" required>
            </div>
            <div class="col-md-6 form-group position-relative">
                <label for="inputUrl">{!! trans('messages.url') !!} *</label>
                <input type="url" class="form-control" id="inputUrl" name="linkurl" value="" required>
            </div>
        </div>
        <input type="submit" value="{!! trans('messages.createlink') !!}" class="btn btn-primary mb-2">
    </form>
@stop
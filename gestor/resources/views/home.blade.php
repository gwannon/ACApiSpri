@extends('layouts.app')
@section('title', 'Gestor SPRI')
@section('maintitle')
    <h1>{!! trans('messages.panel_de_gestion') !!}</h2>
@stop
@section('content')
    <div class="row mt-3">
        <div class="col-md-4 form-group mb-3">
            <h2>{!! trans('messages.newsletters') !!}</h2>
            @if (preg_match('/boletin-basquetrade/', Auth::user()->perms))
                <a href="{{ route('boletines.been-basquetrade') }}" class="btn btn-primary mb-3">{!! trans('messages.boletin_basquetrade') !!}</a>
            @endif
            @if (preg_match('/boletin-bdih-activos/', Auth::user()->perms))
                <a href="{{ route('boletines.bdih-activos') }}" class="btn btn-primary mb-3">{!! trans('messages.boletin_bdih_activos') !!}</a>
            @endif
        </div>
        <div class="col-md-4 form-group mb-3">
            <h2>{!! trans('messages.info') !!}</h2>
            @if (preg_match('/info-campanas/', Auth::user()->perms))
                <a href="{{ route('info.campanas') }}" class="btn btn-primary mb-3">{!! trans('messages.info_campanas') !!}</a>
                <a href="{{ route('info.tags') }}" class="btn btn-primary mb-3">{!! trans('messages.info_tags') !!}</a>
                <a href="{{ route('info.leadscorings', 'intereses') }}" class="btn btn-primary mb-3">{!! trans('messages.info_leadscorings_intereses') !!}</a>
                <a href="{{ route('info.leadscorings', 'ayudas') }}" class="btn btn-primary mb-3">{!! trans('messages.info_leadscorings_ayudas') !!}</a>
                <a href="{{ route('info.automatizaciones') }}" class="btn btn-primary mb-3">{!! trans('messages.info_automatizaciones') !!}</a>
                <a href="{{ route('info.automatizaciones-todas') }}" class="btn btn-primary mb-3">{!! trans('messages.info_automatizaciones_todas') !!}</a>
                <h4>{!! trans('messages.info_usuarios_nuevos') !!}</h4>
                <a href="{{ route('info.usuarios', 7) }}" class="btn btn-primary mb-3">{!! trans('messages.7_days') !!}</a>
                <a href="{{ route('info.usuarios', 30) }}" class="btn btn-primary mb-3">{!! trans('messages.30_days') !!}</a>
                <a href="{{ route('info.usuarios', 90) }}" class="btn btn-primary mb-3">{!! trans('messages.90_days') !!}</a>
            @endif
        </div>
        <div class="col-md-4 form-group mb-3">
            <h2>{!! trans('messages.admin') !!}</h2>
            @if (preg_match('/admin-usuarios/', Auth::user()->perms))
                <a href="{{ route('admin.usuarios') }}" class="btn btn-primary mb-3">{!! trans('messages.adminusers') !!}</a>
            @endif
            @if (preg_match('/otros-paneles/', Auth::user()->perms))
                <a href="https://datastudio.google.com/u/0/reporting/0487745f-a042-4ad7-ac0a-caf987501b47/" class="btn btn-primary mb-3" target="_blank">{!! trans('messages.otherpanels1') !!}</a>
                <a href="https://datastudio.google.com/reporting/5bef1a45-419c-40e3-8873-5db41204d50d/page/p_l3fb3blyqc" class="btn btn-primary mb-3" target="_blank">{!! trans('messages.otherpanels2') !!}</a>
                <a href="https://trello.com/b/eLtoU5H1/spri-calendario-editorial" class="btn btn-primary mb-3" target="_blank">{!! trans('messages.otherpanels3') !!}</a>
                <a href="https://docs.google.com/spreadsheets/d/1Mps6rPPRks23la98QxwzdOhQu8YtXpwsqPLCxD5hhsc/edit?usp=sharing" class="btn btn-primary mb-3" target="_blank">{!! trans('messages.otherpanels4') !!}</a>
            @endif
        </div>
    </div>
@stop
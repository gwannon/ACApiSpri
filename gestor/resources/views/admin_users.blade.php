@extends('layouts.app')
 
@section('title', 'Admin')
@section('maintitle')
    <h1>{!! trans('messages.panel_de_gestion') !!}</h2>
@stop
@section('content')
    <h2 id="users" class="mt-3">{!! trans('messages.registeredusers') !!}</h2>
    @if (request()->get('delete') == 'ok')
        <div class="alert alert-success" role="alert">
        {!! trans('messages.deleteuserok') !!}
        </div>
    @endif
    @if (count($users) > 0)
        <div style="overflow-x:auto;">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">{!! trans('messages.user') !!}</th>
                        <th scope="col">{!! trans('messages.email') !!}</th>
                        <th scope="col">{!! trans('messages.perms') !!}</th>
                        <th scope="col" colspan="3">{!! trans('messages.actions') !!}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr {{ ($user['withdrawn'] === 1 ? " class=withdrawned" : "") }}>
                            <th class="align-middle" scope="row">{{ $user['name'] }}</th>
                            <td class="align-middle"><a href="mailto:{{ $user['email'] }}">{{ $user['email'] }}</a></td>
                            <td class="align-middle">
                                @foreach (explode(",", $user['perms']) as $perm)    
                                <span class="alert alert-info p-1">{!! trans('messages.perm_'.$perm) !!}</span>
                                @endforeach
                            </td>
                            <td class="align-middle">
                                <a class="btn btn-danger btn-sm delete_user" href="{{ route('admin.usuarios.borrar', $user['id']) }}">{!! trans('messages.delete') !!}</a>
                                <a class="btn btn-success btn-sm" href="{{ route('admin.usuarios.editar', $user['id']) }}">{!! trans('messages.edit') !!}</a>
                            </td>
                        </tr> 
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p>{!! trans('messages.noshops') !!}</p>
    @endif
    <h2 id="newuser">{!! trans('messages.createuser') !!}</h2>
    @if (isset($user_created) && $user_created)
        <div class="alert alert-success" role="alert">
        {!! trans('messages.createuserok') !!}
        </div>
    @elseif (isset($user_created) && !$user_created)
        <div class="alert alert-danger" role="alert">
        {!! trans('messages.emailexists') !!}
        </div>
    @endif
    <form method="post" action="{{ route('admin.usuarios.crear') }}">
        @csrf <!-- {{ csrf_field() }} -->
        <div class="row mb-3">
            <div class="col-md-6 form-group">
                <label for="inputName">{!! trans('messages.name') !!} *</label>
                <input type="text" class="form-control" id="inputName" name="username" value="" required>
            </div>
            <div class="col-md-6 form-group position-relative">
                <label for="inputEmail">{!! trans('messages.email') !!} *</label>
                <input type="email" class="form-control" id="inputEmail" name="useremail" value="" required>
            </div>
            <div class="col-md-6 form-group position-relative">
                <label for="inputpassword">{!! trans('messages.password') !!} *</label>
                <input type="password" class="form-control" id="inputPassword" minlength="8" autocomplete="false" name="userpassword" value="" required>
            </div>
            <div class="col-md-6 form-group">
                {!! trans('messages.perms') !!} *<br/>
                @foreach ($perms as $perm)
                    <label>
                        <input type="checkbox" class="permcheckboxes" id="inputPerms{{ $perm }}" name="userperms[]" 
                            value="{{ $perm }}" required> {!! trans('messages.perm_'.$perm) !!}
                    </label><br/>
                @endforeach 
            </div>
        </div>
        <input type="submit" value="{!! trans('messages.createuser') !!}" class="btn btn-primary mb-2">
    </form>
@stop
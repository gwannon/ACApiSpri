@extends('layouts.app')
 
@section('title', 'Admin')

@section('content')
<h2 id="newuser">{!! trans('messages.useredit') !!}</h2>
    @if (request()->get('edit') == 'ok')  
        <div class="alert alert-success" role="alert">
            {!! trans('messages.edituserok') !!}
        </div>
    @endif
  <form method="post" action="{{ route('admin.usuarios.editar', $user['id']) }}">
      @csrf <!-- {{ csrf_field() }} -->
      <div class="row mb-3">
          <div class="col-md-6 form-group">
              <label for="inputName">{!! trans('messages.name') !!} *</label>
              <input type="text" class="form-control" id="inputName" name="username" value="{{ $user['name'] }}" required>
          </div>
          <div class="col-md-6 form-group position-relative">
              <label for="inputEmail">{!! trans('messages.email') !!} *</label>
              <input type="email" class="form-control" id="inputEmail" name="useremail" value="{{ $user['email'] }}" required>
          </div>
          <div class="col-md-6 form-group position-relative">
              <label for="inputLastName">{!! trans('messages.password') !!} *</label>
              <input type="password" class="form-control" id="inputLastName" minlength="8" autocomplete="false" name="userpassword" minlength="8" placeholder="{{ trans('messages.filltochange') }}" value="">
          </div>
          <div class="col-md-6 form-group">
              <label for="inputPerms">{!! trans('messages.perms') !!} *</label>
              <input type="text" class="form-control" id="inputPerms" name="userperms" value="{{ $user['perms'] }}" required>
          </div>
      </div>
      <input type="submit" value="{!! trans('messages.edit') !!}" class="btn btn-primary mb-2">
      <a class="btn btn-success mb-2" href="{{ route('admin.usuarios') }}">{!! trans('messages.back') !!}</a>
  </form>
@stop
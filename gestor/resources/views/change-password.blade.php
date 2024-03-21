@extends('layouts.app')
@section('maintitle')
    <h1>{!! trans('messages.changepassword') !!}</h2>
@stop
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{!! trans('messages.changepassword') !!}</div>

                    <form action="{{ route('update-password') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @elseif (session('error'))
                                <div class="alert alert-danger" role="alert">
                                    {{ session('error') }}
                                </div>
                            @endif

                            <div class="mb-3">
                                <label for="oldPasswordInput" class="form-label">{!! trans('messages.oldpassword') !!}</label>
                                <input name="old_password" type="password" class="form-control @error('old_password') is-invalid @enderror" id="oldPasswordInput"
                                    placeholder="{!! trans('messages.oldpassword') !!}">
                                @error('old_password')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="newPasswordInput" class="form-label">{!! trans('messages.newpassword') !!}</label>
                                <input name="new_password" type="password" class="form-control @error('new_password') is-invalid @enderror" id="newPasswordInput"
                                    placeholder="{!! trans('messages.newpassword') !!}">
                                @error('new_password')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="confirmNewPasswordInput" class="form-label">{!! trans('messages.repeatnewpassword') !!}</label>
                                <input name="new_password_confirmation" type="password" class="form-control" id="confirmNewPasswordInput"
                                    placeholder="{!! trans('messages.repeatnewpassword') !!}">
                            </div>

                        </div>

                        <div class="card-footer">
                            <button class="btn btn-success">{!! trans('messages.changepassword') !!}</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
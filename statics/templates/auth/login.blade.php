@extends('layout')
@section('main')
<div class='d-flex justify-content-between' style='height:100%;'>
    <div class='text-center mt-5 pt-5' style='width: 40%; height: calc(100vh - 100px)'>
        <h1 class='text-center text-muted'>{{ $app_name }}</h1>
        <form method='POST' class='m-5' style='padding: 70px; margin-top:'> 
            {!! $csrf !!}
            <h4 class='mb-5'>Zaloguj się</h4>
            <input type='email' name='email' placeholder='Email (imię.nazwisko@silcare.com)' class='form-control mt-4 @if($error) is-invalid @endif'/>
            <input type='password' name='token', placeholder='Token' class='form-control mt-4 @if($error) is-invalid @endif'/>
            @if($error)
                <br/>
                <small class='text-danger'>Nie udało się zalogować</small>
            @endif
            <input type='submit' class='btn btn-primary mt-4' style='width: 100%'/>
            <br/><br/>
            <a href="http://192.168.0.234/token-reminder" target="_blank">Nie pamiętam tokenu</a>
        </form>
    </div>
    <div id="login-image">
        <img src='{{ $app_path }}/statics/resources/login-bg.jpg' alt='Login Background Image'/>
    </div> 
</div>
@endsection
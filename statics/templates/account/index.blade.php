@extends('layout')
@section('main')
<div id='account'>
    <h3 class='text-center'>Twoje konto</h3>
    <div class='border mx-auto p-5'>
        <h4><i class='fa fa-user mr-3'></i>{{ $user->fname }} {{ $user->lname }}</h4>
        <br/>
        <p><b>Rola:</b> {{ $user->role->name }}</p>
        <p><b>Email:</b> {{ $user->email }}</p>
        <p><b>Ilość zamówień powiązanych:</b> {{ $orders_num }}</p>
        <hr/>
        <form method='POST'>
            {!! $csrf !!}
            <b>Zarządzaj kontem</b>
            <br/><br/>
            <label>
                <input type='checkbox' name='messages_on' @if($app_user->messages_allow) checked @endif/>
                Powiadomienia mailowe
            </label>
            <br/><br/>
            <input type='submit' class='btn btn-primary'/>
        </form>
        <div>
            <br/>
            <b>Nieprzeczytane wiadomości</b>
            <br/>
            @if($unreaded_messages)
                @foreach($unreaded_messages as $m)
                    <button type="button" data-target="{{ $app_path }}/orders/single?id={{ $m->order_id }}" data-id="{{ $m->id }}" class='btn btn-danger text-light text-left border unread-message m-2 p-3' style="width: 100%">
                        <div class="d-flex justify-content-between">
                            <p class='text-light'>{{ $m->get_author() }}</p>
                            <span class='text-light'>zamówienie #{{ $m->order_id }}</span>
                        </div>
                        <br/>
                        <span style="white-space: pre-line">{!! $m->content !!}</span>
                    </button>
                @endforeach
            @else
                <p class="text-center text-muted mt-3">BRAK</p>
            @endif
        </div>
    </div>
</div>
@endsection
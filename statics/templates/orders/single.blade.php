@extends('layout')
@section('main')
<div style='padding: 100px; padding-bottom: 200px;'>
    <div class='mb-5'>
            <div class='d-flex justify-content-between'>
                <h1>Zlecenie #{{ $order->id }}</h1>
                @if($_SESSION['import_auth']->role->name != 'Zwykły użytkownik')
                    <a href="{{ $app_path }}/dashboard/orders/single?id={{ $order->id }}">
                        <i class='fa fa-eye mr-2'></i>
                        Zobacz w panelu edycji
                    </a>
                @endif
            </div>        <div class='d-flex justify-content-between'>
            <div>Status: <b class='blue-v2'>{{ $order->status->name }}</b></div>
            <div>Priorytet: <b class='blue-v2'>{{ $order->priority->name }}</b></div>
            @if($order->stage) 
                <div>Etap: <b class='blue-v2'>{{ $order->stage->name }}</b></div>
            @endif
            @if($order->get_paytime_date()) 
                <div>Planowana data płatności: <b class='blue-v2'>{{ substr($order->get_paytime_date(), 0, 10) }}</b></div>
            @endif
            <div>Wykonawca: <b class='blue-v2'>{{ $order->last_user ? $order->last_user->external_user->first_name.' '.$order->last_user->external_user->last_name : "BRAK" }}</b></div>
        </div>
        <div class='p-5 border mb-2'>
            {!! html_entity_decode($order->message) !!}
        </div>
        <span class='text-muted'>Zgłaszający: {{ isset($order->user->external_user) ? $order->user->external_user->first_name.' '.$order->user->external_user->last_name : "Unknow" }}</span>
        <div class='d-flex justify-content-between mt-5'>
            <div>
                <div>Planowany termin realizacji: <b>{{ $order->get_scheduled_datetime() ? $order->get_scheduled_datetime() : "BRAK" }}</b></div>
                <div>Data wysłania zgłoszenia: <b>{{ $order->created_at }}</b></div>
                <div>Data rozpoczęcia zgłoszenia: <b>{{ $order->get_started_datetime() ? $order->get_started_datetime() : "BRAK" }}</b></div>
                <div>Data zakończenia zgłoszenia: <b>{{ $order->get_ended_datetime() ? $order->get_ended_datetime() : "BRAK" }}</b></div>
            </div>
            <div>
                @foreach($order->files as $file)
                    <br/>
                    <a href="{{ $app_path }}/files?id={{ $file->id }}" download><i class='fa fa-file mr-2'></i> {{ $file->name }}</a>
                @endforeach
            </div>
        </div>
    </div>

    <hr/>
    <div>
        <h3 class='mt-3 mb-5'>Komentarze</h3>
        @foreach($order->comments as $comment)
            <div class="comment">
                <span class='text-muted'>{{ isset($comment->user->external_user) ? $comment->user->external_user->first_name.' '.$comment->user->external_user->last_name : "Unknow" }} {{ $comment->created_at }}</span>
                <div class='px-3 pt-2'>
                    @if(in_array($comment->order_history_id, [7,11,12,13,14]))
                        {{ $comment->order_history->name }} na {{ $comment->content }}
                    @elseif($comment->order_history_id == 2)
                        {{ $comment->order_history->name }} dla {{ $comment->content }}
		    @elseif($comment->order_history_id == 3)
			{{ $comment->order_history->name }} @if($comment->content) <br/>- {{ $comment->content }} @endif
                    @elseif($comment->order_history_id != 0)
                        {{ $comment->order_history->name }}
                    @else
                        <span style="white-space: pre-line">{!! $comment->content !!}</span>
                    @endif
                    @foreach($comment->files as $file)
                        <br/>
                        <a href="{{ $app_path }}/files?id={{ $file->id }}" download><i class='fa fa-file mr-2'></i> {{ $file->name }}</a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
    @if($display_forms)
        <div class='mb-5 pb-5'>
            <form method='POST' action='{{ $app_path }}/orders/comments/add?order_id={{ $order->id }}' enctype='multipart/form-data' class='comment-form'>
                <b>Dodaj komentarz</b>
                {!! $csrf !!}
                <textarea name="content" class="form-control mt-2" style="height: 300px;" placeholder='Dodaj komentarz'></textarea>
		        <br/>
                <div id="content">
        	    <input type="file" name="files[]" id="filer_input2" multiple="multiple">
    		</div>

                <input type='submit' class='btn btn-primary mt-3' value='Aktualizuj'/>
            </form>
        </div>
    @endif
</div>
@endsection
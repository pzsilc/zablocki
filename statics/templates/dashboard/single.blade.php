@extends('layout')
@section('main')

<div style='padding: 100px; padding-bottom: 200px;'>
    <a class="float-right" href="{{ $app_path }}/orders/pdf-generate?order_id={{ $order->id }}"><i class="fa fa-file mr-2"></i>Pobierz PDF</a>
    <div class='mb-5'>
        <h1 class='mb-5'>Zlecenie #{{ $order->id }}</h1>
        <div class='d-flex justify-content-between'>
            <div>Status: <b class='blue-v2'>{{ $order->status->name }}</b></div>
            <div>Priorytet: <b class='blue-v2'>{{ $order->priority->name }}</b></div>
            @if($order->stage) 
                <div>Etap: <b class='blue-v2'>{{ $order->stage->name }}</b></div>
            @endif
            @if($order->get_paytime_date()) 
                <div>Planowana data płatności: <b class='blue-v2'>{{ substr($order->get_paytime_date(), 0, 10) }}</b></div>
            @endif
            @if($order->origin)
                <div>Źródło: <b class='blue-v2'>{{ $order->origin->name }}</b></div>
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
                    <a href="{{ $app_path }}/files?id={{ $file->id }}"><i class='fa fa-file mr-2'></i> {{ $file->name }}</a>
                @endforeach
            </div>
        </div>
    </div>

    @if($user->role->id === 2 || $user->role->id === 4 || $user->role->id === 5 || $user->id === $order->last_user_id)
        @if($display_controls_form)
            <div>
                <hr/>
                <form method="POST" action="{{ $app_path }}/dashboard/orders/proceed?id={{ $order->id }}" enctype="multipart/form-data" class="mt-5">
                    {!! $csrf !!}
                    <h4 class="mb-4">Przetwarzanie</h4>
                    <div class='d-flex justify-content-between'>
                        <div>
                            <label>
                                <input type="checkbox" name="close-order"/>
                                Zakończ zlecenie
                            </label>
                            <br/>
                            <label>
                                <input type="checkbox" name="reject-order"/>
                                Odrzuć zlecenie
                            </label>
                            <br/>
                            <label>
                                <input type="checkbox" name="inappropriate-order"/>
                                Niestosowne zlecenie
                            </label>
                            @if($order->status_id == 2 || $order->status_id == 4 || $order->status_id == 6)
                                @if($order->stage_id != 4)
                                    <br/>
                                    <label>
                                        <input type="checkbox" name="next-stage"/>
                                        Wejdź w etap <b>{{ Stage::get($order->stage_id + 1)->name }}</b>
                                    </label>
                                    @if($order->stage_id == 0)
                                        <br/>
                                        <label>
                                            <input type='checkbox' name='skip-to-logistic'/>
                                            Wejdź w etap logistyki
                                        </label>
                                    @endif
                                @else
                                    <br/>
                                    <b>Zamówienie {{ $order->stage->name }}</b>
                                @endif
                            @endif
                        </div>
                        <div>
                            <label style='width: 300px;'>
                                Termin realizacji:
                                <input type="date" name="execution-date" class="form-control"/>
                            </label>
                            <br/>
                            <label style='width: 300px;'>
                                Priorytet:
                                <select name="priority_id" class="form-control">
                                    @foreach($priorities as $priority)
                                        <option value="{{ $priority->id }}" {{ $priority->id == $order->priority->id ? "selected" : '' }}>{{ $priority->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <br/>
                            <label style='width: 300px;'>
                                Źródło:
                                <select name="origin-id" class="form-control">
                                    @foreach($origins as $origin)
                                        <option value="{{ $origin->id }}" {{ $origin->id == $order->origin_id ? "selected" : '' }}>{{ $origin->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    </div>
                    <input type="submit" class="btn btn-primary mt-2" value="Przetwarzaj"/>
                </form>

                <form method='POST' action='{{ $app_path }}/dashboard/orders/stage-dates?id={{ $order->id }}' class='mt-5'>
                    <hr/>
                    {!! $csrf !!}
                    <h4 class="mb-2">Ustaw planowane daty dla poszczególnych etapów</h4>
                    <div class='container'>
                        <div class='row'>
                            <label class='col-6'>
                                Produkcja
                                <input type="date" class='form-control' name='production_date' value='{{ $order->stage_dates["production"] }}'/>
                            </label>
                            <label class='col-6'>
                                Logistyka
                                <input type="date" class='form-control' name='logistic_date' value='{{ $order->stage_dates["logistic"] }}'/>
                            </label>
                            <label class='col-6'>
                                Transport
                                <input type="date" class='form-control' name='transport_date' value='{{ $order->stage_dates["transport"] }}'/>
                            </label>
                            <label class='col-6'>
                                Reklamacja
                                <input type="date" class='form-control' name='complaint_date' value='{{ $order->stage_dates["complaint"] }}'/>
                            </label>
                        </div>
                    </div>
                    <input type="submit" value='Ustaw' class="btn btn-primary mt-2"/>
                </form>

                @if($user->role->id === 2 || $user->role->id === 4 || $user->role->id === 5)
                    <form method="POST" action="{{ $app_path }}/dashboard/orders/order-execution?id={{ $order->id }}" class="mt-5">
                        <hr/>
                        {!! $csrf !!}
                        <h4 class="mb-2">Zleć wykonanie</h4>
                        <select name="executor_id" class="form-control my-2">
                            @foreach($executors as $executor)
                                <option value="{{ $executor->id }}">{{ $executor->external_user->first_name }} {{ $executor->external_user->last_name }}</option>
                            @endforeach
                        </select>
                        <input type="submit" class="btn btn-primary" value='Zleć'/>
                    </form>
                @endif
                    <form method="POST" action="{{ $app_path }}/dashboard/orders/order-transaction?id={{ $order->id }}" class="mt-5">
                        <hr/>
                        {!! $csrf !!}
                        <h4 class="mb-2">Zleć ustalenie terminu zapłaty</h4>
                        <textarea name="content" class="form-control my-2" placeholder="Zawęź do pozycji"></textarea>
                        <input type="submit" class="btn btn-primary" value='Zleć'/>
                    </form> 
 
                    <form method="POST" action="{{ $app_path }}/dashboard/orders/management-request?id={{ $order->id }}" class="mt-5">
                        <hr/>
                        {!! $csrf !!}
                        <h4 class="mb-2">Wystąp o zgodę zarządu</h4>
                        <input type="submit" class="btn btn-primary" value="Wystąp"/>
                    </form>
            </div>
        @else
            <b>{{ $message }}</b>
        @endif
        @if(in_array($order->status_id, [5,6]) && $user->role->id == 2)
            <form method='POST' action="{{ $app_path }}/dashboard/orders/proceed?id={{ $order->id }}">
                {!! $csrf !!}
                <label>
                    <input type="checkbox" checked name="close-order"/>
                    Zakończ zlecenie
                </label>
                <input type='submit' class='btn btn-primary'/>
            </form>
        @endif
    @endif

    @if(($order->status_id == 5 || $order->status_id == 6) && $user->role->id == 4)
        <form method='POST' action='{{ $app_path }}/dashboard/orders/management-accept?id={{ $order->id }}' class='mt-5'>
            {!! $csrf !!}
            <input type='submit' value='Zaakceptuj' class='btn btn-primary' name='action'/>
            <input type="submit" value='Odrzuć' class='btn btn-danger' name='action'/>
        </form>
    @endif

    @if($order->status_id == 10 && $user->role->id == 6)
        <form method='POST' action='{{ $app_path }}/dashboard/orders/paytime-settings?id={{ $order->id }}' class='mt-5'>
            {!! $csrf !!}
            <label>
                Ustaw datę płatności
                <input type='date' class='form-control' name='paytime' required/>
            </label>
            <br/>
            <input type='submit' class='btn btn-primary' name='action'/>
        </form>    
    @endif

    <hr/>
    <div>
        <h3 class='mt-3 mb-5'>Komentarze</h3>
        @foreach($order->comments as $comment)
            <div class="comment">
                <div class='d-flex justify-content-between'>
		    <span class='text-muted'>{{ isset($comment->user->external_user) ? $comment->user->external_user->first_name.' '.$comment->user->external_user->last_name : "Unknow" }}, {{ $comment->created_at }}</span>
        	    @if(!$comment->order_history_id && ($user->role->id == 2 || $user->role->id == 5))
			<form method="POST" action="{{ $app_path }}/orders/comments/delete">
			    {!! $csrf !!}
			    <input type="hidden" name="comment_id" value="{{ $comment->id }}"/>
			    <input type="hidden" name="order_id" value="{{ $order->id }}"/>
			    <button class='btn btn-default text-danger'><i class="fa fa-trash"></i></button>
			</form>
		    @endif
		</div>
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
                    <div>
                        @foreach($comment->files as $file)
                            <br/>
                            <a href="{{ $app_path }}/files?id={{ $file->id }}" download><i class="fa fa-file mr-2"></i>{{ $file->name }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @if($display_comment_form)
        <div>
            <form method='POST' action='{{ $app_path }}/orders/comments/add?order_id={{ $order->id }}&admin=true' enctype='multipart/form-data' class='comment-form'>
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
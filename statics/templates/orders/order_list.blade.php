@extends('layout')
@section('main')
    <div  class='mx-auto mt-5 pt-5' style='width: 80%;'>
        <h1>Lista twoich zgłoszeń</h1>
        <form method="GET" class='mt-5' id="filters">
            <h3 class='mb-3'>Filtruj</h3>
	    <label>
		<p>Numer zamowienia</p>
		<input type='text' name='number' value='{{ $filters["number"] }}' class='form-control'/>
	    </label>
            <label>
                <p>Status</p>
                <select name="status_id" class="form-control">
                    <option value="">Wybierz...</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}" @if($status->id == $filters['status_id']) selected @endif>
                            {{ $status->name }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label>
                <p>Priorytet</p>
                <select name="priority_id" class="form-control">
                    <option value="">Wybierz...</option>
                    @foreach($priorities as $priority)
                        <option value="{{ $priority->id }}" @if($priority->id == $filters['priority_id']) selected @endif>
                            {{ $priority->name }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label>
                <p>Etap</p>
                <select name="stage_id" class="form-control">
                    <option value="">Wybierz...</option>
                    @foreach($stages as $stage)
                        <option value="{{ $stage->id }}" @if($stage->id == $filters['stage_id']) selected @endif>
                            {{ $stage->name }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label>
                <p>Szukaj w treści</p>
                <input type="text" name="message" class='form-control' value="{{ $filters['message'] }}"/>
            </label>
            <div class=''>
                <input type='submit' class='btn btn-primary' value='Filtruj'/>
                <a href='{{ $app_path }}/orders/list' class='btn btn-danger text-light'>Resetuj</a>
            </div>
        </form>
        <div class='orders-list-table-div'>
            <table class='table table'>
                <thead>
                    <tr>
                        <th>ID</th>
			<th>Opis</th>
                        <th>Data dodania</th>
                        <th>Status</th>
                        <th>Priorytet</th>
                        <th>Etap</th>
                        <th>Twoja rola</th>
                    </tr>
                </thead>
                <tbody>
                    @if($orders)
                        @foreach($orders as $order)
                            <tr>
                                <td><a href="{{ $app_path }}/orders/single?id={{ $order->id }}">{{ $order->id }}</a></td>
				<td><span style="font-size: 10px;">{!! html_entity_decode($order->message) !!}</span></td>
                                <td>{{ $order->created_at }}</td>
                                <td>{{ $order->status->name }}</td>
                                <td>{{ $order->priority->name }}</td>
                                <td>{{ $order->stage ? $order->stage->name : '' }}</td>
                                <td>{{ $order->user_id === $user->id ? 'Utworzenie' : 'Wykonanie' }}</td>
                            </tr>
                        @endforeach
                    @else
                        <p>Brak zamówień</p>
                    @endif
                </tbody>
            </table>
        </div>
	<div class='mb-5 pb-5'>
	    <ul class="pagination pagination-sm">
		@if($page > 1)
		    <li class="page-item"><a class="page-link" href="{{ $app_path }}/orders/list?{{ $parameters }}"><<</a></li>
		@endif
		@if($page - 1 > 0)
		    <li class="page-item"><a class="page-link" href="{{ $app_path }}/orders/list?{{ $parameters }}&page={{ $page - 1 }}">{{ $page - 1 }}</a></li>
		@endif
		<li class="page-item"><a class="page-link" href="{{ $app_path }}/orders/list?{{ $parameters }}&page={{ $page }}">{{ $page }}</a></li>
		@if($page + 1 < $total_num_of_pages)
		    <li class="page-item"><a class="page-link" href="{{ $app_path }}/orders/list?{{ $parameters }}&page={{ $page + 1 }}">{{ $page + 1 }}</a></li>
		@endif
		@if($page < $total_num_of_pages)
		    <li class="page-item"><a class="page-link" href="{{ $app_path }}/orders/list?{{ $parameters }}&page={{ $total_num_of_pages }}">>></a></li>
		@endif
	    </ul>
	</div>
    </div>
@endsection
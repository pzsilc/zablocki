@extends('layout')
@section('main')
<div class='mx-auto' style='width: 80%;'>
    <h1 class=mt-5>Lista zgłoszeń bieżących</h1>
    <div>
        <form id='filters'>
            <h4 class='pt-5 pb-3'>Filtry</h4>
	    <label>	
	        <p>Numer zamowienia</p>
	        <input type='text' name='number' class='form-control' value="{{ $filters['number'] }}"/>
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
                <p>Szukaj w treści</p>
                <input type="text" name="message" class='form-control' value="{{ $filters['message'] }}"/>
            </label>
            <div class='text-center'>
                <input type='submit' class='btn btn-primary'/>
                <a href="{{ $app_path }}/dashboard/archival" class="btn btn-danger text-light">Resetuj</a>
            </div>
        </form>
    </div>

    <div class='orders-list-table-div'>
        <table class='table mt-5'>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Treść zgłoszenia</th>
                    <th>Data złożenia</th>
                    <th>Status</th>
                    <th>Priorytet</th>
                </tr>
            </thead>
            <tbody>
                @if($orders)
                    @foreach($orders as $order)
                        <tr 
                            @if($order->scheduled_date)
                                @if((strtotime($order->scheduled_date) < strtotime(date('Y-m-d'))) && !in_array(intval($order->status_id), [3,8,9]))
                                    class="bg-danger"
                                @elseif((strtotime(date('Y-m-d', strtotime('+3 days'))) > strtotime(date($order->scheduled_date))) && !in_array(intval($order->status_id), [3,8,9]))
                                    class="bg-warning"
                                @endif
                            @endif 
                        >
                            <td><a href="{{ $app_path }}/dashboard/orders/single?id={{ $order->id }}">{{ $order->id }}</a></td>
                            <td><span style='font-size: 10px;'>{!! html_entity_decode($order->message) !!}</span></td>
                            <td>{{ $order->created_at }}</td>
                            <td style='background: {{ $order->status->color }};'>{{ $order->status->name }}</td>
                            <td>{{ $order->priority->name }}</td>
                        </tr>
                    @endforeach
                @else
                    <p>Brak zamówień</p>
                @endif
            </tbody>
        </table>
    </div>

    <div >
        <ul class="pagination mb-5 pb-5">
            @if($page - 1 >= 1)
                <li class="page-item"><a class="page-link" href="{{ $app_path }}/dashboard/archival?{{ $parameters }}"><<</a></li>
                <li class="page-item"><a class="page-link" href="{{ $app_path }}/dashboard/archival?{{ $parameters }}&page={{ $page-1 }}">{{ $page-1 }}</a></li>
            @endif
            <li class="page-item"><a class="page-link" href="{{ $app_path }}/dashboard/archival?{{ $parameters }}&page={{ $page }}">{{ $page }}</a></li>
            @if($page + 1 <= $total_num_of_pages)
                <li class="page-item"><a class="page-link" href="{{ $app_path }}/dashboard/archival?{{ $parameters }}&page={{ $page+1 }}">{{ $page+1 }}</a></li>
                <li class="page-item"><a class="page-link" href="{{ $app_path }}/dashboard/archival?{{ $parameters }}&page={{ $total_num_of_pages }}">>></a></li>
            @endif
        </ul>
    </div>
</div>

@endsection
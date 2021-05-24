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
            @if($user_role_id === 2 || $user_role_id === 5)
                <label>
                    <p>Zgłaszający</p>
                    <select name="user_id" class="form-control">
                        <option value="">Wybierz...</option>
                        @foreach($users as $user)
                            @if($user->external_user)
                                <option value="{{ $user->id }}" @if($user->id == $filters['user_id']) selected @endif>
                                    {{ $user->external_user->first_name }} {{ $user->external_user->last_name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </label>
                <label>
                    <p>Wykonawca</p>
                    <select name="last_user_id" class="form-control">
                        <option value="">Wybierz...</option>
                        @foreach($executors as $executor)
                            <option value="{{ $executor->id }}" @if($executor->id == $filters['last_user_id']) selected @endif>
                                {{ $executor->external_user->first_name }} {{ $executor->external_user->last_name }}
                            </option>
                        @endforeach
                    </select>
                </label>
            @endif
            @if(!in_array($user_role_id, [6, 4]))
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
            @endif
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
            <div class='text-center'>
                <input type='submit' class='btn btn-primary'/>
                <a href="{{ $app_path }}/dashboard" class="btn btn-danger text-light">Resetuj</a>
            </div>
        </form>
    </div>

    <div class='orders-list-table-div'>
        <table class='table mt-5'>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Zgłaszający</th>
                    <th>Treść zgłoszenia</th>
                    <th>Data złożenia</th>
                    <th>Status</th>
                    <th>Etap</th>
                    <th>Wykonawca</th>
                    <th>Planowany termin zapłaty</th>
                    <th>Planowany termin realizacji</th>
                    <th>Priorytet</th>
                    <th>Adnotacje</th>
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
                            <td>{{ isset($order->user->external_user) ? $order->user->external_user->first_name.' '.$order->user->external_user->last_name : "Unknow" }}</td>
                            <td><span style="font-size: 11px;">{!! html_entity_decode($order->message) !!}</span></td>
                            <td>{{ $order->created_at }}</td>
                            <td style='background: {{ $order->status->color }};'>{{ $order->status->name }}</td>
                            <td>{{ $order->stage ? $order->stage->name : '' }}</td>
                            <td>{{ $order->last_user ? $order->last_user->external_user->first_name.' '.$order->last_user->external_user->last_name : '' }}</td>
                            <td>{{ $order->get_paytime_date() }}</td>
                            <td>{{ $order->scheduled_date }}</td>
                            <td>{{ $order->priority->name }}</td>
                            <td>
                                @if($order->scheduled_date && (strtotime($order->scheduled_date) < strtotime(date('Y-m-d'))) && !in_array(intval($order->status_id), [3,8,9]))
                                    {{ intval((strtotime(date('Y-m-d'))-strtotime($order->scheduled_date)) / 86400) }} dni opóźnienia
                                @endif
                            </td>
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
                <li class="page-item"><a class="page-link" href="{{ $app_path }}/dashboard?{{ $parameters }}"><<</a></li>
                <li class="page-item"><a class="page-link" href="{{ $app_path }}/dashboard?{{ $parameters }}&page={{ $page-1 }}">{{ $page-1 }}</a></li>
            @endif
            <li class="page-item"><a class="page-link" href="{{ $app_path }}/dashboard?{{ $parameters }}&page={{ $page }}">{{ $page }}</a></li>
            @if($page + 1 <= $total_num_of_pages)
                <li class="page-item"><a class="page-link" href="{{ $app_path }}/dashboard?{{ $parameters }}&page={{ $page+1 }}">{{ $page+1 }}</a></li>
                <li class="page-item"><a class="page-link" href="{{ $app_path }}/dashboard?{{ $parameters }}&page={{ $total_num_of_pages }}">>></a></li>
            @endif
        </ul>
    </div>
</div>

@endsection
@foreach($messages as $message)
    @if($message['type'] == 'success')
        <div class="btn btn-success message m-success">{{ $message['text'] }}</div>
    @elseif($message['type'] == 'error') 
        <div class="btn btn-danger message m-error">{{ $message['text'] }}</div>
    @elseif($message['type'] == 'info')
        <div class="btn btn-info message m-info">{{ $message['text'] }}</div>
    @endif
@endforeach
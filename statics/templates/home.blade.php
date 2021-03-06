@extends('layout')
@section('main')

<div id='add-order-form'>
    <h2>Dodaj zlecenie</h2>
    <hr/><br/>
    <form method='POST' enctype="multipart/form-data" action="{{ $app_path }}/orders/add">
        {!! $csrf !!}
        <textarea 
            name="_description" 
            class="form-control" 
            placeholder="Opis zamówienia --- uprasza się o dokładny opis"
        ></textarea>
        <small class='text-muted'>wymagane</small>
        <div id="content">
            <input type="file" name="files[]" id="filer_input2" multiple="multiple">
        </div>
        <input type="submit" class="btn btn-primary mt-5"/>
        <p class='text-muted mt-5 mb-5 pb-5'>* - zlecenie powinno zawierać dokładny opis potrzebnych przedmiotów. Możesz dodać linki lub zdjęcie.</p>
        <br/>
    </form>
</div>

@endsection
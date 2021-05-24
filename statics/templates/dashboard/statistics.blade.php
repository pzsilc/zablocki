@extends('layout')
@section('main')

<div id='statistics' class='mx-auto mt-5 text-center' style='width: 80%; margin-bottom: 200px;'>
    <h1 class='mt-5 ml-5'>Statystyka</h1>
    <div id='content' class='d-none'>
        <div class='container mt-5 pt-5'>
            <div class='row'>
                <div class='col-12 col-lg-6 text-center'>
                    <canvas id="start-chart"></canvas>
                    <span>wykres czasów rozpoczęcia</span>
                </div>
                <div class='col-12 col-lg-6 mt-5 pt-5 mt-lg-0 pt-lg-0 text-center'>
                    <canvas id="end-chart"></canvas>
                    <span>wykres czasów zakończenia</span>
                </div>
            </div>
        </div>
        <div class='container'>
            <div class='row justify-content-md-center'>
                <div class='col-12 col-lg-7 mt-5 pt-5 text-center'>
                    <canvas id="total-chart"></canvas>
                    <span>wykres całkowitych czasów trwania zamówienia</snap>
                </div>
            </div>
        </div>
        <div class='mt-5 pt-5' id='additional-info'></div>
        <a href="{{ $app_path }}/dashboard/statistics/generate-xlsx">Generuj plik xlsx</a>
    </div>
    <div id='loader' style="margin-top: 300px; margin-bottom: 700px;">
        <div class='h1 blue-v2'><i class="fas fa-circle-notch fa-spin"></i></div>
        Generowanie statystyki...
    </div>
</div>

@endsection
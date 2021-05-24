<!<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>{{ $app_name }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">    
    <link mhref="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" mrel="stylesheet">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css'/>
    <link href="{{$app_path }}/statics/jQuery.filer-1.3.0/css/jquery.filer.css" type="text/css" rel="stylesheet" />
    <link href="{{$app_path }}/statics/jQuery.filer-1.3.0/css/themes/jquery.filer-dragdropbox-theme.css" type="text/css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ $app_path }}/statics/css/app.css"/>
</head>

<body id="page-top">
        <header>
            @if(isset($_SESSION['import_auth']))
                <div class='bg-dark text-light text-center pt-5'>
                    <h2 class='brand'>{{ $app_name }}</h2>
                    <div class='mt-5'>
                        <hr/>
                        <div class='options-list text-left ml-4 pb-5'>
                            <a href="{{ $app_path }}/"><i class="fa fa-plus mr-2"></i>DODAJ ZLECENIE</a>
                            @if($_SESSION['import_auth']->role->name === 'Zwykły użytkownik')
                                <a href="{{ $app_path }}/orders/list"><i class="fa fa-list mr-2"></i>TWOJE ZAMÓWIENIA</a>
                            @elseif($_SESSION['import_auth']->role->name === 'Wykonawca' || $_SESSION['import_auth']->role->name === 'Wykonawca 2')
                                <a href="{{ $app_path }}/dashboard"><i class="fa fa-list mr-2"></i>PANEL WYKONAWCY</a>
                                <a href="{{ $app_path }}/dashboard/archival"><i class="fa fa-archive mr-2"></i>ARCHIWUM</a>
                            @elseif($_SESSION['import_auth']->role->name === 'Administrator')
                                <a href="{{ $app_path }}/dashboard"><i class="fa fa-database mr-2"></i>PANEL ADMINA</a>
                                <a href="{{ $app_path }}/dashboard/statistics"><i class="fa fa-window-restore mr-2"></i>STATYSTYKI</a>
                                <a href="{{ $app_path }}/dashboard/archival"><i class="fa fa-archive mr-2"></i>ARCHIWUM</a>
                            @elseif($_SESSION['import_auth']->role->name === 'Zarząd')
                                <a href="{{ $app_path }}/dashboard"><i class="fa fa-database mr-2"></i>PANEL ZARZĄDCY</a>
                                <a href="{{ $app_path }}/dashboard/statistics"><i class="fa fa-window-restore mr-2"></i>STATYSTYKI</a>
                                <a href="{{ $app_path }}/dashboard/archival"><i class="fa fa-archive mr-2"></i>ARCHIWUM</a>
                            @elseif($_SESSION['import_auth']->role->name === 'Super Administrator')
                                <a href="{{ $app_path }}/orders/list"><i class="fa fa-list mr-2"></i>TWOJE ZAMÓWIENIA</a>
                                <a href="{{ $app_path }}/dashboard"><i class="fa fa-database mr-2"></i>PANEL SUPERADMINA</a>
                                <a href="{{ $app_path }}/dashboard/statistics"><i class="fa fa-window-restore mr-2"></i>STATYSTYKI</a>
                                <a href="{{ $app_path }}/dashboard/archival"><i class="fa fa-archive mr-2"></i>ARCHIWUM</a>
                                ???
                            @endif
                            <a href="{{ $app_path }}/account" class="d-flex justify-content-between">
				<span><i class="fa fa-user mr-2"></i>TWOJE KONTO</span>
				@if($messages_num)
				    <div class="bg-danger mr-2 text-light px-1">{{ $messages_num }}</div>
				@endif
			    </a>
                            <a href="{{ $app_path }}/logout"><i class="fa fa-sign-out-alt mr-2"></i>WYLOGUJ SIĘ</a>
                        </div>
                        <hr/>
                        <div class='mt-5 pt-5'>
                            <small class='text-muted font-weight-bold float-left ml-3'>Użytkownik</small><br/><br/>
                            <span class='user-info'>{{ $_SESSION['import_auth']->fname }} {{ $_SESSION['import_auth']->lname }}</span>
                            <br/>
                            <small class='user-info'>Rola: {{ $_SESSION['import_auth']->role->name }}</small>
                        </div>
                    </div>
                <div>
            @endif
            @include('messages.index')
        </header>
        <main class="mt-5">
            @yield('main')
        </main>
        <footer class='text-muted'>
            <div class="container my-auto">
                <div class="copyright text-center my-auto">
                    <span>Copyright &copy; Silcare {{ date('Y') }}</span>
                </div>
            </div>
        </footer>
    </div>
    <script src="http://code.jquery.com/jquery-3.1.0.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="https://cdn.ckeditor.com/4.16.0/standard/ckeditor.js"></script>
    <script src="{{$app_path }}/statics/jQuery.filer-1.3.0/js/jquery.filer.min.js"></script>
    <script src="{{ $app_path }}/statics/jQuery.filer-1.3.0/examples/dragdrop/js/custom.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src='{{ $app_path }}/statics/js/home.js'></script>
    <script src='{{ $app_path }}/statics/js/messages.js'></script>
    <script src='{{ $app_path }}/statics/js/statistics.js'></script>
</body>

</html>
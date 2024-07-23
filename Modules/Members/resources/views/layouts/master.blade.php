<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    

    <title>Saradhi - {{ $page_title ?? 'Members' }}</title>

    <meta name="description" content="{{ $page_description ?? '' }}">
    <meta name="keywords" content="{{ $page_keywords ?? '' }}">
    <meta name="author" content="{{ $page_author ?? '' }}">


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
    

    {{-- Vite CSS --}}
    {{-- {{ module_vite('build-members', 'resources/assets/sass/app.scss') }} --}}
    
    @vite(\Nwidart\Modules\Module::getAssets())
    @yield('page-style')
</head>

<body>
    <header class="main">
        <div class="container">
            <div class="logo">
                <img src="{{ asset('images/logo.png') }}" alt="{{ env('APP_NAME') }}" class="img-fluid logo-img">
            </div>
        </div>
    </header>
    <div class="container-main">
        @yield('content')
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
    {{-- Vite JS --}}
    {{-- {{ module_vite('build-members', 'resources/assets/js/app.js') }} --}}
    @yield('page_scripts')
</body>

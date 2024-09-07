<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Saradhi - Admin</title>
    <meta name="description" content="{{ $page_description ?? '' }}">
    <meta name="keywords" content="{{ $page_keywords ?? '' }}">
    <meta name="author" content="{{ $page_author ?? '' }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- Scripts -->
    @vite(['resources/sass/admin/admin.scss', 'resources/js/app.js'])
    @yield('page-style')
</head>
<body>
    <div class="container-main">
        @include('admin.includes.sidebar')
        <div class="content-wrapper" id="content">
            <header class="header-main">
                <button id="sidebar_toggle" class="btn sidebar-toggle"><i class="fa-solid fa-bars"></i></button>
                <div class="title">
                    <div class="logo">
                        Logo
                    </div>
                </div>
                <div class="actions">
                    <div class="dropdown">
                        <a href="" class="avatar dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            @if(Auth::user()->avatar !== null)
                                <img src="{{ url('storage/images/'. Auth::user()->avatar) }}" alt=" " />
                            @endif
                        </a>
                        <ul class="dropdown-menu avatar-dropdown">
                            <li class="title">
                                <span class="avatar">
                                    @if(Auth::user()->avatar !== null)
                                        <img src="{{ url('storage/images/'. Auth::user()->avatar) }}" alt=" " />
                                    @endif
                                </span>
                                <span class="name">{{ Auth::user()->name }}</span>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>
            <div class="content-main">
                @yield('content')
            </div>
        </div>
    </div>
    </div>
    <script type="text/javascript">
        window.FontAwesomeConfig = { autoReplaceSvg: false }
        </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script>
        feather.replace();
    </script>
    {{-- Vite JS --}}
    {{-- {{ module_vite('build-members', 'resources/assets/js/app.js') }} --}}
    @yield('page_scripts')
</body>
</html>

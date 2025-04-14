@extends('layouts.main')
@section('section')

    <body class="hold-transition sidebar-mini layout-fixed sidebar-collapse">
        <div class="wrapper">

            <!-- Preloader -->
            {{-- <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="{{ asset('dist/img/AdminLTELogo.png') }}" alt="AdminLTELogo" height="60"
                width="60">
        </div> --}}

            <!-- Navbar -->
            @include('layouts.navbar')
            <!-- /.navbar -->



            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">

                @yield('content')

            </div>

        </div>
        <!-- ./wrapper -->


    </body>
@endsection

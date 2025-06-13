<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Responsive Bootstrap4 Dashboard Template">
    <meta name="author" content="ParkerThemes">
    <link rel="shortcut icon" href="{{ asset('img/Logo/TTC LOGO.png') }}" />
    <title>TTCA PTNL WEBSITE</title>

    {{-- Load asset ผ่าน Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="{{ url('fonts/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/buttons_custom.css') }}">
    <link rel="stylesheet" href="{{ url('vendor/daterange/daterange.css') }}" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>

    <div id="app-vue"></div>

    <!-- Optional legacy JS -->
    <script src="{{ url('js/jquery.min.js') }}"></script>
    <script src="{{ url('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ url('js/moment.js') }}"></script>
    <script src="{{ url('vendor/slimscroll/slimscroll.min.js') }}"></script>
    <script src="{{ url('vendor/slimscroll/custom-scrollbar.js') }}"></script>
    <script src="{{ url('vendor/daterange/daterange.js') }}"></script>
    <script src="{{ url('vendor/daterange/custom-daterange.js') }}"></script>
    <script src="{{ url('vendor/polyfill/polyfill.min.js') }}"></script>
    <script src="{{ url('js/main.js') }}"></script>

</body>
</html>

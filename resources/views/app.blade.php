<!DOCTYPE html>
<html lang="en">
<head>
	<!-- Required meta tags -->
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Responsive Bootstrap4 Dashboard Template">
    <meta name="author" content="ParkerThemes">
    <title>TTCA PTNL WEBSITE</title>  
    @vite('resources/css/app.css')

            <link rel="stylesheet" href="{{ url('css/bootstrap.min.css') }}">
            <!-- Icomoon Font Icons css -->
            <link rel="stylesheet" href="{{ url('fonts/style.css') }}">
            <!-- Main css -->
            <link rel="stylesheet" href="{{ asset('css/main.css') }}">
            <!-- DateRange css -->
            <link rel="stylesheet" href="{{ url('vendor/daterange/daterange.css') }}" />
            <!-- Icon css -->
          <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
            
</head>
<body >

    <div id="app-vue"></div>

    @vite('resources/js/app.js')

    <!-- Required jQuery first, then Bootstrap Bundle JS -->
<script src="{{ url('js/jquery.min.js') }}"></script>
<script src="{{ url('js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ url('js/moment.js') }}"></script>

<!-- Slimscroll JS -->
<script src="{{ url('vendor/slimscroll/slimscroll.min.js') }}"></script>
<script src="{{ url('vendor/slimscroll/custom-scrollbar.js') }}"></script>

<!-- Daterange -->
<script src="{{ url('vendor/daterange/daterange.js') }}"></script>
<script src="{{ url('vendor/daterange/custom-daterange.js') }}"></script>

<!-- Polyfill JS -->
<script src="{{ url('vendor/polyfill/polyfill.min.js') }}"></script>

<!-- Apex Charts -->
<script src="{{ url('vendor/apex/apexcharts.min.js') }}"></script>
<script src="{{ url('vendor/apex/admin/visitors.js') }}"></script>
<script src="{{ url('vendor/apex/admin/deals.js') }}"></script>
<script src="{{ url('vendor/apex/admin/income.js') }}"></script>
<script src="{{ url('vendor/apex/admin/customers.js') }}"></script>

<!-- Main JS -->
<script src="{{ url('js/main.js') }}"></script>


</body>
</html>
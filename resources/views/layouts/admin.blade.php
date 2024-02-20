<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- admin.css here -->
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet" />

    <!-- Importa SheetJS y FileSaver.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.0/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.0/FileSaver.min.js"></script>



    <!-- app js values -->
    <script type="application/javascript">
        var LSK_APP = {};
        LSK_APP.APP_URL = "{{ env('APP_URL') }}";
        LSK_APP.AUTH_USER = @json(auth()->user());
        LSK_APP.AUTH_USER.profile = @json(auth()->user()->profiable);
        LSK_APP.AUTH_USER.all_permissions = @json(auth()->user()->all_permissions);
        LSK_APP.AUTH_USER.groups = @json(auth()->user()->groups);
        LSK_APP.NAV = <?php echo json_encode($nav); ?>
    </script>
</head>

<body>
    <div id="admin"></div>
    <!-- Scripts -->

    <script src="{{ asset('js/manifest.js') }}"></script>
    <script src="{{ asset('js/vendor.js') }}"></script>
    <script src="{{ asset('js/admin.js') }}"></script>
</body>

</html>

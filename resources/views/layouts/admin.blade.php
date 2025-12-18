<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin Panel') - {{ Auth::user()->agency->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        /* Navbar Container */
        .navbar {
            padding: 12px 22px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            background-color: #495057 !important;
        }

        /* Wrap the links nicely */
        .navbar-nav {
            flex-wrap: wrap !important;
            gap: 6px 10px;
        }

        /* Nav links */
        .navbar-nav .nav-link {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.92rem;
            font-weight: 500;
            background: #f8f9fa;
            color: #333 !important;
            transition: 0.2s ease;
        }

        .navbar-nav .nav-link:hover {
            background: #e2e6ea;
            color: #000 !important;
        }

        /* Active link highlight */
        .navbar-nav .nav-link.active {
            background: #0d6efd !important;
            color: #fff !important;
            font-weight: 600;
        }

        /* Dropdown styling */
        .dropdown-menu {
            border-radius: 6px;
            padding: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .dropdown-item {
            border-radius: 5px;
            padding: 8px 12px;
        }

        .dropdown-item:hover {
            background: #e9ecef;
        }

        .navbar-light .navbar-brand {
            color: aliceblue;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">{{ Auth::user()->agency->name }}</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.inspections.index') }}">Inspections</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.mappings.companycam.index') }}">CompanyCam Mapping</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.users.index') }}">HomeOwners/Advisors</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.photo-report.index') }}">Photo Report</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('homeowner.claim-documents.*') ? 'active' : '' }}"
                            href="{{ route('admin.claim-documents.index') }}">Claim Ready Documents</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="{{ route('admin.settings.index', ['agency' => Auth::user()->agency_id]) }}">
                            System Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="{{ route('admin.settings.user-mapping', ['agency' => Auth::user()->agency_id]) }}">User
                            Mapping</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="{{ route('admin.settings.homeowner-menu', ['agency' => Auth::user()->agency_id]) }}">
                            HomeOwner Menu</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container mt-4">
        @yield('content')
    </main>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        toastr.options = {
            closeButton: true,
            progressBar: true,
            timeOut: 3000,
            positionClass: "toast-top-right"
        };
    </script>
    @stack('scripts')
</body>

</html>

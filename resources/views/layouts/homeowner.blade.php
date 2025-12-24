<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'My Portal') - StormProof</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.19/index.global.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/calendar-styles.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        /* Navbar Container */
        .navbar {
            padding: 12px 22px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            background-color: #9f93f3 !important;
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

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">

            <a class="navbar-brand" href="{{ route('homeowner.dashboard') }}">Homeowner Portal</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">

                <ul class="navbar-nav me-auto">

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('homeowner.inspections.*') ? 'active' : '' }}"
                            href="{{ route('homeowner.inspections.index') }}">My Inspections</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="javascript:void(0)" style="opacity:0.6;"
                            onclick="return false;">Messages (Coming Soon)</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('homeowner.photo-reports.*') ? 'active' : '' }}"
                            href="{{ route('homeowner.photo-reports.index') }}">Photos Reports</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('homeowner.claim-documents.*') ? 'active' : '' }}"
                            href="{{ route('homeowner.claim-documents.index') }}">Claim Ready Documents</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('homeowner.projects.*') ? 'active' : '' }}"
                            href="{{ route('homeowner.projects.index') }}">Open Projects</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="javascript:void(0)" style="opacity:0.6;"
                            onclick="return false;">Interior Photo Upload (Coming Soon)</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="javascript:void(0)" style="opacity:0.6;"
                            onclick="return false;">Referral A Friend (Coming Soon)</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                            href="{{ route('homeowner.storm.season.index') }}?sessionKey=@{{ sessionKey }}">Storm
                            Season Prep Kit</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('homeowner.questionnaire.*') ? 'active' : '' }}"
                            href="{{ route('homeowner.questionnaire.index') }}">Home Questionnaire</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('homeowner.contact.*') ? 'active' : '' }}"
                            href="{{ route('homeowner.contact-support.index') }}">Contact Customer Success</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('homeowner.account.*') ? 'active' : '' }}"
                            href="{{ route('homeowner.account.index') }}">Account Details</a>
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
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="{{ asset('assets/js/appointment-handler.js') }}"></script>
    <script src="{{ asset('assets/js/calendar-utilities.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    @stack('scripts')
</body>

</html>

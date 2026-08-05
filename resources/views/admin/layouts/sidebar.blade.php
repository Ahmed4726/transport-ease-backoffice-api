<aside class="col-lg-2 col-md-3 bg-dark sidebar p-0" id="sidebar">

    <div class="sidebar-header text-center py-4 border-bottom border-secondary">

        <h5 class="text-white mb-0">
            Admin Panel
        </h5>

    </div>

    <ul class="nav flex-column mt-3">

        <li class="nav-item">

            <a href="{{ route('admin.dashboard') }}"
               class="nav-link text-white">

                <i class="bi bi-speedometer2 me-2"></i>

                Dashboard

            </a>

        </li>

        <li class="nav-item">

            <a href="{{ route('admin.drivers.index') }}"
               class="nav-link text-white">

                <i class="bi bi-person-badge me-2"></i>

                Drivers

            </a>

        </li>

        <li class="nav-item">

            <a href="#"
               class="nav-link text-white">

                <i class="bi bi-people me-2"></i>

                Passengers

            </a>

        </li>

        <li class="nav-item">

            <a href="{{ route('admin.vehicles.index') }}"
               class="nav-link text-white">

                <i class="bi bi-bus-front me-2"></i>

                Vehicles

            </a>

        </li>

        <li class="nav-item">

            <a href="{{ route('admin.cities.index') }}"
               class="nav-link text-white">

                <i class="bi bi-building me-2"></i>

                Cities

            </a>

        </li>

        <li class="nav-item">

            <a href="{{ route('admin.routes.index') }}"
               class="nav-link text-white">

                <i class="bi bi-signpost-2 me-2"></i>

                Routes

            </a>

        </li>

        <li class="nav-item">

            <a href="#"
               class="nav-link text-white">

                <i class="bi bi-map me-2"></i>

                Trips

            </a>

        </li>

        <li class="nav-item">

            <a href="#"
               class="nav-link text-white">

                <i class="bi bi-ticket-perforated me-2"></i>

                Bookings

            </a>

        </li>

        <li class="nav-item">

            <a href="#"
               class="nav-link text-white">

                <i class="bi bi-graph-up-arrow me-2"></i>

                Reports

            </a>

        </li>

        <li class="nav-item">

            <a href="#"
               class="nav-link text-white">

                <i class="bi bi-gear me-2"></i>

                Settings

            </a>

        </li>

    </ul>

</aside>

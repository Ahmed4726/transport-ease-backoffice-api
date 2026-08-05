<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">

    <div class="container-fluid">

        <button class="btn btn-primary d-lg-none me-3"
                id="sidebarToggle">

            <i class="bi bi-list fs-4"></i>

        </button>

        <a class="navbar-brand fw-bold"
           href="{{ route('admin.dashboard') }}">

            🚍 Transport Management

        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarContent">

            <span class="navbar-toggler-icon"></span>

        </button>

        <div class="collapse navbar-collapse"
             id="navbarContent">

            <ul class="navbar-nav ms-auto align-items-center">

                <li class="nav-item dropdown">

                    <a class="nav-link dropdown-toggle d-flex align-items-center"
                       href="#"
                       role="button"
                       data-bs-toggle="dropdown">

                        <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center"
                             style="width:40px;height:40px;">

                            <i class="bi bi-person-fill"></i>

                        </div>

                        <span class="ms-2">

                            {{ auth()->user()->name ?? 'Administrator' }}

                        </span>

                    </a>

                    <ul class="dropdown-menu dropdown-menu-end shadow">

                        <li>

                            <a class="dropdown-item"
                               href="#">

                                <i class="bi bi-person-circle me-2"></i>

                                Profile

                            </a>

                        </li>

                        <li>

                            <hr class="dropdown-divider">

                        </li>

                        <li>

                            <form action="{{ route('admin.logout') }}"
                                  method="POST">

                                @csrf

                                <button type="submit"
                                        class="dropdown-item text-danger">

                                    <i class="bi bi-box-arrow-right me-2"></i>

                                    Logout

                                </button>

                            </form>

                        </li>

                    </ul>

                </li>

            </ul>

        </div>

    </div>

</nav>

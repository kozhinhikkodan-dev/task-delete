<div class="main-nav">
    <!-- Sidebar Logo -->
    <div class="logo-box">
        <a href="{{ route('dashboard') }}" class="logo-dark">
            <img src="/images/logo-sm.png" class="logo-sm" alt="logo sm">
            <img src="/images/logo-dark.png" class="logo-lg" alt="logo dark">
        </a>

        <a href="{{ route('dashboard') }}" class="logo-light">
            <img src="/images/logo-sm.png" class="logo-sm" alt="logo sm">
            <img src="/images/logo-light.png" class="logo-lg" alt="logo light">
        </a>
    </div>

    <!-- Menu Toggle Button (sm-hover) -->
    <button type="button" class="button-sm-hover" aria-label="Show Full Sidebar">
        <iconify-icon icon="solar:double-alt-arrow-right-bold-duotone" class="button-sm-hover-icon"></iconify-icon>
    </button>

    <div class="scrollbar" data-simplebar>
        <ul class="navbar-nav" id="navbar-nav">


            <li class="nav-item">
                <a class="nav-link {{ routeActive(['dashboard.*', 'home']) }}" href="{{ route('dashboard') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="solar:widget-5-bold-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text"> Dashboard </span>
                </a>
            </li>

            {{-- Hidden Orders Section --}}
            {{-- <li class="menu-title">Orders</li>

            <li class="nav-item">
                <a class="nav-link menu-arrow" href="#sidebarSupplierOrders" data-bs-toggle="collapse" role="button"
                    aria-expanded="false" aria-controls="sidebarSupplierOrders">
                    <span class="nav-icon">
                        <iconify-icon icon="solar:user-hand-up-bold-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text"> Supplier Orders </span>
                </a>
                <div class="collapse" id="sidebarSupplierOrders">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('demo-route') }}">Add new Supplier Order</a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('demo-route') }}">All Supplier Orders</a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link menu-arrow" href="#sidebarAlterationOrders" data-bs-toggle="collapse" role="button"
                    aria-expanded="false" aria-controls="sidebarAlterationOrders">
                    <span class="nav-icon">
                        <iconify-icon icon="solar:scissors-square-line-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text"> Alteration Orders </span>
                </a>
                <div class="collapse" id="sidebarAlterationOrders">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('demo-route') }}">Add new Alteration Orders</a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('demo-route') }}">All Alteration Orders</a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link menu-arrow" href="#sidebarManufacturingOrders" data-bs-toggle="collapse"
                    role="button" aria-expanded="false" aria-controls="sidebarManufacturingOrders">
                    <span class="nav-icon">
                        <iconify-icon icon="solar:hamburger-menu-bold-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text"> Manufacturings Orders </span>
                </a>
                <div class="collapse" id="sidebarManufacturingOrders">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('demo-route') }}">Add new Manufacturing Orders</a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('demo-route') }}">All Manufacturing Orders</a>
                        </li>
                    </ul>
                </div>
            </li> --}}

            {{-- Hidden Master Data Section --}}
            {{-- <li class="menu-title">Master Data</li>

            <li class="nav-item">
                <a class="nav-link menu-arrow" href="#sidebarProducts" data-bs-toggle="collapse" role="button"
                    aria-expanded="false" aria-controls="sidebarProducts">
                    <span class="nav-icon">
                        <iconify-icon icon="solar:t-shirt-bold-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text"> Products </span>
                </a>
                <div class="collapse" id="sidebarProducts">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('demo-route') }}">Add new Product</a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('demo-route') }}">All Products</a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link menu-arrow" href="#sidebarCategories" data-bs-toggle="collapse" role="button"
                    aria-expanded="false" aria-controls="sidebarCategories">
                    <span class="nav-icon">
                        <iconify-icon icon="solar:layers-minimalistic-bold-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text"> Categories </span>
                </a>
                <div class="collapse" id="sidebarCategories">
                    <ul class="nav sub-navbar-nav">
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('demo-route') }}">Add new Category</a>
                        </li>
                        <li class="sub-nav-item">
                            <a class="sub-nav-link" href="{{ route('demo-route') }}">All Categories</a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('demo-route') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text"> Suppliers </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('demo-route') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="solar:ruler-angular-bold-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text"> Size Chart </span>
                </a>
            </li> --}}



            @if(auth()->user()->can('View tasks list') || auth()->user()->can('View task types list'))
            <li class="menu-title">Task Management</li>
            @endif

            @can('viewAny', \App\Models\Task::class)
                <li class="nav-item">
                    <a class="nav-link {{ routeActive('tasks.*') }}" href="{{ route('tasks.index') }}">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:clipboard-list-bold-duotone"></iconify-icon>
                        </span>
                        <span class="nav-text"> Tasks </span>
                    </a>
                </li>
            @endcan

            @can('viewAny', \App\Models\TaskType::class)
                <li class="nav-item">
                    <a class="nav-link {{ routeActive('task-types.*') }}" href="{{ route('task-types.index') }}">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:checklist-minimalistic-bold-duotone"></iconify-icon>
                        </span>
                        <span class="nav-text"> Task Types </span>
                    </a>
                </li>
            @endcan

            @if(auth()->user()->can('View customers list'))
            <li class="menu-title">Customer Management</li>
            @endif

            @can('viewAny', \App\Models\Customer::class)
                <li class="nav-item">
                    <a class="nav-link {{ routeActive('customers.*') }}" href="{{ route('customers.index') }}">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:user-heart-rounded-bold-duotone"></iconify-icon>
                        </span>
                        <span class="nav-text"> Customers </span>
                    </a>
                </li>
            @endcan

            @if(Gate::allows('viewAny', \App\Models\Role::class) || Gate::allows('viewAny', \App\Models\User::class))
            <li class="menu-title">User Management</li>
            @endif

            @can('viewAny', \App\Models\User::class)
                <li class="nav-item">
                    <a class="nav-link {{ routeActive('users.*') }}" href="{{ route('users.index') }}">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:users-group-two-rounded-bold-duotone"></iconify-icon>
                        </span>
                        <span class="nav-text"> Users </span>
                    </a>
                </li>
            @endcan

            @can('viewAny', \App\Models\Role::class)
                <li class="nav-item">
                    <a class="nav-link {{ routeActive('roles.*') }}" href="{{ route('roles.index')}}">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:shield-user-bold-duotone"></iconify-icon>
                        </span>
                        <span class="nav-text"> User Roles </span>
                    </a>
                </li>
            @endcan

        </ul>
    </div>
</div>
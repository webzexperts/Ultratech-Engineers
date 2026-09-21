<header id="page-topbar">
    <button class="rm-mobile-menu-btn" onclick="rmToggleMenu()">☰</button>
    <div class="layout-width">
        <div class="navbar-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="navbar-brand-box horizontal-logo">
                    <a href="dashboard" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="{{ URL::asset('build/images/ultratech.png') }}" height="22">
                        </span>

                        <span class="logo-lg">
                            <img class="mt-2" src="{{ URL::asset('build/images/ultratech.png') }}" height="60">
                        </span>
                    </a>

                    <a href="dashboard" class="logo logo-light ml-2">
                        <span class="logo-sm">
                            <img class="mt-2" src="{{ URL::asset('build/images/ultratech.png') }}" height="22">
                        </span>

                        <span class="logo-lg">
                            <img class="mt-2" src="{{ URL::asset('build/images/ultratech.png') }}" height="60">
                        </span>
                    </a>
                </div>

                <div id="scrollbar" class="ms-3">
                    <div class="container-fluid p-0">
                        <ul class="navbar-nav flex-row align-items-center gap-3 top-menu mb-0">
                            <li class="menu-title">
                                <span></span>
                            </li>
                            @forelse(accessModule() as $module)
                                @if(hasAccess((accessMenu($module->id)), 'manage'))
                                    @php
                                        $childActive = false;
                                        foreach (manageAccessMenu($module->id) as $menus) {
                                            $passRoute = $menus->page == "user_access" ? "edit-" : "manage-";
                                            if (Route::currentRouteName() == $passRoute . $menus->page || Route::currentRouteName() == 'switch-company_year') {
                                                $childActive = true;
                                                break;
                                            }
                                        }
                                        $modCollapseId = "top_mod_" . preg_replace('/[^a-zA-Z0-9]/', '_', $module->display_name);
                                    @endphp
                                    <li class="nav-item">
                                        <a class="nav-link menu-link {{ $childActive ? 'active' : '' }}" href="#{{ $modCollapseId }}" data-bs-toggle="collapse">
                                            <span>{{ $module->display_name }}</span>
                                        </a>

                                         <div class="collapse menu-dropdown {{ $childActive ? 'show' : '' }}" id="{{ $modCollapseId }}">
                                             <ul class="nav nav-sm flex-column">
                                                 @if(isSubMenuEnabled())
                                                     @php
                                                         $subModules = getSubModules($module->id);
                                                     @endphp
                                                     @if(count($subModules) > 0)
                                                         @foreach($subModules as $subModule)
                                                             @if(hasSubModuleAccess($subModule->id))
                                                                 @php
                                                                     $subModuleMenus = getSubModuleMenus($subModule->id);
                                                                     $subActive = false;
                                                                     foreach($subModuleMenus as $smm) {
                                                                         $passRoute = $smm->page == "user_access" ? "edit-" : "manage-";
                                                                         if (Route::currentRouteName() == $passRoute . $smm->page) {
                                                                             $subActive = true;
                                                                             break;
                                                                         }
                                                                     }
                                                                     $subCollapseId = "sub_h_" . preg_replace('/[^a-zA-Z0-9]/', '_', $subModule->display_name);
                                                                 @endphp
                                                                 <li class="nav-item">
                                                                     <a href="#{{ $subCollapseId }}"
                                                                        class="nav-link {{ $subActive ? 'active' : '' }}"
                                                                        data-bs-toggle="collapse"
                                                                        role="button"
                                                                        aria-expanded="{{ $subActive ? 'true' : 'false' }}"
                                                                        aria-controls="{{ $subCollapseId }}">
                                                                         {{ $subModule->display_name }}
                                                                     </a>
                                                                     <div class="collapse menu-dropdown {{ $subActive ? 'show' : '' }}"
                                                                          id="{{ $subCollapseId }}">
                                                                         <ul class="nav nav-sm flex-column">
                                                                             @foreach($subModuleMenus as $menus)
                                                                                 @if(hasAccess($menus->page, "manage"))
                                                                                     @php $passRoute = $menus->page == "user_access" ? "edit-" : "manage-"; @endphp
                                                                                     <li class="nav-item">
                                                                                         <a href="{{ route($passRoute.$menus->page) }}"
                                                                                            class="nav-link">
                                                                                             {{ $menus->display_name }}
                                                                                         </a>
                                                                                     </li>
                                                                                 @endif
                                                                             @endforeach
                                                                         </ul>
                                                                     </div>
                                                                 </li>
                                                             @endif
                                                         @endforeach
                                                     @endif
                                                 @endif

                                                 @foreach(getDirectModuleMenus($module->id) as $menus)
                                                     @if(hasAccess($menus->page, "manage"))
                                                         @php $passRoute = $menus->page == "user_access" ? "edit-" : "manage-"; @endphp
                                                         <li class="nav-item">
                                                             <a href="{{ route($passRoute.$menus->page) }}" class="nav-link">
                                                                 {{ $menus->display_name }}
                                                             </a>
                                                         </li>
                                                     @endif
                                                 @endforeach

                                                 @if($module->display_name=="Master")
                                                     <li class="nav-item">
                                                         <a href="{{ route('switch-company_year') }}" class="nav-link">Switch Year</a>
                                                     </li>
                                                 @endif
                                             </ul>
                                         </div>
                                    </li>
                                @else
                                <li class="nav-item">
                                    @if($module->display_name == "Master")
                                        <a class="nav-link menu-link" href="#MasterManual" data-bs-toggle="collapse">
                                            <span>{{ $module->display_name }}</span>
                                        </a>
                                        <div class="collapse menu-dropdown" id="MasterManual">
                                            <ul class="nav nav-sm flex-column">
                                                <li class="nav-item">
                                                    <a href="{{ route('switch-company_year') }}" class="nav-link">
                                                        Switch Year
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif
                                </li>
                                @endif
                                @empty
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

           
            <div class="d-flex align-items-center justify-content-end">
                <div class="dropdown ms-1 topbar-head-dropdown header-item rm-mobile-hide me-3">
                    <a href="{{ route('switch-company_year')}}">
                        <h5 class="nav-top-h5" style="color:#ffffff">FY {{ defaultCompanyYear() }}</h5>
                    </a>
                </div>
                <!-- <div class="d-flex align-items-center justify-content-end">
                    <div class="dropdown ms-1 topbar-head-dropdown header-item rm-mobile-hide me-3">
                        <a href="{{ route('selectLocation')}}">
                            <h5 class="nav-top-h5" style="color:#ffffff">Location : @if(getUserLocation() != ''){{ getUserLocation() ?: ""}} @endif </h5>
                        </a>
                    </div>
    
                </div> -->
                <div class="d-flex align-items-center justify-content-end">
                    <div class="dropdown ms-1 topbar-head-dropdown header-item rm-mobile-hide ">
                        <a href="javascript:void(0);">
                            <h5 class="nav-top-h5" style="color:#ffffff">{{Auth::user()->user_name}}</h5>
                        </a>
                    </div>
    
                </div>

                {{-- <div class="ms-sm-3 header-item topbar-user rm-mobile-hide">
                    <button type="button" class="btn shadow-none">
                        <span class="d-flex align-items-center justify-content-end">
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{Auth::user()->user_name}}</span>
                            </span>
                        </span>
                    </button>
                </div> --}}

                <div class="ms-sm-3 rm-mobile-hide">
                    <a href="javascript:void(0);" style="color:#ffffff" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="mdi mdi-logout fs-23"></i>
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="rm-mobile-menu" id="rmMobileMenu">
    <ul>
        @forelse(accessModule() as $module)
            @if(hasAccess((accessMenu($module->id)), 'manage'))
                <li>
                    <span onclick="rmToggleSub(this)">{{ $module->display_name }}</span>
                    <ul class="rm-submenu">
                        @if(isSubMenuEnabled())
                            @php
                                $subModules = getSubModules($module->id);
                            @endphp
                            @if(count($subModules) > 0)
                                @foreach($subModules as $subModule)
                                    @if(hasSubModuleAccess($subModule->id))
                                        <li>
                                            <span onclick="rmToggleSub(this)" style="font-weight: 500; padding-left: 15px; display: block; padding-top: 8px; padding-bottom: 8px; color: #eee; cursor: pointer;">{{ $subModule->display_name }}</span>
                                            <ul class="rm-submenu">
                                                @foreach(getSubModuleMenus($subModule->id) as $menus)
                                                    @if(hasAccess($menus->page, "manage"))
                                                        @php $passRoute = $menus->page == "user_access" ? "edit-" : "manage-"; @endphp
                                                        <li>
                                                            <a href="{{ route($passRoute.$menus->page) }}" style="padding-left: 30px;">
                                                                {{ $menus->display_name }}
                                                            </a>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </li>
                                    @endif
                                @endforeach
                            @endif
                        @endif

                        @foreach(getDirectModuleMenus($module->id) as $menus)
                            @if(hasAccess($menus->page, "manage"))
                                @php $passRoute = $menus->page == "user_access" ? "edit-" : "manage-"; @endphp
                                <li>
                                    <a href="{{ route($passRoute.$menus->page) }}" style="padding-left: 15px;">
                                        {{ $menus->display_name }}
                                    </a>
                                </li>
                            @endif
                        @endforeach

                        @if($module->display_name=="Master")
                            <li>
                                <a href="{{ route('switch-company_year') }}" style="padding-left: 15px;">Switch Year</a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            @empty
        @endforelse
    </ul>

    <div class="rm-mobile-bottom">
        <a href="{{ route('switch-company_year')}}">FY {{ defaultCompanyYear() }}</a>
        <a href="javascript:void(0);">
            Location : @if(getUserLocation() != ''){{ getUserLocation() ?: ""}} @endif
        </a>
        <a href="#">{{Auth::user()->user_name}}</a>
        <a href="javascript:void(0);" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="mdi mdi-logout fs-23"></i>
        </a>
    </div>
</div>

<style>
    /* Styling for nested mobile menus */
    .rm-submenu .rm-submenu {
        background: #1e2030 !important; /* Darker background for menu items */
        border-left: 4px solid #4b566b; /* Left indicator border */
    }
    .rm-submenu .rm-submenu a {
        padding-left: 28px !important;
        color: #cbd5e1 !important;
        border-bottom: 1px solid #2e324a;
        font-size: 13.5px;
    }
    .rm-submenu .rm-submenu a:hover {
        background: #161824 !important;
        color: #ffffff !important;
    }
    /* Add subtle background variation on the sub-module headers themselves */
    .rm-submenu > li > span {
        border-bottom: 1px solid #3c3e59;
    }
</style>

<script>
    function rmToggleMenu() {
        document.getElementById("rmMobileMenu").classList.toggle("active");
    }

    function rmToggleSub(el) {
        var submenu = el.nextElementSibling;
        submenu.classList.toggle("active");
    }
</script>
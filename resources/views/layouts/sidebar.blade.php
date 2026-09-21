<!-- ========== App Menu ========== -->
<!-- <div class="app-menu navbar-menu"> -->
<div class="app-menu navbar-menu d-none">

    <!-- LOGO -->
    <div class="navbar-brand-box">
        <a href="dashboard" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/logo-dark.png') }}" alt="" height="17">
            </span>
        </a>

        <a href="dashboard" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/ultratech.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/ultratech.png') }}" alt="" height="60">
            </span>
        </a>

        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span></span></li>
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
                            $modCollapseId = "mod_" . preg_replace('/[^a-zA-Z0-9]/', '_', $module->display_name);
                        @endphp

                        <li class="nav-item">
                            <a class="nav-link menu-link {{ $childActive ? 'active' : '' }}"
                               href="#{{ $modCollapseId }}"
                               data-bs-toggle="collapse"
                               role="button"
                               aria-expanded="{{ $childActive ? 'true' : 'false' }}"
                               aria-controls="{{ $modCollapseId }}">
                               @if(str_contains($module->display_name, "Master"))
                               <i class="ri-settings-2-line"></i>
                               @elseif($module->display_name == "Transaction")
                                 <i class="ri-file-line"></i>
                               @elseif(str_contains($module->display_name, "Report"))
                                 <i class="ri-file-excel-2-line"></i>
                               @elseif(str_contains($module->display_name, "Store"))
                                 <i class="ri-store-3-line"></i>
                               @elseif($module->display_name == "Admin")
                                 <i class="ri-group-fill"></i>
                               @elseif($module->display_name == "Utility")
                                 <i class="ri-tools-line"></i>
                               @else
                                 <i class="ri-folder-line"></i>
                               @endif
                               <span>{{ $module->display_name }}</span>
                            </a>

                            <div class="collapse menu-dropdown {{ $childActive ? 'show' : '' }}"
                                 id="{{ $modCollapseId }}">
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
                                                        $subCollapseId = "sub_" . preg_replace('/[^a-zA-Z0-9]/', '_', $subModule->display_name);
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
                                                                        @php
                                                                            $passRoute = $menus->page == "user_access" ? "edit-" : "manage-";
                                                                            $active = Route::currentRouteName() == $passRoute . $menus->page ? 'active' : '';
                                                                        @endphp
                                                                        <li class="nav-item">
                                                                            <a href="{{ route($passRoute . $menus->page) }}"
                                                                               class="nav-link {{ $active }}">
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
                                            @php
                                                $passRoute = $menus->page == "user_access" ? "edit-" : "manage-";
                                                $active = Route::currentRouteName() == $passRoute . $menus->page ? 'active' : '';
                                            @endphp
                                            <li class="nav-item">
                                                <a href="{{ route($passRoute . $menus->page) }}"
                                                   class="nav-link {{ $active }}">
                                                    {{ $menus->display_name }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach

                                    @if($module->display_name == "Master")   
                                        @php
                                            $switchActive = Route::currentRouteName() == 'switch-company_year' ? 'active' : '';
                                        @endphp
                                        <li class="nav-item">
                                            <a href="{{ route('switch-company_year') }}" class="nav-link {{ $switchActive }}">
                                                Switch Year
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif
                @empty
                @endforelse
            </ul>
        </div>
    </div>

    <div class="sidebar-background"></div>
</div>
<div class="vertical-overlay"></div>
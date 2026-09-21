<header id="page-topbar">
    <button class="rm-mobile-menu-btn" onclick="rmToggleMenu()">☰</button>
    <div class="layout-width">
        <div class="navbar-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="navbar-brand-box horizontal-logo">
                    <a href="dashboard" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="<?php echo e(URL::asset('build/images/ultratech.png')); ?>" height="22">
                        </span>

                        <span class="logo-lg">
                            <img class="mt-2" src="<?php echo e(URL::asset('build/images/ultratech.png')); ?>" height="60">
                        </span>
                    </a>

                    <a href="dashboard" class="logo logo-light ml-2">
                        <span class="logo-sm">
                            <img class="mt-2" src="<?php echo e(URL::asset('build/images/ultratech.png')); ?>" height="22">
                        </span>

                        <span class="logo-lg">
                            <img class="mt-2" src="<?php echo e(URL::asset('build/images/ultratech.png')); ?>" height="60">
                        </span>
                    </a>
                </div>

                <div id="scrollbar" class="ms-3">
                    <div class="container-fluid p-0">
                        <ul class="navbar-nav flex-row align-items-center gap-3 top-menu mb-0">
                            <li class="menu-title">
                                <span></span>
                            </li>
                            <?php $__empty_1 = true; $__currentLoopData = accessModule(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php if(hasAccess((accessMenu($module->id)), 'manage')): ?>
                                    <?php
                                        $childActive = false;
                                        foreach (manageAccessMenu($module->id) as $menus) {
                                            $passRoute = $menus->page == "user_access" ? "edit-" : "manage-";
                                            if (Route::currentRouteName() == $passRoute . $menus->page || Route::currentRouteName() == 'switch-company_year') {
                                                $childActive = true;
                                                break;
                                            }
                                        }
                                        $modCollapseId = "top_mod_" . preg_replace('/[^a-zA-Z0-9]/', '_', $module->display_name);
                                    ?>
                                    <li class="nav-item">
                                        <a class="nav-link menu-link <?php echo e($childActive ? 'active' : ''); ?>" href="#<?php echo e($modCollapseId); ?>" data-bs-toggle="collapse">
                                            <span><?php echo e($module->display_name); ?></span>
                                        </a>

                                         <div class="collapse menu-dropdown <?php echo e($childActive ? 'show' : ''); ?>" id="<?php echo e($modCollapseId); ?>">
                                             <ul class="nav nav-sm flex-column">
                                                 <?php if(isSubMenuEnabled()): ?>
                                                     <?php
                                                         $subModules = getSubModules($module->id);
                                                     ?>
                                                     <?php if(count($subModules) > 0): ?>
                                                         <?php $__currentLoopData = $subModules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subModule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                             <?php if(hasSubModuleAccess($subModule->id)): ?>
                                                                 <?php
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
                                                                 ?>
                                                                 <li class="nav-item">
                                                                     <a href="#<?php echo e($subCollapseId); ?>"
                                                                        class="nav-link <?php echo e($subActive ? 'active' : ''); ?>"
                                                                        data-bs-toggle="collapse"
                                                                        role="button"
                                                                        aria-expanded="<?php echo e($subActive ? 'true' : 'false'); ?>"
                                                                        aria-controls="<?php echo e($subCollapseId); ?>">
                                                                         <?php echo e($subModule->display_name); ?>

                                                                     </a>
                                                                     <div class="collapse menu-dropdown <?php echo e($subActive ? 'show' : ''); ?>"
                                                                          id="<?php echo e($subCollapseId); ?>">
                                                                         <ul class="nav nav-sm flex-column">
                                                                             <?php $__currentLoopData = $subModuleMenus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menus): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                 <?php if(hasAccess($menus->page, "manage")): ?>
                                                                                     <?php $passRoute = $menus->page == "user_access" ? "edit-" : "manage-"; ?>
                                                                                     <li class="nav-item">
                                                                                         <a href="<?php echo e(route($passRoute.$menus->page)); ?>"
                                                                                            class="nav-link">
                                                                                             <?php echo e($menus->display_name); ?>

                                                                                         </a>
                                                                                     </li>
                                                                                 <?php endif; ?>
                                                                             <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                         </ul>
                                                                     </div>
                                                                 </li>
                                                             <?php endif; ?>
                                                         <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                     <?php endif; ?>
                                                 <?php endif; ?>

                                                 <?php $__currentLoopData = getDirectModuleMenus($module->id); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menus): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                     <?php if(hasAccess($menus->page, "manage")): ?>
                                                         <?php $passRoute = $menus->page == "user_access" ? "edit-" : "manage-"; ?>
                                                         <li class="nav-item">
                                                             <a href="<?php echo e(route($passRoute.$menus->page)); ?>" class="nav-link">
                                                                 <?php echo e($menus->display_name); ?>

                                                             </a>
                                                         </li>
                                                     <?php endif; ?>
                                                 <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                                 <?php if($module->display_name=="Master"): ?>
                                                     <li class="nav-item">
                                                         <a href="<?php echo e(route('switch-company_year')); ?>" class="nav-link">Switch Year</a>
                                                     </li>
                                                 <?php endif; ?>
                                             </ul>
                                         </div>
                                    </li>
                                <?php else: ?>
                                <li class="nav-item">
                                    <?php if($module->display_name == "Master"): ?>
                                        <a class="nav-link menu-link" href="#MasterManual" data-bs-toggle="collapse">
                                            <span><?php echo e($module->display_name); ?></span>
                                        </a>
                                        <div class="collapse menu-dropdown" id="MasterManual">
                                            <ul class="nav nav-sm flex-column">
                                                <li class="nav-item">
                                                    <a href="<?php echo e(route('switch-company_year')); ?>" class="nav-link">
                                                        Switch Year
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </li>
                                <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

           
            <div class="d-flex align-items-center justify-content-end">
                <div class="dropdown ms-1 topbar-head-dropdown header-item rm-mobile-hide me-3">
                    <a href="<?php echo e(route('switch-company_year')); ?>">
                        <h5 class="nav-top-h5" style="color:#ffffff">FY <?php echo e(defaultCompanyYear()); ?></h5>
                    </a>
                </div>
                <!-- <div class="d-flex align-items-center justify-content-end">
                    <div class="dropdown ms-1 topbar-head-dropdown header-item rm-mobile-hide me-3">
                        <a href="<?php echo e(route('selectLocation')); ?>">
                            <h5 class="nav-top-h5" style="color:#ffffff">Location : <?php if(getUserLocation() != ''): ?><?php echo e(getUserLocation() ?: ""); ?> <?php endif; ?> </h5>
                        </a>
                    </div>
    
                </div> -->
                <div class="d-flex align-items-center justify-content-end">
                    <div class="dropdown ms-1 topbar-head-dropdown header-item rm-mobile-hide ">
                        <a href="javascript:void(0);">
                            <h5 class="nav-top-h5" style="color:#ffffff"><?php echo e(Auth::user()->user_name); ?></h5>
                        </a>
                    </div>
    
                </div>

                

                <div class="ms-sm-3 rm-mobile-hide">
                    <a href="javascript:void(0);" style="color:#ffffff" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="mdi mdi-logout fs-23"></i>
                    </a>

                    <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none">
                        <?php echo csrf_field(); ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="rm-mobile-menu" id="rmMobileMenu">
    <ul>
        <?php $__empty_1 = true; $__currentLoopData = accessModule(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php if(hasAccess((accessMenu($module->id)), 'manage')): ?>
                <li>
                    <span onclick="rmToggleSub(this)"><?php echo e($module->display_name); ?></span>
                    <ul class="rm-submenu">
                        <?php if(isSubMenuEnabled()): ?>
                            <?php
                                $subModules = getSubModules($module->id);
                            ?>
                            <?php if(count($subModules) > 0): ?>
                                <?php $__currentLoopData = $subModules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subModule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(hasSubModuleAccess($subModule->id)): ?>
                                        <li>
                                            <span onclick="rmToggleSub(this)" style="font-weight: 500; padding-left: 15px; display: block; padding-top: 8px; padding-bottom: 8px; color: #eee; cursor: pointer;"><?php echo e($subModule->display_name); ?></span>
                                            <ul class="rm-submenu">
                                                <?php $__currentLoopData = getSubModuleMenus($subModule->id); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menus): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php if(hasAccess($menus->page, "manage")): ?>
                                                        <?php $passRoute = $menus->page == "user_access" ? "edit-" : "manage-"; ?>
                                                        <li>
                                                            <a href="<?php echo e(route($passRoute.$menus->page)); ?>" style="padding-left: 30px;">
                                                                <?php echo e($menus->display_name); ?>

                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </ul>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php $__currentLoopData = getDirectModuleMenus($module->id); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menus): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(hasAccess($menus->page, "manage")): ?>
                                <?php $passRoute = $menus->page == "user_access" ? "edit-" : "manage-"; ?>
                                <li>
                                    <a href="<?php echo e(route($passRoute.$menus->page)); ?>" style="padding-left: 15px;">
                                        <?php echo e($menus->display_name); ?>

                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php if($module->display_name=="Master"): ?>
                            <li>
                                <a href="<?php echo e(route('switch-company_year')); ?>" style="padding-left: 15px;">Switch Year</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
            <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <?php endif; ?>
    </ul>

    <div class="rm-mobile-bottom">
        <a href="<?php echo e(route('switch-company_year')); ?>">FY <?php echo e(defaultCompanyYear()); ?></a>
        <a href="javascript:void(0);">
            Location : <?php if(getUserLocation() != ''): ?><?php echo e(getUserLocation() ?: ""); ?> <?php endif; ?>
        </a>
        <a href="#"><?php echo e(Auth::user()->user_name); ?></a>
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
</script><?php /**PATH F:\xampp\htdocs\ultratech-cbs\resources\views/layouts/topbar.blade.php ENDPATH**/ ?>
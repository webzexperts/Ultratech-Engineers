<!-- ========== App Menu ========== -->
<!-- <div class="app-menu navbar-menu"> -->
<div class="app-menu navbar-menu d-none">

    <!-- LOGO -->
    <div class="navbar-brand-box">
        <a href="dashboard" class="logo logo-dark">
            <span class="logo-sm">
                <img src="<?php echo e(URL::asset('build/images/logo-sm.png')); ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="<?php echo e(URL::asset('build/images/logo-dark.png')); ?>" alt="" height="17">
            </span>
        </a>

        <a href="dashboard" class="logo logo-light">
            <span class="logo-sm">
                <img src="<?php echo e(URL::asset('build/images/ultratech.png')); ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="<?php echo e(URL::asset('build/images/ultratech.png')); ?>" alt="" height="60">
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
                            $modCollapseId = "mod_" . preg_replace('/[^a-zA-Z0-9]/', '_', $module->display_name);
                        ?>

                        <li class="nav-item">
                            <a class="nav-link menu-link <?php echo e($childActive ? 'active' : ''); ?>"
                               href="#<?php echo e($modCollapseId); ?>"
                               data-bs-toggle="collapse"
                               role="button"
                               aria-expanded="<?php echo e($childActive ? 'true' : 'false'); ?>"
                               aria-controls="<?php echo e($modCollapseId); ?>">
                               <?php if(str_contains($module->display_name, "Master")): ?>
                               <i class="ri-settings-2-line"></i>
                               <?php elseif($module->display_name == "Transaction"): ?>
                                 <i class="ri-file-line"></i>
                               <?php elseif(str_contains($module->display_name, "Report")): ?>
                                 <i class="ri-file-excel-2-line"></i>
                               <?php elseif(str_contains($module->display_name, "Store")): ?>
                                 <i class="ri-store-3-line"></i>
                               <?php elseif($module->display_name == "Admin"): ?>
                                 <i class="ri-group-fill"></i>
                               <?php elseif($module->display_name == "Utility"): ?>
                                 <i class="ri-tools-line"></i>
                               <?php else: ?>
                                 <i class="ri-folder-line"></i>
                               <?php endif; ?>
                               <span><?php echo e($module->display_name); ?></span>
                            </a>

                            <div class="collapse menu-dropdown <?php echo e($childActive ? 'show' : ''); ?>"
                                 id="<?php echo e($modCollapseId); ?>">
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
                                                        $subCollapseId = "sub_" . preg_replace('/[^a-zA-Z0-9]/', '_', $subModule->display_name);
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
                                                                        <?php
                                                                            $passRoute = $menus->page == "user_access" ? "edit-" : "manage-";
                                                                            $active = Route::currentRouteName() == $passRoute . $menus->page ? 'active' : '';
                                                                        ?>
                                                                        <li class="nav-item">
                                                                            <a href="<?php echo e(route($passRoute . $menus->page)); ?>"
                                                                               class="nav-link <?php echo e($active); ?>">
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
                                            <?php
                                                $passRoute = $menus->page == "user_access" ? "edit-" : "manage-";
                                                $active = Route::currentRouteName() == $passRoute . $menus->page ? 'active' : '';
                                            ?>
                                            <li class="nav-item">
                                                <a href="<?php echo e(route($passRoute . $menus->page)); ?>"
                                                   class="nav-link <?php echo e($active); ?>">
                                                    <?php echo e($menus->display_name); ?>

                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                    <?php if($module->display_name == "Master"): ?>   
                                        <?php
                                            $switchActive = Route::currentRouteName() == 'switch-company_year' ? 'active' : '';
                                        ?>
                                        <li class="nav-item">
                                            <a href="<?php echo e(route('switch-company_year')); ?>" class="nav-link <?php echo e($switchActive); ?>">
                                                Switch Year
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </li>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="sidebar-background"></div>
</div>
<div class="vertical-overlay"></div><?php /**PATH F:\xampp\htdocs\ultratech-cbs\resources\views/layouts/sidebar.blade.php ENDPATH**/ ?>
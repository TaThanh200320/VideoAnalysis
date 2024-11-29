<div class="sidebar sidebar-narrow-unfoldable sidebar-dark sidebar-fixed border-end" id="sidebar">
    <div class="sidebar-header border-bottom">
        <div class="sidebar-brand">
            <div class="sidebar-brand-full">
                <p class="!mb-0" style="font-size: 24px">STC Video Analysis</p>
            </div>
            <img src="images/logo.png" alt="Logo" class="sidebar-brand-narrow w-8 h-8">
        </div>
        <button class="btn-close d-lg-none" type="button" data-coreui-dismiss="offcanvas" data-coreui-theme="dark"
            aria-label="Close"
            onclick="coreui.Sidebar.getInstance(document.querySelector(&quot;#sidebar&quot;)).toggle()"></button>
    </div>
    <ul class="sidebar-nav" data-coreui="navigation" data-simplebar>
        <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">
                <svg class="nav-icon">
                    <use xlink:href="node_modules/@coreui/icons/sprites/free.svg#cil-speedometer"></use>
                </svg> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('users.index') }}">
                <svg class="nav-icon">
                    <use xlink:href="node_modules/@coreui/icons/sprites/free.svg#cil-user"></use>
                </svg> Users</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ url('roles') }}">
                <svg class="nav-icon">
                    <use xlink:href="node_modules/@coreui/icons/sprites/free.svg#cil-address-book"></use>
                </svg> Roles</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('cameras') }}">
                <svg class="nav-icon">
                    <use xlink:href="node_modules/@coreui/icons/sprites/free.svg#cil-camera"></use>
                </svg> Cameras</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('configurations.areas') }}">
                <svg class="nav-icon">
                    <use xlink:href="node_modules/@coreui/icons/sprites/free.svg#cil-apps-settings"></use>
                </svg> Configurations</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('events') }}">
                <svg class="nav-icon">
                    <use xlink:href="node_modules/@coreui/icons/sprites/free.svg#cil-bell-exclamation"></use>
                </svg> Events</a></li>
    </ul>
    <div class="sidebar-footer border-top d-none d-md-flex">
        <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
    </div>
</div>

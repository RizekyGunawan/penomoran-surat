<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse border-end" style="min-height: calc(100vh - 56px);">
  <div class="position-sticky pt-3 sidebar-sticky">
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link <?= (url_is('dashboard')) ? 'active' : '' ?>" aria-current="page" href="/dashboard">
          Dashboard
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= (url_is('sapa-data')) ? 'active' : '' ?>" href="/sapa-data">
          Realisasi Anggaran SAPA
        </a>
      </li>
    </ul>
  </div>
</nav>


<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item {{ Request::is('/') ? 'active' : '' }}">
                    <a class="nav-link" href="/">Главная</a>
                </li>
                <li class="nav-item {{ Request::is('panel') ? 'active' : '' }}">
                    <a class="nav-link" href="/panel">Список доступных файлов</a>
                </li>
                <li class="nav-item {{ Request::is('files-list') ? 'active' : '' }}">
                    <a class="nav-link" href="/files-list">Список файлов</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
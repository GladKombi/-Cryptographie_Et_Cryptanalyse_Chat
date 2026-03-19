<?php
// ================= AJAX HANDLER =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    function cesar(string $message, int $cle, string $mode): string {
        $resultat = '';
        $cle = $cle % 26;
        if ($mode === 'dechiffrer') $cle = 26 - $cle;

        for ($i = 0; $i < strlen($message); $i++) {
            $c = $message[$i];
            if ($c >= 'a' && $c <= 'z') {
                $resultat .= chr(((ord($c) - 97 + $cle) % 26) + 97);
            } elseif ($c >= 'A' && $c <= 'Z') {
                $resultat .= chr(((ord($c) - 65 + $cle) % 26) + 65);
            } else {
                $resultat .= $c;
            }
        }
        return $resultat;
    }

    echo json_encode([
        'resultat' => cesar($_POST['message'], (int)$_POST['cle'], $_POST['action'])
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
<meta charset="UTF-8">
<title>Dashboard • Chiffrement César</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
/* ===== Sidebar ===== */
.sidebar {
    width: 250px;
    min-height: 100vh;
    position: fixed;
    background: var(--bs-body-bg);
    border-right: 1px solid var(--bs-border-color);
}

/* ===== Content ===== */
.content {
    margin-left: 250px;
    padding: 1.5rem;
}

/* ===== Dark mode animation ===== */
#themeToggle i {
    transition: transform .4s ease, opacity .3s;
}
.rotate {
    transform: rotate(180deg);
}
</style>
</head>

<body class="bg-body-secondary">

<!-- ============ SIDEBAR ============ -->
<div class="sidebar p-3">
    <h5 class="fw-bold mb-4">
        <i class="bi bi-grid-fill text-primary"></i> Mon Dashboard
    </h5>

    <ul class="nav nav-pills flex-column gap-2">
        <li class="nav-item">
            <a class="nav-link active" href="#">
                <i class="bi bi-shield-lock"></i> César
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">
                <i class="bi bi-people"></i> Utilisateurs
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">
                <i class="bi bi-graph-up"></i> Rapports
            </a>
        </li>
    </ul>
</div>

<!-- ============ CONTENT ============ -->
<div class="content">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand bg-body rounded shadow-sm mb-4 px-3">
        <span class="navbar-brand fw-semibold">
            🔐 Chiffrement César
        </span>

        <div class="ms-auto">
            <button id="themeToggle" class="btn btn-outline-secondary">
                <i class="bi bi-moon-stars-fill"></i>
            </button>
        </div>
    </nav>

    <!-- CARD -->
    <div class="card shadow border-0">
        <div class="card-body">
            <form id="cesarForm">

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-chat-text"></i> Message
                    </label>
                    <textarea class="form-control" name="message" rows="4" required></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-key"></i> Clé
                        </label>
                        <input type="number" class="form-control" name="cle" value="3" required>
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-arrow-left-right"></i> Action
                        </label>
                        <div class="d-flex gap-4 mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="action" value="chiffrer" checked>
                                <label class="form-check-label">Chiffrer</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="action" value="dechiffrer">
                                <label class="form-check-label">Déchiffrer</label>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary btn-lg">
                    <i class="bi bi-lightning-charge"></i> Exécuter
                </button>
            </form>
        </div>
    </div>

    <!-- RESULT -->
    <div id="resultCard" class="card shadow border-success mt-4 d-none">
        <div class="card-header bg-success text-white">
            <i class="bi bi-check-circle"></i> Résultat
        </div>
        <div class="card-body">
            <textarea id="resultat" class="form-control" rows="4" readonly></textarea>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ================= DARK MODE =================
const html = document.documentElement;
const btn = document.getElementById('themeToggle');
const icon = btn.querySelector('i');

function setTheme(theme) {
    html.setAttribute('data-bs-theme', theme);
    localStorage.setItem('theme', theme);
    icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
}

setTheme(localStorage.getItem('theme') || 'light');

btn.addEventListener('click', () => {
    icon.classList.add('rotate');
    setTimeout(() => icon.classList.remove('rotate'), 400);

    setTheme(html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
});

// ================= AJAX =================
document.getElementById('cesarForm').addEventListener('submit', e => {
    e.preventDefault();

    const formData = new FormData(e.target);
    formData.append('ajax', '1');

    fetch('', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            document.getElementById('resultat').value = data.resultat;
            document.getElementById('resultCard').classList.remove('d-none');
        });
});
</script>

</body>
</html>

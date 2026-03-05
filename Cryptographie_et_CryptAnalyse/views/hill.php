<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cesar</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">

  <style>
    :root {
      --sidebar-width: 250px;
    }

    body {
      background-color: #f8f9fc;
      overflow-x: hidden;
    }

    /* Layout Structure sans classes complexes */
    #wrapper {
      display: flex;
      min-height: 100vh;
    }

    #content-wrapper {
      flex: 1;
      display: flex;
      flex-direction: column;
      width: 100%;
    }

    /* Sidebar minimaliste */
    .sidebar {
      width: var(--sidebar-width);
      background: #9e2323;
      color: white;
      min-height: 100vh;
    }

    .main-content {
      padding: 25px;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .sidebar {
        display: none;
      }

      /* On pourra ajouter un menu toggle plus tard */
    }
  </style>
</head>

<body id="page-top">

  <div id="wrapper">
    <?php include_once('aside.php'); ?>

    <div id="content-wrapper">

      <main class="main-content">
        <div class="container-fluid">

          <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Algorithme Hill</h1>
          </div>

          <div class="row">
            <div class="col-lg-9 mx-auto">
              <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                  <h6 class="m-0 font-weight-bold text-center">Paramètres de la Matrice</h6>
                </div>
                <div class="card-body p-4">
                  <form action="../models/traitement_hill.php" method="POST">
                    <div class="mb-3">
                      <label class="form-label fw-bold">Opération</label>
                      <select class="form-select" name="operation">
                        <option value="1">🔒 Chiffrer</option>
                        <option value="2">🔓 Déchiffrer</option>
                      </select>
                    </div>

                    <div class="mb-3">
                      <label class="form-label fw-bold">Message</label>
                      <input required type="text" name="message" class="form-control" placeholder="Ex: CODE">
                    </div>

                    <label class="form-label fw-bold mb-2">Matrice de la clé (a,b,c,d)</label>
                    <div class="row g-3 mb-4">
                      <div class="col-3">
                        <input required type="number" name="a" class="form-control text-center" placeholder="a">
                      </div>
                      <div class="col-3">
                        <input required type="number" name="b" class="form-control text-center" placeholder="b">
                      </div>
                      <div class="col-3">
                        <input required type="number" name="c" class="form-control text-center" placeholder="c">
                      </div>
                      <div class="col-3">
                        <input required type="number" name="d" class="form-control text-center" placeholder="d">
                      </div>
                    </div>

                    <button type="submit" name="valider" class="btn btn-danger text-white w-100 py-2 fw-bold">
                      Calculer Hill
                    </button>
                  </form>
                </div>
              </div>

              <?php if (isset($_SESSION['resultat_hill'])): ?>
                <div class="card border-start border-info border-4 shadow-sm mb-4">
                  <div class="card-body">
                    <div class="d-flex justify-content-between">
                      <label class="small fw-bold text-muted text-uppercase">Résultat :</label>
                      <a href="clear_session.php" class="btn btn-sm btn-link text-danger p-0">Effacer</a>
                    </div>
                    <div class="mt-2 p-3 bg-light rounded font-monospace fs-4 text-center">
                      <?= htmlspecialchars($_SESSION['resultat_hill']) ?>
                    </div>
                  </div>
                </div>
                <?php unset($_SESSION['resultat_hill']); ?>
              <?php endif; ?>
              <?php if (isset($_SESSION['notif'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <?= $_SESSION['notif'] ?>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['notif']); ?>
              <?php endif; ?>
            </div>
          </div>

        </div>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <?php include_once('script.php'); ?>
</body>

</html>
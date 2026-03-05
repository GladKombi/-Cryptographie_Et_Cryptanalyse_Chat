<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Affine</title>

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
            <h1 class="h3 mb-0 text-gray-800">Affine</h1>
          </div>

          <div class="row">
            <div class="col-lg-8 mx-auto">
              <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                  <h6 class="m-0 font-weight-bold text-center">Affine</h6>
                </div>
                <div class="card-body">
                  <form action="../models/traitement_affine.php" method="POST">
                    <div class="mb-3">
                      <label class="form-label fw-bold">Opération</label>
                      <select class="form-select" name="operation">
                        <option value="1">🔒 Chiffrer</option>
                        <option value="2">🔓 Déchiffrer</option>
                      </select>
                    </div>

                    <div class="mb-3">
                      <label class="form-label fw-bold">Message</label>
                      <textarea required name="message" class="form-control" rows="3" placeholder="Texte à traiter..."></textarea>
                    </div>

                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Coefiscient A (doit être première avec 26)</label>
                        <input required type="number" name="cle1" class="form-control" placeholder="Ex: 3, 5, 7, 9...">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Coefiscient B </label>
                        <input required type="number" name="cle2" class="form-control" placeholder="Ex: 8">
                      </div>
                    </div>

                    <button type="submit" name="valider" class="btn btn-danger w-100 py-2 shadow-sm fw-bold">
                      Exécuter l'algorithme Affine
                    </button>
                  </form>
                </div>
              </div>

              <?php if (isset($_SESSION['resultat_affine'])): ?>
                <div class="card border-start border-danger border-4 shadow-sm mb-4">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <label class="small text-uppercase fw-bold text-muted">Résultat :</label>
                      <a href="clear_session.php" class="btn btn-sm btn-outline-secondary">Effacer</a>
                    </div>
                    <div class="p-3 bg-light rounded font-monospace fs-5">
                      <?= htmlspecialchars($_SESSION['resultat_affine']) ?>
                    </div>
                    <?php if (isset($_SESSION['error_affine'])): ?>
                      <div class="small text-danger mt-2"><?= $_SESSION['error_affine'] ?></div>
                    <?php endif; ?>
                  </div>
                </div>
                <?php unset($_SESSION['resultat_affine']);
                unset($_SESSION['error_affine']); ?>
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
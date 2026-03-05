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
            <h1 class="h3 mb-0 text-gray-800">Algorithme de Vigenère</h1>
          </div>

          <div class="row">
            <div class="col-lg-8 mx-auto">
              <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                  <form action="../models/traitement_vigenere.php" method="POST">
                    <div class="mb-3">
                      <label class="form-label fw-bold">Opération</label>
                      <select class="form-select" name="operation">
                        <option value="1">🔒 Chiffrer</option>
                        <option value="2">🔓 Déchiffrer</option>
                      </select>
                    </div>

                    <div class="mb-3">
                      <label class="form-label fw-bold">Message</label>
                      <textarea required name="message" class="form-control" rows="3" placeholder="Message à traiter..."></textarea>
                    </div>

                    <div class="mb-4">
                      <label class="form-label fw-bold">Clé (Un Mot-clé)</label>
                      <input required type="text" name="cle" class="form-control" placeholder="Ex: MUSIQUE">
                    </div>

                    <button type="submit" name="valider" class="btn btn-danger w-100 py-2 fw-bold">
                      Exécuter Vigenère
                    </button>
                  </form>
                </div>
              </div>

              <?php if (isset($_SESSION['resultat_vigenere'])): ?>
                <div class="card border-0 shadow-sm bg-light mb-4">
                  <div class="card-body">
                    <label class="small fw-bold text-muted text-uppercase">Résultat :</label>
                    <div class="mt-2 p-3 bg-white border rounded font-monospace fs-5">
                      <?= htmlspecialchars($_SESSION['resultat_vigenere']) ?>
                    </div>
                    <div class="mt-2 text-end">
                      <a href="clear_session.php" class="btn btn-sm text-danger px-0">Effacer</a>
                    </div>
                  </div>
                </div>
                <?php unset($_SESSION['resultat_vigenere']); ?>
              <?php endif; ?>
            </div>
          </div>

        </div>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
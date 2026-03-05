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
            <h1 class="h3 mb-0 text-gray-800">Tableau de bord</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item active">Admin</li>
                <li class="breadcrumb-item">Accueil</li>
              </ol>
            </nav>
          </div>

          <div class="row g-4">
            <div class="col-md-4">
              <div class="card border-0 shadow-sm p-3">
                <div class="text-muted small">Messages Chiffrés</div>
                <div class="h4 fw-bold mb-0">0</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card border-0 shadow-sm p-3">
                <div class="text-muted small">Equipe de developpement</div>
                <div class="h4 fw-bold mb-0">0</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card border-0 shadow-sm p-3">
                <div class="text-muted small">Algorithme</div>
                <div class="h4 fw-bold mb-0">0</div>
              </div>
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
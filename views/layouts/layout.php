<?php
    require_once("lib/core.php");
?>

<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MVC</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" integrity="sha256-KTPJY0ik6ufLv48oDKCYFYaptcCX75UrmWytfSjy+tA=" crossorigin="anonymous">
</head>

<body>
    <header>
        <nav class="navbar navbar-expand navbar-light bg-light">
            <ul class="nav navbar-nav me-auto ms-5">
                <li class="nav-item">
                    <a class="nav-link" href="?module=home&action=index">Accueil</a>
                </li>
            </ul>
            
            <ul class="nav navbar-nav ms-auto me-5">
                <?php if (is_authenticated()) { ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Mon compte</a>
                        
                        <ul class="dropdown-menu dropdown-menu-light">
                            <li>
                                <a class="dropdown-item" href="?module=account&action=forums">Mes articles</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="?module=account&action=logout">Se déconnecter</a>
                            </li>
                        </ul>
                    </li>
                <?php } else { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="?module=account&action=register">S'inscrire</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?module=account&action=login">Se connecter</a>
                    </li>
                <?php } ?>
            </ul>
        </nav>
    </header>
    
    <main class="container-fluid">
        <?php
            if (isset($content))
            {
                echo($content);
            }
        ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.bundle.min.js" integrity="sha256-qFsv4wd3fI60fwah7sOZ/L3f6D0lL9IC0+E1gFH88n0=" crossorigin="anonymous"></script>
</body>
</html>
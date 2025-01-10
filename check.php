<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

    <!-- Form for clearing the log, filtering, and deleting blocked entries -->
    <form action="" method="post">
        <button type="submit" name="clear_log" class="btn btn-danger mb-3">Vider</button>
        <button type="submit" name="show_authorized" class="btn btn-primary mb-3">Afficher Autorisé</button>
        <button type="submit" name="show_blocked" class="btn btn-warning mb-3">Afficher Bloqué</button>
        <button type="submit" name="delete_blocked" class="btn btn-danger mb-3">Supprimer Bloqué</button>
    </form>

    <?php
    $filename = 'log.txt';
    $authorizedIPs = [];
    $blockedIPs = [];

    if (file_exists($filename) && is_readable($filename)) {
        $lines = file($filename);
        
        foreach ($lines as $line) {
            $data = explode("\t", $line);
            if (count($data) >= 5) {
                $ip = trim($data[1]);
                $status = trim($data[4]);
                if ($status === 'authorized') {
                    $authorizedIPs[$ip] = true; // Store unique IPs
                } elseif ($status === 'blocked') {
                    $blockedIPs[$ip] = true; // Store unique IPs
                }
            }
        }
    }

    $authorizedCount = count($authorizedIPs);
    $blockedCount = count($blockedIPs);
    ?>

    <div class="alert alert-info">
        <strong>Nombre Autorisé:</strong> <?php echo $authorizedCount; ?><br>
        <strong>Nombre Bloqué:</strong> <?php echo $blockedCount; ?>
    </div>

    <table class="table table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Date et Heure</th>
                <th>Adresse IP</th>
                <th>Pays</th>
                <th>Organisation (ISP)</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Vérifier si le bouton de vidage a été cliqué
            if (isset($_POST['clear_log'])) {
                file_put_contents($filename, "");
                header("Refresh:0");
            }

            // Vérifier si le bouton de suppression a été cliqué
            if (isset($_POST['delete_blocked'])) {
                // Read all lines and filter out blocked entries
                $lines = file($filename);
                $filtered_lines = array_filter($lines, function($line) {
                    $data = explode("\t", $line);
                    return count($data) < 5 || trim($data[4]) !== 'blocked'; // Keep lines that are not blocked
                });
                // Save the filtered lines back to the log file
                file_put_contents($filename, implode("", $filtered_lines));
                header("Refresh:0");
            }

            $showAuthorized = isset($_POST['show_authorized']);
            $showBlocked = isset($_POST['show_blocked']);

            if (file_exists($filename) && is_readable($filename)) {
                $file = fopen($filename, 'r');

                if ($file) {
                    while (($line = fgets($file)) !== false) {
                        $data = explode("\t", $line);
                        if (count($data) >= 5) {
                            $status = trim($data[4]);
                            if (($showAuthorized && $status === 'authorized') || ($showBlocked && $status === 'blocked') || (!$showAuthorized && !$showBlocked)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($data[0]) . "</td>";
                                echo "<td>" . htmlspecialchars($data[1]) . "</td>";
                                echo "<td>" . htmlspecialchars($data[2]) . "</td>";
                                echo "<td>" . htmlspecialchars($data[3]) . "</td>";
                                echo "<td>" . htmlspecialchars($status) . "</td>";
                                echo "</tr>";
                            }
                        }
                    }
                    fclose($file);
                } else {
                    echo "<tr><td colspan='5'>Erreur lors de l'ouverture du fichier de log.</td></tr>";
                }
            } else {
                echo "<tr><td colspan='5'>Le fichier de log n'existe pas ou n'est pas accessible en lecture.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>

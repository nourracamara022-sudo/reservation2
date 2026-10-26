
<?php
require_once "Connexion.php";

$demande_a_modifier = null;
$erreur = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "enregistrer";

    // Charger une demande dans le formulaire pour la modifier
    if ($action === "charger_modification") {
        $id = (int) ($_POST["id"] ?? 0);

        if ($id > 0) {
            $requete = $pdo->prepare(
                "SELECT id, nom_agent, objet, date_reunion
                 FROM demandes
                 WHERE id = ?"
            );

            $requete->execute([$id]);
            $demande_a_modifier = $requete->fetch(PDO::FETCH_ASSOC);
        }

    // Enregistrer une nouvelle demande
    } elseif ($action === "enregistrer") {
        $nom_agent = trim($_POST["nom_agent"] ?? "");
        $objet = trim($_POST["objet"] ?? "");
        $date_reunion = $_POST["date_reunion"] ?? "";

        if ($nom_agent !== "" && $objet !== "" && $date_reunion !== "") {
            $requete = $pdo->prepare(
                "INSERT INTO demandes (nom_agent, objet, date_reunion)
                 VALUES (?, ?, ?)"
            );

            $requete->execute([
                $nom_agent,
                $objet,
                $date_reunion
            ]);

            header("Location: index.php");
            exit;
        } else {
            $erreur = "Veuillez remplir tous les champs.";
        }

    // Modifier une demande existante
    } elseif ($action === "mettre_a_jour") {
        $id = (int) ($_POST["id"] ?? 0);
        $nom_agent = trim($_POST["nom_agent"] ?? "");
        $objet = trim($_POST["objet"] ?? "");
        $date_reunion = $_POST["date_reunion"] ?? "";

        if (
            $id > 0 &&
            $nom_agent !== "" &&
            $objet !== "" &&
            $date_reunion !== ""
        ) {
            $requete = $pdo->prepare(
                "UPDATE demandes
                 SET nom_agent = ?, objet = ?, date_reunion = ?
                 WHERE id = ?"
            );

            $requete->execute([
                $nom_agent,
                $objet,
                $date_reunion,
                $id
            ]);

            header("Location: index.php");
            exit;
        } else {
            $erreur = "Veuillez remplir tous les champs.";
        }

    // Supprimer une demande
    } elseif ($action === "supprimer") {
        $id = (int) ($_POST["id"] ?? 0);

        if ($id > 0) {
            $requete = $pdo->prepare(
                "DELETE FROM demandes WHERE id = ?"
            );

            $requete->execute([$id]);
        }

        header("Location: index.php");
        exit;
    }
}

// Récupérer la liste des demandes
$requete = $pdo->query(
    "SELECT id, nom_agent, objet, date_reunion
     FROM demandes
     ORDER BY id ASC"
);

$demandes = $requete->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des réservations</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="conteneur">
        <h1>Gestion des demandes de réservation</h1>

        <section class="carte">
            <h2>
                <?= $demande_a_modifier !== null
                    ? "Modifier une demande"
                    : "Nouvelle demande" ?>
            </h2>

            <?php if ($erreur !== "") : ?>
                <p><?= htmlspecialchars($erreur) ?></p>
            <?php endif; ?>

            <form method="POST" action="index.php">
                <?php if ($demande_a_modifier !== null) : ?>
                    <input type="hidden" name="action" value="mettre_a_jour">
                    <input type="hidden" name="id"
                           value="<?= (int) $demande_a_modifier["id"] ?>">
                <?php else : ?>
                    <input type="hidden" name="action" value="enregistrer">
                <?php endif; ?>

                <label for="nom_agent">Nom de l'agent</label>
                <input type="text" id="nom_agent"
                       name="nom_agent"
                       value="<?= htmlspecialchars($demande_a_modifier["nom_agent"] ?? "") ?>"
                       required>

                <label for="objet">Objet de la réunion</label>
                <input type="text" id="objet"
                       name="objet"
                       value="<?= htmlspecialchars($demande_a_modifier["objet"] ?? "") ?>"
                       required>

                <label for="date_reunion">Date de la réunion</label>
                <input type="date" id="date_reunion"
                       name="date_reunion"
                       value="<?= htmlspecialchars($demande_a_modifier["date_reunion"] ?? "") ?>"
                       required>

                <button type="submit">
                    <?= $demande_a_modifier !== null
                        ? "Enregistrer les modifications"
                        : "Enregistrer" ?>
                </button>
            </form>
        </section>

        <section class="carte">
            <h2>Liste des demandes</h2>

            <div class="tableau-conteneur">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom de l'agent</th>
                            <th>Objet</th>
                            <th>Date de la réunion</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($demandes as $demande) : ?>
                            <tr>
                                <td><?= (int) $demande["id"] ?></td>
                                <td><?= htmlspecialchars($demande["nom_agent"]) ?></td>
                                <td><?= htmlspecialchars($demande["objet"]) ?></td>
                                <td><?= htmlspecialchars($demande["date_reunion"]) ?></td>
                                <td>
                                    <div class="actions">
                                        <form method="POST" action="index.php">
                                            <input type="hidden" name="action"
                                                   value="charger_modification">
                                            <input type="hidden" name="id"
                                                   value="<?= (int) $demande["id"] ?>">
                                            <button type="submit">Modifier</button>
                                        </form>

                                        <form method="POST" action="index.php">
                                            <input type="hidden" name="action"
                                                   value="supprimer">
                                            <input type="hidden" name="id"
                                                   value="<?= (int) $demande["id"] ?>">
                                            <button type="submit">Supprimer</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (count($demandes) === 0) : ?>
                            <tr>
                                <td colspan="5">Aucune demande enregistrée.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

</body>
</html>


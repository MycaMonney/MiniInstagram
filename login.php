<?php
// Importer PDOSingleton
use Models\ARUser;
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/Css/style.css">
    <title>Connexion</title>
</head>

<body>
    <h1>Connexion</h1>
    <form action="login.php" method="post">
        <label for="username">Pseudo :</label>
        <input type="text" id="username" name="username" required>
        <button type="submit">Connexion</button>
    </form>

</body>

</html>
<?php
try {
    // Récupérer l'instance unique de PDO
    $pdo = PDOSingleton::getInstance()->getConnection();
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Vérification que les données du formulaire sont envoyées
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer le pseudo du formulaire
    $pseudo = trim($_POST['username']);

    // Vérifier que le champ pseudo est rempli
    if (!empty($pseudo)) {
        // Récupérer l'utilisateur correspondant au pseudo
        $query = $pdo->prepare("SELECT * FROM users WHERE pseudo = :pseudo");
        $query->execute([':pseudo' => $pseudo]);
        $user = $query->fetch();

        // Vérifier si l'utilisateur existe
        if ($user) {
            echo "Connexion réussie. Bienvenue, " . htmlspecialchars($user['pseudo']) . "!";
        } else {
            echo "Aucun utilisateur trouvé avec ce pseudo.";
        }
    } else {
        echo "Veuillez entrer votre pseudo.";
    }
} else {
    echo "Formulaire non soumis.";
}
?>
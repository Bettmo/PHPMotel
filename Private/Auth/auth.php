<?php
session_start();
include 'db.php';

// Henter epost og passord. PDO er database tilkoblings funksjonen fra db.php
function login($email, $password, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Første sjekk er om brukeren allerede er låst. Da får han ikke lov til å gjøre noe annet en å vente.
        if ($user['lock_until'] && strtotime($user['lock_until']) > time()) {
            return ['error' => "Kontoen din er låst til " . $user['lock_until']];
        }
        // Andre sjekk er selv primær sjekken. Hvor vi sjekker passordet og brukeren opp mot db (password_verify fordi vi encrypterte passordet)
        if (password_verify($password, $user['password'])) {
            // Nullstill feilforsøk. 
            $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, lock_until = NULL WHERE id = :id");
            $stmt->bindValue(':id', $user['id'], PDO::PARAM_INT);
            $stmt->execute();

            // Setter øktdata. Vi henter rolle, navn og id. som er de relevante for nå
            $_SESSION['bruker_id'] = $user['id'];
            $_SESSION['bruker_navn'] = $user['first_name'];
            $_SESSION['bruker_rolle'] = $user['role'];

            return ['success' => true];

            // Tredje og siste steg. Hvis vedkommende skrev feilpassord, så legger vi til 1 på failed. Hvis failed >= 3 så setter vi 1 time lock.
        } else {
            // Øk feilforsøk
            $failedAttempts = $user['failed_attempts'] + 1;
            $lockUntil = $failedAttempts >= 3 ? date('Y-m-d H:i:s', strtotime('+1 hour')) : null;

            $stmt = $pdo->prepare("UPDATE users SET failed_attempts = :attempts, lock_until = :lock_until WHERE id = :id");
            $stmt->bindValue(':attempts', $failedAttempts, PDO::PARAM_INT);
            $stmt->bindValue(':lock_until', $lockUntil, PDO::PARAM_STR);
            $stmt->bindValue(':id', $user['id'], PDO::PARAM_INT);
            $stmt->execute();

            // Her sender vi feilmeldingen til brukeren
            return ['error' => "Ugyldig e-post eller passord."];
        }
    }

    // feilmelding til brukeren, hvis vedkommende prøver å sende blankt
    return ['error' => "Ugyldig e-post eller passord."];
}

// Her sjekker vi om brukeren er logget inn, ved å se om de har en session bruker_id
function isLoggedIn() {
    return isset($_SESSION['bruker_id']);
}

// Her sjekker vi om brukeren er en admin, ved å se om de har en rolle, og at den er admin. (dette blir også en er du logget inn sjekk.)
function isAdmin() {
    return isset($_SESSION['bruker_rolle']) && $_SESSION['bruker_rolle'] === 'admin';
}

// Hvis brukeren ikke er logget inn (altså har ikke brukerid) så blir han sendt til login.php
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php?error=unauthorized");
        exit;
    }
}
// Hvis brukeren ikke er logget inn (altså har ikke brukerid) eller har rollen admin så blir han sendt til login.php
function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        header("Location: login.php?error=not_admin");
        exit;
    }
}

// Logger brukeren ut, ved å slette session dataen. 
function logout() {
    session_unset();
    session_destroy();
    header("Location: login.php?message=loggedout");
    exit;
}
?>
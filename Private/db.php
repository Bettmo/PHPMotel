<?php
/**
 * Etabler en database forbindelse med PDO.
 *
 * Denne koden kobler til en MySQL-database ved hjelp av PDO og håndterer
 * eventuelle feil som oppstår under oppkoblingen.
 *
 * @var string $DB_VERT Vertens adresse (For oss så er dette 'localhost').
 * @var string $DB_BRUKER Brukernavn for databasen.
 * @var string $DB_PASS Passord for databasen.
 * @var string $DB_NAVN Navn på databasen som skal kobles til.
 */

$DB_VERT = "localhost"; // Vertens adresse
$DB_BRUKER = "root";    // Databasebrukernavn
$DB_PASS = "";          // Databasepassord
$DB_NAVN = "Forelesning"; // Navn på databasen

try {
    // Etabler en databaseforbindelse
    $pdo = new PDO(
        "mysql:host=$DB_VERT;dbname=$DB_NAVN;charset=utf8", // Tilkoblingsstreng
        $DB_BRUKER,                                        // Brukernavn
        $DB_PASS                                           // Passord
    );
    
    // Sett PDO til å kaste unntak ved feil istedet for å cræshe
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Suksessmelding (Etter man har testet burde meldingen fjernes ettersom den kan fort bli veldig irriterende)
    // echo "Databaseforbindelse etablert.";
} catch (PDOException $e) {
    // Håndter feilen og vis feilmedlingen
    die("Databaseforbindelse mislyktes: " . $e->getMessage());
}
?>
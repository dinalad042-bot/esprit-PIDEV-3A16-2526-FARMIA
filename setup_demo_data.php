<?php

/**
 * FarmAI Demo Data Setup Script
 * Ensures a consistent state for Demo Day.
 */

$host = '127.0.0.1';
$db   = 'farmai';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "✅ Connected to database: $db\n";

    // 1. Create Default Admin if not exists
    $stmt = $pdo->prepare("SELECT id_user FROM user WHERE email = ?");
    $stmt->execute(['admin@farmai.com']);
    $adminId = $stmt->fetchColumn();

    if (!$adminId) {
        $sql = "INSERT INTO user (nom, prenom, email, password, role, cin, adresse, telephone, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        // Password is 'admin123' hashed with Symfony's default (or just plain text if using legacy, but let's assume hash)
        // For the sake of demo simplicity, if using LegacyPasswordHasher, it might be plain or md5.
        // Let's use a common Bcrypt hash for 'admin123' just in case.
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        $stmt->execute(['Admin', 'System', 'admin@farmai.com', $hash, 'ADMIN', '12345678', 'Esprit Ghazela', '55112233']);
        $adminId = $pdo->lastInsertId();
        echo "✅ Created Admin user (admin@farmai.com / admin123)\n";
    } else {
        echo "ℹ️ Admin user already exists.\n";
    }

    // 2. Create Target Ferme if not exists
    $stmt = $pdo->prepare("SELECT id_ferme FROM ferme WHERE nom_ferme = ?");
    $stmt->execute(['Ferme de Test PIDEV']);
    $fermeId = $stmt->fetchColumn();

    if (!$fermeId) {
        $sql = "INSERT INTO ferme (nom_ferme, lieu, surface, id_fermier, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['Ferme de Test PIDEV', 'Tunis, Ariana', 15.5, $adminId]);
        $fermeId = $pdo->lastInsertId();
        echo "✅ Created Demo Ferme.\n";
    }

    // 3. Create/Reset Analyse ID 1 (Crucial for Demo URLs)
    // We force ID 1 to ensure URLs like /admin/analyse/1/ai-diagnostic work
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->prepare("DELETE FROM analyse WHERE id_analyse = 1")->execute();
    
    $sql = "INSERT INTO analyse (id_analyse, id_technicien, id_ferme, date_analyse, resultat_technique, image_url) 
            VALUES (?, ?, ?, NOW(), ?, ?)";
    $stmt = $pdo->prepare($sql);
    $symptoms = "Les feuilles présentent des taches jaunes avec flétrissement progressif";
    $stmt->execute([1, $adminId, $fermeId, "Diagnostic initial pour demo AI. Symptômes: " . $symptoms, "https://images.unsplash.com/photo-1599148400037-33929497fba9?auto=format&fit=crop&q=80&w=400"]);
    echo "✅ Synchronized Analyse ID 1 for AI Demo.\n";

    // 4. Add Sample Conseil for ID 1
    $pdo->prepare("DELETE FROM conseil WHERE id_analyse = 1")->execute();
    $sql = "INSERT INTO conseil (description_conseil, priorite, id_analyse) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["Vérifier l'irrigation et appliquer un traitement antifongique dès que possible.", "HAUTE", 1]);
    $stmt->execute(["Paillage recommandé pour conserver l'humidité.", "MOYENNE", 1]);
    echo "✅ Added Demo Conseils for Analyse ID 1.\n";

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "\n🚀 SETUP COMPLETE! You can now start the demo.\n";

} catch (\PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

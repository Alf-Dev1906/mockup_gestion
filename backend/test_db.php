<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=sge_db', 'root', '');
    echo "✅ Conexión exitosa a MySQL\n";
    echo "Versión: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total usuarios: " . $result['count'] . "\n";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

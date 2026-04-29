<?php
// Desactivamos los warnings para que no rompan el JSON de la web
error_reporting(0); 

$serverName = "192.168.70.10\SQLEXPRESS"; 
$connectionOptions = array(
    "Database" => "AresFitPro_DB",
    "Uid" => "sa",
    "PWD" => "Aneto_3404",
    "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => true 
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    header('Content-Type: application/json');
    die(json_encode(["error" => "Error de conexión"]));
}

$fecha_hoy = date("Y-m-d");

// Consulta estándar
$sql = "SELECT Clase_apuntada, COUNT(*) FROM Reservas WHERE Data = ? GROUP BY Clase_apuntada";
$params = array($fecha_hoy);
$stmt = sqlsrv_query($conn, $sql, $params);

$counts = [];

if ($stmt !== false) {
    // Usamos SQLSRV_FETCH_NUMERIC para forzar los índices 0 y 1
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC)) {
        if (isset($row[0])) {
            $counts[$row[0]] = (int)$row[1];
        }
    }
}

// Si la base de datos está vacía, enviamos un objeto vacío limpio
header('Content-Type: application/json');
echo json_encode($counts);

if ($stmt) sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
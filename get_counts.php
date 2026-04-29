<?php
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
    die(json_encode(["error" => "Error de conexión", "detalles" => sqlsrv_errors()]));
}

$fecha_hoy = date("Y-m-d");

// Forzamos los nombres con [] para evitar problemas de mayúsculas
$sql = "SELECT Clase_apuntada as clase, COUNT(*) as total 
        FROM Reservas 
        WHERE Data = ? 
        GROUP BY Clase_apuntada";

$params = array($fecha_hoy);
$stmt = sqlsrv_query($conn, $sql, $params);

$counts = [];
if ($stmt) {
    // Usamos el número 1 para evitar fallos de constantes
    while($row = sqlsrv_fetch_array($stmt, 1)) {
        // Usamos los alias 'clase' y 'total' que pusimos en la consulta arriba
        $counts[$row['clase']] = (int)$row['total'];
    }
}

header('Content-Type: application/json');
echo json_encode($counts);

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
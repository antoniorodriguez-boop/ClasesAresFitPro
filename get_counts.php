<?php
// Configuración para SQL Server
$serverName = "192.168.70.10"; // Ej: "192.168.1.50"
$connectionOptions = array(
    "Database" => "AresFitPro_DB",
    "Uid" => "Administrador",             // El usuario que uséis para entrar al SQL
    "PWD" => "Aneto_3404"   // La contraseña del usuario sa
);

// Establir connexió
$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die(json_encode(["error" => "Error de connexió"]));
}

$fecha_hoy = date("Y-m-d");

// Consulta con TUS nombres de columna
$sql = "SELECT Clase_apuntada, COUNT(*) as total 
        FROM Reservas 
        WHERE Data = ? 
        GROUP BY Clase_apuntada";

$params = array($fecha_hoy);
$stmt = sqlsrv_query($conn, $sql, $params);

$counts = [];
if ($stmt) {
    while($row = sqlsrv_fetch_array($stmt, SQL_SRV_FETCH_ASSOC)) {
        $counts[$row['Clase_apuntada']] = (int)$row['total'];
    }
}

header('Content-Type: application/json');
echo json_encode($counts);

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
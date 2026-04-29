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
    die(json_encode(["error" => "Error de conexión"]));
}

$fecha_hoy = date("Y-m-d");

// Consulta simplificada
$sql = "SELECT Clase_apuntada, COUNT(*) 
        FROM Reservas 
        WHERE Data = ? 
        GROUP BY Clase_apuntada";

$params = array($fecha_hoy);
$stmt = sqlsrv_query($conn, $sql, $params);

$counts = [];
if ($stmt) {
    // Usamos el número 2 para obtener un array con números (0, 1, 2...)
    // En lugar de nombres, así no fallará nunca el "array key"
    while($row = sqlsrv_fetch_array($stmt, 2)) {
        $nombre_clase = $row[0]; // Primera columna (Clase_apuntada)
        $cantidad = (int)$row[1]; // Segunda columna (COUNT)
        $counts[$nombre_clase] = $cantidad;
    }
}

header('Content-Type: application/json');
echo json_encode($counts);

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
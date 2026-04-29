<?php
// Configuración para SQL Server
$serverName = "192.168.70.10\SQLEXPRESS"; 
$connectionOptions = array(
    "Database" => "AresFitPro_DB",
    "Uid" => "sa",
    "PWD" => "Aneto_3404",
    "CharacterSet" => "UTF-8",
    // ESTO ES LA CLAVE: Ignora el certificado SSL para que conecte en local
    "TrustServerCertificate" => true 
);

// Establecer conexión
$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    // Si falla, esto nos dirá EXACTAMENTE por qué (usuario, contraseña o red)
    header('Content-Type: application/json');
    die(json_encode([
        "error" => "Error de conexión",
        "detalles" => sqlsrv_errors()
    ]));
}

$fecha_hoy = date("Y-m-d");

// Consulta (Asegúrate de que 'Data' y 'Clase_apuntada' se escriben así en tu BD)
$sql = "SELECT Clase_apuntada, COUNT(*) as total 
        FROM Reservas 
        WHERE Data = ? 
        GROUP BY Clase_apuntada";

$params = array($fecha_hoy);
$stmt = sqlsrv_query($conn, $sql, $params);

$counts = [];
if ($stmt) {
    while($row = sqlsrv_fetch_array($stmt, 1)) { 
    $counts[$row['Clase_apuntada']] = (int)$row['total'];
}
} else {
    header('Content-Type: application/json');
    die(json_encode(["error" => "Error en la consulta SQL", "detalles" => sqlsrv_errors()]));
}

header('Content-Type: application/json');
echo json_encode($counts);

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
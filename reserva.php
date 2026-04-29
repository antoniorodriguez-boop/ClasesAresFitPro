<?php
// 1. Configuración (IGUAL que en get_counts.php)
$serverName = "192.168.70.10\SQLEXPRESS"; 
$connectionOptions = array(
    "Database" => "AresFitPro_DB",
    "Uid" => "sa",
    "PWD" => "Aneto_3404",
    "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => true // ESTO ES LO QUE TE FALTA SEGURO
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    header('Content-Type: application/json');
    die(json_encode([
        "status" => "error", 
        "message" => "Error de conexión a la BD",
        "detalles" => sqlsrv_errors()
    ]));
}

// Captura de datos
$dni = isset($_POST['dni']) ? $_POST['dni'] : null;
$clase = isset($_POST['clase_id']) ? $_POST['clase_id'] : null;
$fecha = date("Y-m-d");

// Validar Socio
$sql_user = "SELECT Nom, Cognoms FROM Socis WHERE DNI = ?";
$params_user = array($dni);
$stmt_user = sqlsrv_query($conn, $sql_user, $params_user);

if ($stmt_user && $row_user = sqlsrv_fetch_array($stmt_user, SQL_SRV_FETCH_ASSOC)) {
    // Insertar Reserva
    $sql_ins = "INSERT INTO Reservas (DNI_soci, Nom, Cognom, Clase_apuntada, Data) 
                VALUES (?, ?, ?, ?, ?)";
    $params_ins = array($dni, $row_user['Nom'], $row_user['Cognoms'], $clase, $fecha);
    
    if (sqlsrv_query($conn, $sql_ins, $params_ins)) {
        echo json_encode(["status" => "success", "message" => "Reserva OK para " . $row_user['Nom']]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al insertar", "detalles" => sqlsrv_errors()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Socio no encontrado"]);
}

sqlsrv_close($conn);
?>
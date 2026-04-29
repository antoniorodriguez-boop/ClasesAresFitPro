<?php
header('Content-Type: application/json');

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
    echo json_encode([
        "status" => "error", 
        "message" => "Error de conexión a la BD",
        "debug" => sqlsrv_errors()
    ]);
    exit;
}

$dni = isset($_POST['dni']) ? trim($_POST['dni']) : null;
$clase = isset($_POST['clase_id']) ? trim($_POST['clase_id']) : null;
$fecha = date("Y-m-d");

if (!$dni || !$clase) {
    echo json_encode(["status" => "error", "message" => "Faltan datos en el envío"]);
    exit;
}

$sql_user = "SELECT Nom, Cognoms FROM Socis WHERE DNI = ?";
$stmt_user = sqlsrv_query($conn, $sql_user, array($dni));

if ($stmt_user && $row_user = sqlsrv_fetch_array($stmt_user, SQL_SRV_FETCH_ASSOC)) {
    $sql_ins = "INSERT INTO Reservas (DNI_soci, Nom, Cognom, Clase_apuntada, Data) VALUES (?, ?, ?, ?, ?)";
    $params_ins = array($dni, $row_user['Nom'], $row_user['Cognoms'], $clase, $fecha);
    
    if (sqlsrv_query($conn, $sql_ins, $params_ins)) {
        echo json_encode(["status" => "success", "message" => "Reserva OK para " . $row_user['Nom']]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al insertar", "sql_error" => sqlsrv_errors()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Socio no encontrado: " . $dni]);
}

sqlsrv_close($conn);
?>
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
    echo json_encode(["status" => "error", "message" => "Error de conexión"]);
    exit;
}

$dni = isset($_POST['dni']) ? trim($_POST['dni']) : null;
$clase = isset($_POST['clase_id']) ? trim($_POST['clase_id']) : null;
$fecha = date("Y-m-d");

if (!$dni || !$clase) {
    echo json_encode(["status" => "error", "message" => "Faltan datos"]);
    exit;
}

// 1. COMPROBAR SI YA TIENE UNA RESERVA PARA ESTA CLASE HOY
$sql_check = "SELECT * FROM Reservas WHERE DNI_soci = ? AND Clase_apuntada = ? AND Data = ?";
$params_check = array($dni, $clase, $fecha);
$stmt_check = sqlsrv_query($conn, $sql_check, $params_check);

if ($stmt_check && sqlsrv_has_rows($stmt_check)) {
    echo json_encode(["status" => "error", "message" => "¡Ya estás apuntado a esta clase hoy!"]);
    exit; // Cortamos aquí, no seguimos con el registro
}

// 2. SI NO ESTÁ APUNTADO, BUSCAMOS SUS DATOS EN SOCIS
$sql_user = "SELECT Nom, Cognoms FROM Socis WHERE LTRIM(RTRIM(UPPER(DNI))) = LTRIM(RTRIM(UPPER(?)))";
$stmt_user = sqlsrv_query($conn, $sql_user, array($dni));

if ($stmt_user && $row_user = sqlsrv_fetch_array($stmt_user, SQLSRV_FETCH_BOTH)) { 
    
    $nom_real = !empty($row_user['Nom']) ? $row_user['Nom'] : $row_user[0];
    $cognom_real = !empty($row_user['Cognoms']) ? $row_user['Cognoms'] : $row_user[1];

    // 3. INSERTAMOS EN RESERVAS
    $sql_ins = "INSERT INTO Reservas (DNI_soci, Nom, Cognom, Clase_apuntada, Data) VALUES (?, ?, ?, ?, ?)";
    $params_ins = array($dni, $nom_real, $cognom_real, $clase, $fecha);
    
    if (sqlsrv_query($conn, $sql_ins, $params_ins)) {
        echo json_encode(["status" => "success", "message" => "Reserva OK per a " . $nom_real]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al insertar"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Soci no trobat: " . $dni]);
}

sqlsrv_close($conn);
?>
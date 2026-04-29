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
    echo json_encode(["status" => "error", "message" => "Error de conexión a la BD"]);
    exit;
}

$dni = isset($_POST['dni']) ? trim($_POST['dni']) : null;
$clase = isset($_POST['clase_id']) ? trim($_POST['clase_id']) : null;
$fecha = date("Y-m-d");

if (!$dni || !$clase) {
    echo json_encode(["status" => "error", "message" => "Faltan datos"]);
    exit;
}

// 1. Busquem el soci fent servir DNI_soci (com m'has dit)
// Fem servir UPPER per si escrius la lletra en minúscula
$sql_user = "SELECT Nom, Cognom FROM Socis WHERE UPPER(DNI_soci) = UPPER(?)";
$stmt_user = sqlsrv_query($conn, $sql_user, array($dni));

if ($stmt_user && $row_user = sqlsrv_fetch_array($stmt_user, 1)) { 
    
    $nom_soci = $row_user['Nom'];
    $cognom_soci = $row_user['Cognom'];

    // 2. Fem l'INSERT a la taula Reservas
    $sql_ins = "INSERT INTO Reservas (DNI_soci, Nom, Cognom, Clase_apuntada, Data) VALUES (?, ?, ?, ?, ?)";
    $params_ins = array($dni, $nom_soci, $cognom_soci, $clase, $fecha);
    
    if (sqlsrv_query($conn, $sql_ins, $params_ins)) {
        echo json_encode(["status" => "success", "message" => "Reserva OK per a " . $nom_soci]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al insertar reserva", "debug" => sqlsrv_errors()]);
    }
} else {
    // Si surt això, és que realment el DNI no està a la columna DNI_soci de la taula Socis
    echo json_encode(["status" => "error", "message" => "Soci no trobat amb DNI_soci: " . $dni]);
}

sqlsrv_close($conn);
?>
<?php
$serverName = "192.168.71.10";
$connectionOptions = array(
    "Database" => "AresFitPro_DB",
    "Uid" => "sa",
    "PWD" => "TuPassword123"
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die(json_encode(["status" => "error", "message" => "Error DB"]));
}

$dni = $_POST['dni'];
$clase = $_POST['clase_id'];
$fecha = date("Y-m-d");

// 1. Validar en tu tabla dbo.Socis
$sql_user = "SELECT Nom, Cognoms FROM Socis WHERE DNI = ?";
$params_user = array($dni);
$stmt_user = sqlsrv_query($conn, $sql_user, $params_user);

if ($row_user = sqlsrv_fetch_array($stmt_user, SQL_SRV_FETCH_ASSOC)) {
    $nombre_completo = $row_user['Nom'] . " " . $row_user['Cognoms'];

    // 2. Comprobar límite en dbo.Reservas
    $sql_count = "SELECT COUNT(*) as total FROM Reservas WHERE Clase_apuntada = ? AND Data = ?";
    $params_count = array($clase, $fecha);
    $stmt_count = sqlsrv_query($conn, $sql_count, $params_count);
    $row_count = sqlsrv_fetch_array($stmt_count, SQL_SRV_FETCH_ASSOC);

    if ($row_count['total'] < 20) {
        // 3. Insertar con TUS columnas: DNI_soci, Nom, Cognom, Clase_apuntada, Data
        $sql_ins = "INSERT INTO Reservas (DNI_soci, Nom, Cognom, Clase_apuntada, Data) 
                    VALUES (?, ?, ?, ?, ?)";
        $params_ins = array($dni, $row_user['Nom'], $row_user['Cognoms'], $clase, $fecha);
        
        if (sqlsrv_query($conn, $sql_ins, $params_ins)) {
            echo json_encode(["status" => "success", "message" => "Reserva OK para $nombre_completo"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Clase llena"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Socio no encontrado"]);
}

sqlsrv_close($conn);
?>
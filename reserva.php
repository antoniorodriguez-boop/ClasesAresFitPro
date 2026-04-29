<?php
// 1. Configuración idéntica a get_counts.php
$serverName = "192.168.70.10\SQLEXPRESS"; 
$connectionOptions = array(
    "Database" => "AresFitPro_DB",
    "Uid" => "sa",
    "PWD" => "Aneto_3404",
    "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => true // Crucial para que no de error SSL
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

// 2. Captura de datos (asegúrate de que el HTML manda 'dni' y 'clase_id')
$dni = isset($_POST['dni']) ? $_POST['dni'] : null;
$clase = isset($_POST['clase_id']) ? $_POST['clase_id'] : null;
$fecha = date("Y-m-d");

if (!$dni || !$clase) {
    die(json_encode(["status" => "error", "message" => "Faltan datos en el formulario"]));
}

// 1. Validar en la tabla dbo.Socis
$sql_user = "SELECT Nom, Cognoms FROM Socis WHERE DNI = ?";
$params_user = array($dni);
$stmt_user = sqlsrv_query($conn, $sql_user, $params_user);

if ($stmt_user && $row_user = sqlsrv_fetch_array($stmt_user, SQL_SRV_FETCH_ASSOC)) {
    $nombre_completo = $row_user['Nom'] . " " . $row_user['Cognoms'];

    // 2. Comprobar límite en dbo.Reservas
    $sql_count = "SELECT COUNT(*) as total FROM Reservas WHERE Clase_apuntada = ? AND Data = ?";
    $params_count = array($clase, $fecha);
    $stmt_count = sqlsrv_query($conn, $sql_count, $params_count);
    $row_count = sqlsrv_fetch_array($stmt_count, SQL_SRV_FETCH_ASSOC);

    if ($row_count['total'] < 20) {
        // 3. Insertar (He revisado que los nombres coincidan con tu lógica anterior)
        $sql_ins = "INSERT INTO Reservas (DNI_soci, Nom, Cognom, Clase_apuntada, Data) 
                    VALUES (?, ?, ?, ?, ?)";
        $params_ins = array($dni, $row_user['Nom'], $row_user['Cognoms'], $clase, $fecha);
        
        $res_ins = sqlsrv_query($conn, $sql_ins, $params_ins);
        
        if ($res_ins) {
            echo json_encode(["status" => "success", "message" => "Reserva OK para $nombre_completo"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al insertar", "detalles" => sqlsrv_errors()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Clase llena (máximo 20)"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Socio no encontrado. Revisa el DNI."]);
}

sqlsrv_close($conn);
?>
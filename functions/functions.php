<?php
# Variable de entorno
define('URL', '/APIS_jwt/');


# Funcion para validar el token obtenido con el entorno JWT
function validaToken($token) {
    # Verifica que el token no esté vacío
    if (empty($token)) {
        http_response_code(400);
        echo json_encode(['error' => 'Token no proporcionado']);
        return false;
    }

    # URL que valida si el token es válido
    $url = "http://192.184.2.20:8090/api/users";
    
    # Inicializa cURL para hacer la solicitud HTTP
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    # Configura las cabeceras de la solicitud
    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    # Ejecuta la solicitud y almacena la respuesta
    $response = curl_exec($ch);

    # Verifica si hubo errores durante la ejecución de la solicitud
    if ($response === false) {
        echo json_encode(['error' => 'Error en la solicitud: ' . curl_error($ch)]);
        return false;
    }
    
    # Cierra la sesión cURL
    curl_close($ch);
    
    # Decodifica la respuesta JSON
    $decodedResponse = json_decode($response, true);

    # Muestra la respuesta para depuración
    // echo json_encode($decodedResponse, JSON_PRETTY_PRINT);

    # Verifica si la respuesta indica que el token es inválido
    if (isset($decodedResponse['error'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Token inválido: ' . $decodedResponse['message']]);
        return false;
    }

    # Si el token es válido
    http_response_code(200);
    echo json_encode(['success' => 'Token válido']);
    return true;
}

# Funcion para obtener los resultados de las querys 
function obtenerResultados($query_psg, $pdo){
    try {
        // Prepara la consulta postgresql
        $stmt = $pdo->prepare($query_psg);
        // Ejecuta la consulta
        $stmt->execute(); 
        
        // Obtenemos los resultados como un arreglo asociativo
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Si se obtienen resultados, devolverlos como JSON
        if ($results) {
            echo json_encode($results);
        } else {
            // Si no hay resultados, devolvera un error en formato JSON
            echo json_encode(["error" => "No se encontraron resultados."]);
        }
    } catch (PDOException $e) {
        // Si ocurre un error, devolvera el error en formato JSON
        echo json_encode(["error" => "Error en la consulta: " . $e->getMessage()]);
    }
}
?>

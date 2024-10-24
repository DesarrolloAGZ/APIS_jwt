<?php
    #############################################
    #  Created by: Josue Sanchez V.
    #  Created on: 10/23/2024
    #
    #  Description:
    #  API para extraer un listado de los cultivos siempre y cuando el token que se 
    #  ingrese como cabecera sea valido.
    ############################################

    # Contenido tipo JSON
    header('Content-Type: application/json');
    include_once ('../functions/functions.php');

    # Verificar si el encabezado Authorization está presente
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) 
    {
        # Obtener el token del encabezado
        $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']); # Extrae el token
        $token = trim($token); # Limpia el token

        # Verificar si el token es válido usando la función
        if (!validaToken($token)) 
        {
            echo json_encode(["error" => "Acceso denegado. Token inválido o expirado."]);
            exit();
        }

        # Si el token es válido, continuar con la consulta a la base de datos
        include_once ('../config/connections.php');

        # Consulta PostgreSQL
        $query_psg = "SELECT 
                            c.cultivoid AS id_cultivo, 
                            c.cultivoidentificador AS identificador_cultivo, 
                            c.cultivonombre AS nombre_cultivo, 
                            c.cultivofechainicio AS fecha_creacion, 
                            t.tipolotenombre AS lote, 
                            f.frutonombre AS fruto 
                    FROM 
                            cultivos c 
                    LEFT JOIN 
                            lotes l ON l.loteid = c.loteid 
                    LEFT JOIN 
                            tiposlotes t ON t.tipoloteid = l.tipoloteid 
                    LEFT JOIN 
                            frutos f ON f.frutoid = c.frutoid 
                    WHERE 
                            c.estadoid = 1";

        $stmt = $pdo->prepare($query_psg); # Prepara la consulta
        $stmt->execute(); # Ejecuta la consulta
        
        # Obtenemos los resultados como un arreglo asociativo
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        # Si se obtienen resultados, devolverlos como JSON
        if ($results) {
            echo json_encode($results);
        } else {
            echo json_encode(["error" => "No se encontraron resultados."]);
        }

    } else {
        # No se proporcionó el token
        echo json_encode(["error" => "Acceso denegado. No se proporcionó el token."]);
    }

?>


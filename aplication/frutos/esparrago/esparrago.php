<?php
    ######################################################################
    #  Created by: Josue Sanchez V.
    #  Created on: 16/11/2024
    #
    #  Description:
    #  Este es el menú de las Apis para el fruto de esparrago para
    #  controlar y validar las peticiones.
    ######################################################################

    # Contenido tipo JSON
    header('Content-Type: application/json');
    include_once '../../../functions/functions.php';

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

        # Verificar el método de la solicitud (GET o POST)
        $request_method = $_SERVER['REQUEST_METHOD'];

        if ($request_method === 'GET') 
        {
            
            $continua = false;
            if(isset($_GET['reporte'])){
                $continua = true;
                $reporte = $_GET['reporte'];
                # Se llama al archivo de conexion a la BD
                include_once '../../../config/connections.php';
            }
            else
            {
                # Si no hay ninguno de los parámetros, manda algo por defecto
                echo json_encode(["message" => "No se especificó el parámetro reporte."]);
            }
            
            if($continua == true)
            {
                switch ($reporte) {
                    case 'producto-procesado':
                        include ('./productoProcesado.php');
                        break;
                        
                    default:
                        # Si no hay ninguno de los parámetros, manda algo por defecto
                        echo json_encode(["message" => "Reporte no encontrado."]);
                        break;
                }
            }
        } 
        else 
        {
            # Maneja otros métodos HTTP
            echo json_encode(["error" => "Método no permitido."]);
            http_response_code(405); // 405 Method Not Allowed
        }
    } 
    else 
    {
        # No se proporcionó el token
        echo json_encode(["error" => "Acceso denegado. No se proporcionó el token."]);
    }
?>
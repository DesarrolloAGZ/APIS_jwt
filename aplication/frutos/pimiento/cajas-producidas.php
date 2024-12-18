<?php
    ######################################################################
    #  Created by: Josue Sanchez V.
    #  Created on: 20/11/2024
    #
    #  Description:
    #  Este es el archivo para obtener el reporte de cajas producidas
    #  de Pimiento.
    ######################################################################
    
    # Tempo de espera del PHP
    set_time_limit(60000);

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
            $GLOBALS['data']['token'] = "Acceso denegado. Token inválido o expirado.";
            echo json_encode($GLOBALS['data']);
            exit();
        }

        # Verificar el método de la solicitud (GET o POST)
        $request_method = $_SERVER['REQUEST_METHOD'];

        if ($request_method === 'GET') 
        {
            # Se llama al archivo de conexion a la BD
            include_once '../../../config/connections.php';
            
            if(filter_input(INPUT_GET,'fechaInicio') && filter_input(INPUT_GET,'fechaFin')){
                # Expresión regular para validar el formato dd/mm/yyyy
                $regexFecha = '/^\d{2}\/\d{2}\/\d{4}$/';
                
                # Parametros para la query del reporte
                $fechaInicio = filter_input(INPUT_GET,'fechaInicio'); # Fecha inicio del reporte
                $fechaFin = filter_input(INPUT_GET,'fechaFin'); # Fecha fin del reporte
                $empresa = filter_input(INPUT_GET,'empresa'); # empresa AGZ o AGN
        
                # Validar la fecha de inicio
                if ($fechaInicio && preg_match($regexFecha, $fechaInicio) && $fechaFin && preg_match($regexFecha, $fechaFin)) 
                {
                    if($empresa){
                        $fechaInicio = $fechaInicio." 05:00:00.00"; # Fecha inicio del reporte
                        $fechaFinTimestamp = strtotime(str_replace('/', '-', $fechaFin) . ' +1 day'); # Cambiar '/' a '-' para strtotime
                        $fechaFin = date('d/m/Y', $fechaFinTimestamp) . " 04:59:59.00"; 

                        /*____________ PARAMETROS ____________*/
                        /*#*/
                        /*#*/   $fruto = 1; # Pimiento 
                        /*#*/
                        
                        $GLOBALS['data']['reporte'] = "Cajas producidas pimiento.";

                        $queryIds = " SELECT "
                                        . " dp.fechacreacion::DATE AS fecha, "
                                        . " cpa.productoidempaque AS id_empaque, "
                                        . " cpa.productonombre AS producto, "
                                        . " cpa.frutoNombre AS fruto, "
                                        . " cpa.tamanonombre AS tamaño, "
                                        . " COUNT(dp.detallepalletid) AS cajas, "
                                        . " ROUND(CASE WHEN COUNT(dp.detallepalletid) = 0 THEN 0 ELSE SUM(cip.kilosprocesados)::DECIMAL / COUNT(dp.detallepalletid) END, 2) AS peso, "
                                        . " cpa.peso AS kilos_empacables, "
                                        . " (cpa.peso * COUNT(dp.detallepalletid)) AS kilos_pagables, "
                                        . " cip.bandanombre AS banda "
                                    . " FROM "
                                        . " catalogoidentificadoresproduccion cip "
                                    . " LEFT JOIN "
                                        . " detallePalletsPIM dp ON cip.identificadorproduccion = dp.identificadorproduccion "
                                    . " LEFT JOIN "
                                        . " catalogoproductoactivo cpa ON dp.productoid = cpa.productoid "
                                    . " WHERE "
                                        . " cip.fechaproceso BETWEEN '".$fechaInicio."' AND '".$fechaFin."' "
                                        . " AND cip.frutoId = " . $fruto
                                        . " AND (dp.estadoid = 1 OR dp.estadoid IS NULL) "
                                    . " GROUP BY "
                                        . " cip.identificadorproduccion, "
                                        . " cip.bandanombre, "
                                        . " dp.fechacreacion::DATE, "
                                        . " cpa.productoidempaque, "
                                        . " cpa.productonombre, "
                                        . " cpa.frutoNombre, "
                                        . " cpa.tamanonombre, "
                                        . " cip.kilosprocesados, "
                                        . " cpa.peso ";

                        if($empresa === "AGN"){ # Si la empreas es AGN es agrizar Norte (AGZ2)
                            obtenerResultadosN($queryIds, $pdoN);     
                        }elseif ($empresa === 'AGZ'){ # Si la empreas es AGZ es agrizar Silao (AGZ1)
                            obtenerResultados($queryIds, $pdo);
                        } else{
                            $GLOBALS['data']['error'] = "El nombre de la empresa es incorrecto. Sólo se admite 'AGN' o 'AGZ'";
                        }
                    }else{
                        $GLOBALS['data']['error'] = "No se especificó el parametro 'empresa' correctamente.";
                    }
                }
                else
                {
                    $GLOBALS['data']['error'] = "Formato de fechas no válido. Debe tener el formato dd/mm/yyyy.";
                }
            }
            else
            {
                $GLOBALS['data']['error'] = "No se especificó el parametro 'fechaInicio' o 'fechaFin' correctamente.";
            }
        } 
        else 
        {
            # Maneja otros métodos HTTP
            $GLOBALS['data']['error'] = "Método no permitido.";
            http_response_code(405); // 405 Method Not Allowed
        }
    } 
    else 
    {
        # No se proporcionó el token
        $GLOBALS['data']['token'] = "Acceso denegado. No se proporcionó el token.";
    }
    echo json_encode($GLOBALS['data']);
?>
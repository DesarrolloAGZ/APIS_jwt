<?php
    ######################################################################
    #  Created by: Josue Sanchez V.
    #  Created on: 16/11/2024
    #
    #  Description:
    #  Este es el archivo para obtener el reporte de producto procesado
    #  de Pimiento.
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
            $GLOBALS['data']['token'] = 'Acceso denegado. Token inválido o expirado.';
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
        
                // Validar la fecha de inicio
                if ($fechaInicio && preg_match($regexFecha, $fechaInicio) && $fechaFin && preg_match($regexFecha, $fechaFin)) 
                {
                    $fechaFin = explode("/",$fechaFin);
                    $banda = '1,2,3,4'; # (PIMIENTO LINEA 1, PIMIENTO LINEA 2, PIMIENTO LINEA 3, PIMIENTO LINEA 4)
                    $fruto = 1; # Pimiento
                    $detallado = 0; # No detallado
                    $embolsado = 0; # No embolsado
                    $categoria = 0; # Sin categoria
                    $recepcion = '5,8,10'; # (GRANEL PARA MADURACION, DEVOLUCION, TERCERA)
        
                    $GLOBALS['data']['reporte'] = 'Producto procesado pimiento.';
        
                    $query_psg = "SELECT "
                                    . " fechaproceso::date AS fecha_proceso, "
                                    . " identificadorproduccion AS id_produccion, "
                                    . " agricultoridentificador AS id_agricultor, "
                                    . " cultivonombre AS cultivo, "
                                    . " colornombre AS color_pimiento, "
                                    . " productonombre AS nombre_producto, "
                                    . " SUM(cajasprocesadas) AS cajas, "
                                    . " SUM(kilosprocesados) AS kilos, "
                                    . " bandanombre AS banda, " 
                                    . " tiporecepcionnombre AS tipo_recepcion, " 
                                    . " frutonombre AS fruto, "
                                    . " categorianombre AS categoria "
                                . " FROM "
                                    . " catalogoidentificadoresproduccion "
                                . " WHERE "
                                    . " fechaproceso BETWEEN '".$fechaInicio." 05:00:00.000000' AND '".date('j/n/y',strtotime($fechaFin[2]."-".$fechaFin[1]."-".$fechaFin[0]." + 1days"))." 04:59:59.999999' " 
                                    . " AND identificadorproduccion NOT IN "
                                        . " (SELECT "
                                            . " identificadorproduccion "
                                        . " FROM " 
                                            . " productoprocesobanda ppb, "
                                            . " catalogoproductoactivo cpa "
                                        . " WHERE "
                                            . " ppb.estadoid = 1 "
                                            . " AND ppb.fechaproceso between '".$fechaInicio." 05:00:00.0000' AND '".date('j/n/y',strtotime($fechaFin[2]."-".$fechaFin[1]."-".$fechaFin[0]." + 1days"))." 04:59:59.999999' "
                                            . " AND ppb.banderaterminado = 't' " 
                                            . " AND ppb.productoid = cpa.productoid "
                                            . " AND cpa.frutoid = ".$fruto." "
                                            . " AND ppb.bandaid in  (".$banda. ") "
                                        . " ) "
                                    . " AND frutoid = ".$fruto." "
                                    . " AND bandaid in (".$banda.") "
                                    . " AND tiporecepcionid in (".$recepcion.") "
                                . " GROUP BY "
                                    . " fechaproceso::date, "
                                    . " identificadorproduccion, "
                                    . " agricultoridentificador, "
                                    . " cultivonombre, "
                                    . " colornombre, "
                                    . " productonombre, "
                                    . " bandanombre, "
                                    . " tiporecepcionnombre, "
                                    . " frutonombre, "
                                    . " categorianombre "
                                . " order by "
                                    . " identificadorproduccion";
                    obtenerResultados($query_psg, $pdo);
                }
                else
                {
                    $GLOBALS['data']['error'] = 'Formato de fechas no válido. Debe tener el formato dd/mm/yyyy.';
                }
            }
            else
            {
                $GLOBALS['data']['error'] = 'No se especificó el parametro fechaInicio y fechaFin.';
            }
        } 
        else 
        {
            # Maneja otros métodos HTTP
            $GLOBALS['data']['error'] = 'Método no permitido.';
            http_response_code(405); // 405 Method Not Allowed
        }
    } 
    else 
    {
        # No se proporcionó el token
        $GLOBALS['data']['token'] = 'Acceso denegado. No se proporcionó el token.';
    }
    echo json_encode($GLOBALS['data']);
?>
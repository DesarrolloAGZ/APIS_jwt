<?php
    ######################################################################
    #  Created by: Josue Sanchez V.
    #  Created on: 16/11/2024
    #
    #  Description:
    #  Este es el archivo para obtener el reporte de producto recepcion
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
                    // $fechaFin = explode("/",$fechaFin);
                    $fruto = 1; # Pimiento
        
                    $GLOBALS['data']['reporte'] = 'Producto recepcion pimiento.';
        
                    $query_psg = "SELECT "
                                    . " cr.recepcionFecha as fecha, "
                                    . " cr.frutonombre as fruto, "
                                    . " cr.productonombre as producto, "
                                    . " sum(cr.detalleRecepcionCajas) as cajas, "
                                    . " sum(cr.detalleRecepcionKgs) as kilos, "
                                    . " cr.tamanoNombre as tamaño, "
                                    . " cr._bandanombre as banda, "
                                    . " COALESCE(v.nombre, 'sin variedad') AS variedad  "
                                . " FROM " 
                                    . " vw_reporterecepcionesdetallado cr "
                                . " left join "
                                    . " relacion_lote_variedad rlv on cr.loteid = rlv.loteid "
                                . " left join "
                                    . " variedades v on v.variedadid = rlv.variedadid "
                                . " WHERE "
                                    . " cr.recepcionfecha between '{$fechaInicio}' and '{$fechaFin}' "
                                    . " AND cr.frutoid = ".$fruto
                                . " GROUP BY "
                                    . " cr.recepcionFecha, "
                                    . " cr.frutonombre, "
                                    . " cr.productonombre, "
                                    . " cr.tamanoNombre, "
                                    . " cr._bandaNombre, " 
                                    . " v.nombre";
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
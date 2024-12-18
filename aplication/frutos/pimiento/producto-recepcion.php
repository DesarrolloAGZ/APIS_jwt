<?php
    ######################################################################
    #  Created by: Josue Sanchez V.
    #  Created on: 16/11/2024
    #
    #  Description:
    #  Este es el archivo para obtener el reporte de producto recepcion
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
        
                // Validar la fecha de inicio
                if ($fechaInicio && preg_match($regexFecha, $fechaInicio) && $fechaFin && preg_match($regexFecha, $fechaFin)) 
                {
                    if($empresa){
                        
                        /*____________ PARAMETROS ____________*/
                        /*#*/
                        /*#*/   $fruto = 1; # Pimiento 
                        /*#*/   $reporteRecepcion = ($empresa === "AGN") ? "vw_reporterecepcionesdetallado_agn" : "vw_reporterecepcionesdetallado";
                        /*#*/   $bandaParametro = ($empresa === "AGN") ? "bandanombre" : "_bandaNombre";
            
                        $GLOBALS['data']['reporte'] = "Producto recepcion pimiento.";
            
                        $query_psg = "SELECT "
                                        . " cr.recepcionFecha as fecha, "
                                        . " cr.frutonombre as fruto, "
                                        . " cr.productonombre as producto, "
                                        . " sum(cr.detalleRecepcionCajas) as cajas, "
                                        . " sum(cr.detalleRecepcionKgs) as kilos, "
                                        . " cr.tamanoNombre as tamaño, "
                                        . " cr.".$bandaParametro." as banda "
                                    . " FROM " 
                                        . " ".$reporteRecepcion." cr "
                                    . " WHERE "
                                        . " cr.recepcionfecha between '{$fechaInicio}' and '{$fechaFin}' "
                                        . " AND cr.frutoid = ".$fruto
                                    . " GROUP BY "
                                        . " cr.recepcionFecha, "
                                        . " cr.frutonombre, "
                                        . " cr.productonombre, "
                                        . " cr.tamanoNombre, "
                                        . " cr.".$bandaParametro;
                        
                        if($empresa === "AGN"){ # Si la empreas es AGN es agrizar Norte (AGZ2)
                            obtenerResultadosN($query_psg, $pdoN);     
                        }elseif ($empresa === 'AGZ'){ # Si la empreas es AGZ es agrizar Silao (AGZ1)
                            obtenerResultados($query_psg, $pdo);
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
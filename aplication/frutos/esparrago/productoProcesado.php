<?php
    ######################################################################
    #  Created by: Josue Sanchez V.
    #  Created on: 16/11/2024
    #
    #  Description:
    #  Este es el archivo para obtener el reporte de producto procesado
    #  de esparrago.
    ######################################################################

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
            $banda = 0; # Sin banda
            $fruto = 3; # Esparrago
            $detallado = 0; # No detallado
            $embolsado = 0; # No embolsado
            $categoria = 0; # Sin categoria

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
                                . " ) "
                            . " AND frutoid = ".$fruto." "
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
            echo json_encode(["error" => "Formato de fechas no válido. Debe tener el formato dd/mm/yyyy."]);
        }
    }
    else
    {
        echo json_encode(["error" => "No se especificó el parametro fechaInicio y fechaFin."]);
    }
?>
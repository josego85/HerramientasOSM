<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <title>OSM</title>
  </head>
  <body>
    <h1><center>Listado de correcciones para OSM</h1></center>
    <?php
        $fp = fopen("../Datos/doctor.csv" , "r");
        $contador = 1;
        while (($data = fgetcsv ($fp, 1000, ",")) !== FALSE){
            $id_osm = $data[0];
            if($id_osm != 'osm_id'){
                $descripcion = $data[1];
                // Puede ser node o way en la url.
                //$link_parte = 'https://www.openstreetmap.org/edit?editor=id&node=' . $id_osm;
                $link_parte = 'https://www.openstreetmap.org/edit?editor=id&way=' . $id_osm;
                $link = $contador . ".- " . $id_osm . ' <a href="' . $link_parte . '" target = "_blank" >'. $descripcion .'</a>';
                echo $link;
                echo "<br /><br />";
                $contador++;
            }
        }
        fclose($fp);
    ?>
  </body>
</html>

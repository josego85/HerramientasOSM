<?php
    // Script que saca de un directorio donde se encuentran archivos Poly
    // y inserta en una base de datos MySQL en una tabla distritos.
    // Estos archivos son distritos de Paraguay.
    // Autor: josego
    // mail: josego85@gmail.coma
    // Fecha creacion: 10 de diciembre de 2017 a las 19:10 hs


    // Carpeta donde se encuentran los archivos Poly.
    $rutaDirectorio = "/home/proyectosbeta/Descargas/Distritos/Poly/";
    $listadoArchivos = obtenerListadoDeArchivos($rutaDirectorio);

    $contador = 1;
    $sentencias = "";
    foreach($listadoArchivos as $indice => $value){
        $lineas = array();
        $nombreArchivo = $listadoArchivos[$indice]['Nombre'];
        $rutaArchivo = $listadoArchivos[$indice]['DirectorioNombre'];

        // Obtener el archivo poly.
        $file = fopen($rutaArchivo, 'r');
        while(!feof($file)) {
            $name = fgets($file);
            $lineas[] = $name . ",";
        }
        fclose($file);

        // Todas las lineas quedan almacenadas en $lineas
        // Ahora eliminamos las dos primeras filas y las tres ultimas.
        $ultimaLinea = count($lineas);
        unset($lineas[0]);
        unset($lineas[1]);
        unset($lineas[$ultimaLinea - 1]);
        unset($lineas[$ultimaLinea - 2]);
        unset($lineas[$ultimaLinea - 3]);

        // Borrar la ultima coma de la ultima linea.
        $ultimaLineaTmp = $lineas[count($lineas) + 1];
        $ultimaLineaTmp = substr($ultimaLineaTmp, 0, -1);
        $lineas[count($lineas) + 1] = $ultimaLineaTmp;

        // Cargar todas la lineas en un string.
        $stringPoly = "";
        foreach($lineas as $linea){
            $stringPoly.= $linea;
        }

        // Armar sentencia SQL.
        $sentencias .= "SET @g" . $contador . "= 'POLYGON((";
        $sentencias .= $stringPoly . "))';\n";
        $sentencias .= "INSERT INTO distritos (distrito_nombre, geom) VALUES ('"
          . $nombreArchivo . "', ST_GeomFromText(@g" . $contador . "));\n\n";
        $contador++;
    }
    // Conectar a una base de datos MySQL e insertar sentencias SQL.
    ejecutarSentencias($sentencias);


    /**
     * Funcion que se obtiene un listado de archuvos de un directorio. No
     * se contempla las subcarpetas.
     */
    function obtenerListadoDeArchivos($directorio){
        // Array en el que obtendremos los resultados
        $res = array();

        // Agregamos la barra invertida al final en caso de que no exista
        if(substr($directorio, -1) != "/")
            $directorio .= "/";

        // Creamos un puntero al directorio y obtenemos el listado de archivos
        $dir = @dir($directorio) or die("getFileList: Error abriendo el directorio $directorio para leerlo");
        while(($archivo = $dir->read()) !== false) {
            // Obviamos los archivos ocultos
             if($archivo[0] == "."){
                 continue;
             }
             // Se quita la extension .poly.
             $nombreArchivo = str_replace(".poly", "", $archivo);

             if(is_dir($directorio . $archivo)) {
                 $res[] = array(
                     "Nombre" => $nombreArchivo,
                     "DirectorioNombre" => $directorio . $archivo . "/"
                 );
              } else if (is_readable($directorio . $archivo)) {
                    $res[] = array(
                        "Nombre" => $nombreArchivo,
                        "DirectorioNombre" => $directorio . $archivo
                    );
              }
         }
         $dir->close();
         return $res;
     }

     /**
      * Funcion para conectar a una base de datos MySQL e insertar sentencias SQL.
      */
      function ejecutarSentencias($sentencias){
          $conn = new mysqli("localhost", "root", "123456", "paraguay");

          // Checkear coneccion.
          if ($conn->connect_error) {
              die("Coneccion fallida: " . $conn->connect_error);
          }

          if($conn->multi_query($sentencias) === TRUE){
              echo "\nExito. ";
          }else{
               echo "\nError: " . $conn->error;
          }

          // Se cierra la coneccion de la base de datos.
          $conn->close();
      }

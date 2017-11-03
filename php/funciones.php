<?php
	  //Convierte un string en numeros para calcular mejor la posicion (A = 1, B = 2, ...)
	  //Nota: solo admite caracteres ingleses.
	  function getCodeOfString($string){
		  $num = 0;
		  foreach(str_split($string) as $char){
			if($num > 0)
				$num *= 26;
			
			$num += ((int) ord($char)) - 64;
		  }
		return $num;
	  }
	  
	  //Convierte el codigo devuelta a string
	  function getStringOfCode($num){
		  $string = "";
		  while($num > 0){
			  $mod = ($num % 26);
			  if($mod == 0)
				  $mod = 26;
			  $char = chr($mod + 64);
			  $string = $char.$string;
			  $num-= $mod;
			  $num/=26;
		  }
		  return $string;
	  }
	  //Obtener un array con la posicion pasada. ([0] = num, [1] = caracter)
      function getPosition($string){
        return explode("-", $string);
      }
	  //Obtener un array con el codigo de la posicion pasada. ([0] = num, [1] = caracter)
	  function getPositionCode($string, $dif = 0){
		$positions = getPosition($string);
		$positions[0] = $positions[0] - $dif;
		$positions[1] = getCodeOfString($positions[1]) - $dif;
        return $positions;
      }
	  //Comprobar que la coordenada no se salga del tablero
      function comprobarQueNoSeSalga($string, $cantColumnaTablero, $cantFilaTablero){
        $posicion = getPositionCode($string, 0);
        $numAscii = $posicion[0];
        $numMinAscii = 1;
        $numMaxAscii = $cantFilaTablero;
        $charAscii = $posicion[1];
        $charMinAscii = getCodeOfString("A");
        $charMaxAscii = $cantColumnaTablero;
		
        return ($numAscii >= $numMinAscii && $numAscii <= $numMaxAscii && $charAscii >= $charMinAscii && $charAscii <= $charMaxAscii);
      }

      //Devuelve false si el formato de las coordenadas es incorrecto,
	  //en caso contrario devuelve el formato correcto y ordenado (num-char).
	  //posibles formatos: 1b, b1, 1-b, b-1
	  function validarFormatoCoordenades($string){
		  $cadenas = str_split(strtoupper($string));
		  $finNumeros = false;
		  $finChar = false;
		  $num = "";
		  $char = "";
		  
		  foreach ($cadenas as $caracter){
		  	  //Si es un numero
			  if(is_numeric($caracter)){
			  	if(!empty($char) && !$finChar) $finChar = true; //Comprobar que se ha terminado de escribir los caracteres
			  	if(!$finNumeros){ //Si aun se esta escribiendo numeros
			  		$num .= $caracter;
			  	}else return false;
			  //Si es un caracter
			  }else if(ord($caracter) >= 65 && ord($caracter) <= 90){
			  	if(!empty($num) && !$finNumeros) $finNumeros = true; //Comprobar que se ha terminado de escribir los numeros
			  	if(!$finChar){ //Si aun se esta escribiendo caracteres
			  		$char .= $caracter;
			  	}else return false;
			  //Si no esta entre A-Z ni es numero
			  }else{
			  	if(!empty($num) && !$finNumeros) $finNumeros = true; //Comprobar que se ha terminado de escribir los numeros
			  	else if(!empty($char) && !$finChar) $finChar = true; //Comprobar que se ha terminado de escribir los caracteres
			  	else return false;
			  }
		  }
		  return $num."-".$char;
	  }

	  //Crea un tablero vacio
      function crearTablero($filas, $columnas){
			$_SESSION['tablero'] = array();
			for($i = 0; $i < $filas; $i++){
				$fila = array();
				for($e = 0; $e < $columnas; $e++){
					$fila[] = -1;
				}
				$_SESSION['tablero'][] = $fila;
			}
		}

		//Genera barcos aleatoriamente
		function setBarcosAleatorios($cant = 1, $minLength = 1, $maxLength = 5){
			$barcos = array();
			for($i = 0; $i < $cant; $i++){
				$barcos[] = rand($minLength, $maxLength);
			}
			rsort($barcos);
			$_SESSION['barcosActuales'] = $barcos; //Contiene los barcos que estan en flote
		}

		//Coloca los barcos aleatoriamente en el tablero
		function colocarBarcos(){
			if(!isset($_SESSION['barcosActuales'])||empty($_SESSION['barcosActuales'])) return false;

			$barcos = $_SESSION['barcosActuales']; //Contiene los barcos que sigen en flote

			$_SESSION['estadoBarcos'] = $barcos; //Contiene el largo de cada barco
			$_SESSION['finPartida'] = 0; //Contiene el largo total de todos los barcos
			$_SESSION['intentos'] = 0; //Contiene los disparos fallidos

			$filas = count($_SESSION['tablero']);
			$columnas = count($_SESSION['tablero'][0]);

			foreach ($barcos as $id => $largo) {
				$intentos = 0;

				$direccion = mt_rand(0,1); //0 vertical, 1 horizontal
				//En rango se almacena el margen donde el barco se puede meter en el tablero sin salirse de el
				$rango1 = ($direccion == 0 ? $filas : $columnas) - $largo;
				$rango2 = ($direccion == 0 ? $columnas : $filas) - 1;
				//En posicion almacenan la posicion del barco
				$posicion1;
				$posicion2;
			
				do{
					//Cambiar la direccion si no encuentra un lugar donde ponerse o no cabe en el tablero
					if($intentos == 1000 || ($intentos < 1000 && $rango1 < 0)){
						$direccion = $direccion == 1 ? 0 : 1;
						$rango1 = ($direccion == 0 ? $filas : $columnas) - $largo;
						$rango2 = ($direccion == 0 ? $columnas : $filas) - 1;
					}else if($intentos == 2000 || $rango1 < 0) {
						unset($_SESSION['tablero']);
						unset($_SESSION['estadoBarcos']);
						unset($_SESSION['barcosActuales']);
						unset($_SESSION['finPartida']);
						unset($_SESSION['intentos']);
						return false;
					}

					$intentos ++;

					$posicion1 = mt_rand(0, $rango1);
					$posicion2 = rand(0, $rango2);
				}while(!sePuedeColocarBarco($posicion1, $posicion2, $largo, $direccion));
				colocarBarco($posicion1, $posicion2, $largo, $direccion, $id);
			}
			return true;
		}

		//Comprueva si el barco cabe en la posicion indicada
		function sePuedeColocarBarco($posicion1, $posicion2, $largo, $direccion){
			for($i = 0; $i < $largo; $i++){
				if($direccion == 0){
					if($_SESSION['tablero'][$posicion1 + $i][$posicion2] != -1) return false;
				}else{
					if($_SESSION['tablero'][$posicion2][$posicion1 + $i] != -1) return false;
				}
			}
			return true;
		}

		//Coloca el barco en pa posicion indicada
		function colocarBarco($posicion1, $posicion2, $largo, $direccion, $id){
			$_SESSION['finPartida'] += $largo;
			for($i = 0; $i < $largo; $i++){
				if($direccion == 0){
					$_SESSION['tablero'][$posicion1 + $i][$posicion2] = $id;
				}else{
					$_SESSION['tablero'][$posicion2][$posicion1 + $i] = $id;
				}
			}
		}

		//Disparar a una coordenada
		function disparar($coordenadas){
			$coordenadas = getPositionCode($coordenadas, 1);
			$barco = explode(" ", $_SESSION['tablero'][$coordenadas[1]][$coordenadas[0]]);
			if(count($barco) == 1){
				$id = $barco[0];
				if($id != -1){
					$_SESSION['estadoBarcos'][$id] --;
					$_SESSION['finPartida'] --;

					if($_SESSION['estadoBarcos'][$id] > 0){ //tocado
						$_SESSION['tablero'][$coordenadas[1]][$coordenadas[0]] = $id." t";
					}else{ //hundido
						hundirBarco($id);
					}
					if($_SESSION['finPartida'] <= 0){
						finJuego();
					}
				}else{ //agua
					$_SESSION['intentos'] ++;
					$_SESSION['tablero'][$coordenadas[1]][$coordenadas[0]] = $id." a";
				}
			}else return false;
			return true;
		}

		//Hundir un barco por id
		function hundirBarco($idBarco){
			unset($_SESSION['barcosActuales'][$idBarco]);
			foreach ($_SESSION['tablero'] as $indexFila => $fila) {
				foreach ($fila as $indexColumnas => $valor) {
					$id = explode(" ", $valor)[0];
					if($idBarco == $id){
						$_SESSION['tablero'][$indexFila][$indexColumnas] = $id." h";
					}
				}
			}
		}

		//Muestra el tablero
		function mostrarTablero(){
			echo "<table>";
			echo "<tr>";
			for($i = 0; $i <= count($_SESSION['tablero'][0]); $i++)
				if($i==0)
					echo "<td><span></span></td>";
				else
					echo "<td><span>$i</span></td>";
			echo "<tr>";

			foreach ($_SESSION['tablero'] as $indexFila => $fila) {
				$letraFila = getStringOfCode($indexFila+1);
				echo "<tr>";
				echo "<td><span>$letraFila</span></td>";
				foreach ($fila as $indexColumnas => $valor) {
					$datos = explode(" ", $valor);
					$class = "celdas";
					if(count($datos) == 2){
						switch ($datos[1]) {
							case 'a':
								$class .= " agua";
								break;
							case 't':
								$class .= " tocado";
								break;
							case 'h':
								$class .= " hundido";
								break;
						}
					}

					if(count($datos) == 1 && $_SESSION['finPartida'] > 0)
						echo "<td><a href='index.php?posicion=".$letraFila.($indexColumnas + 1)."' class='$class'></a></td>";
					else
						echo "<td><span class='$class'></span></td>";
				}
				echo "</tr>";
			}
			echo "</table>";
		}

		//Muestra el tablero con la posicion de los barcos (debug)
		function mostrarTablero2(){
			echo "<table>";
			echo "<tr>";
			for($i = 0; $i <= count($_SESSION['tablero'][0]); $i++)
				if($i==0)
					echo "<td><span></span></td>";
				else
					echo "<td><span>$i</span></td>";
			echo "<tr>";

			foreach ($_SESSION['tablero'] as $indexFila => $fila) {
				$letraFila = getStringOfCode($indexFila+1);
				echo "<tr>";
				echo "<td><span>$letraFila</span></td>";
				foreach ($fila as $indexColumnas => $valor) {
					$datos = explode(" ", $valor);
					echo "<td><span>".($datos[0] == -1 ? "":$datos[0])."</td></span>";
				}
				echo "</tr>";
			}
			echo "</table>";
		}

		//Metodo que se llama cuando se finaliza el juego
		function finJuego(){
			$disparos = $_SESSION['intentos'];
			addMensaje("<script>setTimeout(function(){ alert('Has finalizado el juego. \\n\\nDisparos Fallidos: $disparos'); }, 100);</script>");
		}

		//Resetear el juego
		function resetear(){
			session_destroy();
			session_start();
		}

		//Redirige al index
		function recargarIndex(){
			header("Location: index.php");
			die(); //detiene la ejecucion del php
		}

		//Añade un mensaje para mostrarlo
		function addMensaje($mensaje){
			if(isset($_SESSION['mensaje']))
				$_SESSION['mensaje'] .= $mensaje;
			else
				$_SESSION['mensaje'] = $mensaje;
		}

		//obtiene cuantos barcos hay y de que largo
		function getCantBarcos(){
			$cantidad = array();
			$barcos = $_SESSION['barcosActuales'];

			foreach ($barcos as $key => $value) {
				if(array_key_exists($value, $cantidad)){
					$cantidad[$value] ++;
				}else{
					$cantidad[$value] = 1;
				}
			}

			return $cantidad;
		}
?>
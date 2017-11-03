<?php
	session_start();
?>
<html>
<head>
	<style type="text/css">
		body {
		    margin: 0;
		    background-color: #f2f2f2;
		}
		a{
			text-decoration: none;
		}
		table {
			border-collapse: collapse;
			padding: 0;
		}
		table{
			padding: 20px;
			display: inline-block;
			background-color: white;
			border-radius: 15px;
		}
		td{
			padding: 2px;
			border: 1px solid black;
		}
		table a{
			padding: 10px;
			margin: 0;
			display: block;
			background-color: white;
		}
		span{
			padding: 10px;
			margin: 0;
			display: block;
		}
		.celdas{
			height: 18px;
		}
		.tocado{
			background-color: orange;
		}
		.hundido{
			background-color: red;
		}
		.agua{
			background-color: blue;
		}
		.boton  {
    		font-size: 18px;
		    display: inline-block;
		    padding: 8px 16px;
		    border-radius: 25px;
		    background-color: #4CAF50;
		    color: white;
		    border:none;
		}

		.boton:hover {
		    background-color: #72CE76 !important; 
		    color: black !important;
		    cursor:pointer; cursor: hand;
		}
		.central > div{
			width: 100%;
			margin: 20px 1%;
			float:left;
			min-width: 350px;
			padding-bottom: 10px;
		}
		.central{
			text-align: center;
			width: 100%;
			margin: 0 auto;
			padding-bottom: 30px;
		  	display: flex;
			-webkit-flex-flow: row wrap;
			justify-content: space-around;
		}
		.central h2{
			width: 80%;
			min-width: 300px;
			background-color: white;
			border-radius: 15px;
			padding: 10px;
			margin: 10px 1%;
		}
		.error{
			color:red;
    		font-size: 30px;
		}
		.inicio{
			margin: 8% auto 0 auto;
			width: 450px;
			min-width: 300px;
			position: relative;
		    background-color: white;
		    border-radius: 15px;
		}
		.inicio > div{
			padding: 20px;
		}
		.inicio h1{
			margin: 0;
		    border-radius: 15px 15px 0 0;
			padding: 14px 16px;
			text-align: center;
			color: white;
			background: #333;
		}
		.inicio .boton{
			width: 100%;
		}
	</style>
</head>
<body>
	<?php
		include "funciones.php";

		//dimensiones tablero
		$filas = 8;
		$columnas = 8;

		if($_SERVER['REQUEST_METHOD'] == 'GET'){
			if(isset($_GET['reset']) && $_GET['reset'] == "true"){ // ?reset=true
				resetear();
				recargarIndex();
			}
			if(isset($_GET['posicion'])){
				$coordenadas = validarFormatoCoordenades($_GET['posicion']);
				if($coordenadas != false){
					if(comprobarQueNoSeSalga($coordenadas, $columnas, $filas)){
						if(!disparar($coordenadas)) addMensaje("<h2 class='error'>Error: No se puede disparar el las coordenadas seleccionadas.</h2>");
					}else addMensaje("<h2 class='error'>Error: La coordenada introducida no pertenece al tablero.</h2>");
				}else addMensaje("<h2 class='error'>Error: El formato de las corrdenadas son incorrectas.</h2>");
				recargarIndex();
			}
		}

		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			if(isset($_POST['jugar']) && $_POST['jugar'] = "empezar"){
				crearTablero($filas, $columnas);
				if(!colocarBarcos()){
					resetear();
					addMensaje("<h2 class='error'>Error: El tablero no se ha generado porque los barcos no caben.</h2>");
				}
			}
			recargarIndex();
		}

		if(isset($_SESSION['mensaje'])){
			echo $_SESSION['mensaje'];
			unset($_SESSION['mensaje']);
		}

		if(!isset($_SESSION['tablero'])){
	?>
		<div class="inicio">
			<h1>Batalla naval</h1>
			<div>
				<form action="index.php" method="POST">
					<input type="submit" class="boton" name="jugar" value="empezar">
				</form>
			</div>
		</div>
	<?php
		}else{
			echo '<div class="central">';
			echo '<h2><a class="boton" href="index.php?reset=true">resetear</a> Disparos Fallidos: '.$_SESSION['intentos'].'</h2>';

			echo '<div>';
			mostrarTablero();
			echo "<table style='vertical-align: top; margin-left: 20px;'>";
			echo "<tr>";
				echo "<td><span class='agua'></span></td>";
				echo "<td>Agua</td>";
			echo "<tr>";
			echo "<tr>";
				echo "<td><span class='tocado'></span></td>";
				echo "<td>Tocado</td>";
			echo "<tr>";
			echo "<tr>";
				echo "<td><span class='hundido'></span></td>";
				echo "<td>Hundido</td>";
			echo "<tr>";
			echo "</table>";
			echo '</div>';
			echo '<div>';
			//mostrarTablero2();
			echo '</div>';
			echo '</div>';
		}
	?>
</body>
</html>
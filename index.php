<?php
	session_start();
	include "php/funciones.php";

	//Dimensiones tablero
	$filas = 8;
	$columnas = 8;
	$maxBarcos = 8;

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
		if(isset($_POST['jugar']) && $_POST['jugar'] == "empezar"){
			crearTablero($filas, $columnas);
			setBarcosAleatorios($maxBarcos);
			if(!colocarBarcos()){
				resetear();
				addMensaje("<h2 class='error'>Error: El tablero no se ha generado porque no hay barcos o no caben.</h2>");
			}
		}
		recargarIndex();
	}
?>
<html>
<head>
	<title>batalla Naval</title>
	<link rel="stylesheet" type="text/css" href="estilos/main.css" />
</head>
<body>
	<?php
		if(isset($_SESSION['mensaje'])){
			echo $_SESSION['mensaje'];
			unset($_SESSION['mensaje']);
		}

		if(!isset($_SESSION['tablero'])){ ?>
			<div class="inicio">
				<h1>Batalla naval</h1>
				<div>
					<form action="index.php" method="POST">
						<input type="submit" class="boton" name="jugar" value="empezar">
					</form>
				</div>
			</div>
		<?php }else{ ?>
			<div class="central">
			<h2><a class="boton" href="index.php?reset=true">resetear</a> Disparos Fallidos: <?php echo $_SESSION['intentos']; ?></h2>

			<div>
				<div class="columna redondeado">
					<?php mostrarTablero();?>
				</div>
			<div class="columna marginLeft">
				<div class="redondeado marginBottom">
					<h3 class="noMargin marginBottom">Leyenda</h3>
					<table class="padding10">
						<tr>
							<td><span class="agua"></span></td>
							<td>Agua</td>
						<tr>
						<tr>
							<td><span class="tocado"></span></td>
							<td>Tocado</td>
						<tr>
						<tr>
							<td><span class="hundido"></span></td>
							<td>Hundido</td>
						<tr>
					</table>
				</div>
				<div class="redondeado">
					<h3 class="noMargin marginBottom">Barcos</h3>
					<?php if(count(getCantBarcos())>0){?>
						<table class="textCenter padding10">
							<tr>
								<th>Largo</th>
								<th>Cantidad</th>
							</tr>
							<?php foreach (getCantBarcos() as $key => $value) {?>
								<tr>
									<td><?php echo $key;?></td>
									<td><?php echo $value;?></td>
								</tr>
							<?php }?>
						</table>
					<?php }else{ ?>
						<p>Todos destruidos.</p>
					<?php } ?>
				</div>
			</div>
			</div>
				<div>
					<!-- Descomentar para ver las posiciones de los barcos -->
					<?php //mostrarTablero2();?>
				</div>
			</div>
		<?php }?>
	
</body>
</html>
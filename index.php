<?php
require "composer.json";
require_once "src/control/vistas_control.php";
$vista = new vistasControlador();
$vista->obtenerPlantillaControlador();

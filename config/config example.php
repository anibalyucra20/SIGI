<?php
// Configuración base
const BASE_URL = 'http://localhost';
const API_BASE_URL = 'http://192.168.0.101';

// Configuración de la base de datos
const DB_HOST = 'localhost';
const DB_NAME = 'sigis';
const DB_USER = 'root';
const DB_PASS = 'root';


// Configuración de Integración Moodle
const MOODLE_URL   = 'https://campus.tuinstituto.edu.pe'; // Sin slash al final
const MOODLE_TOKEN = 'a1b2c3d4e5...'; // Token generado en Moodle > Admin > Plugins > Web Services
const MOODLE_ENABLED = true; // Interruptor general para apagar la sincro si Moodle se cae
// Llave de encriptación para SSO (32 caracteres)
const MOODLE_SSO_KEY = 'debe_de_ser_de_longitud_32_caracteres';

<?php
namespace HandlerCore\models;
/**
 * Clase que representa los parámetros y la conexión a la base de datos.
 */
    class Connection {

        /**
         * @var resource|null La conexión a la base de datos.
         */
        public $connection;

        /**
         * @var string|null El nombre de host de la base de datos.
         */
        public $host;

        /**
         * @var string|null El nombre de usuario para la conexión a la base de datos.
         */
        public $user;

        /**
         * @var string|null La contraseña para la conexión a la base de datos.
         */
        public $pass;

        /**
         * @var string|null El nombre de la base de datos.
         */
        public $db;

        /**
         * Constructor de la clase Connection.
         *
         * @param string|null $host      El nombre de host de la base de datos.
         * @param string|null $bd        El nombre de la base de datos.
         * @param string|null $usuario   El nombre de usuario para la conexión a la base de datos.
         * @param string|null $pass      La contraseña para la conexión a la base de datos.
         * @param resource|null $connection La conexión a la base de datos.
         */
		function __construct($host=null,$bd=null,$usuario=null,$pass=null, $connection = null){
			$this->host=$host;
			$this->user=$usuario;
			$this->db=$bd;
			$this->pass=$pass;
			$this->connection=$connection;
		}
    }


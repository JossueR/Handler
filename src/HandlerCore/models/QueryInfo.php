<?php
namespace HandlerCore\models;
/**
 * Clase que almacena información sobre la ejecución de un query.
 */
    class QueryInfo {


        /**
         * @var mixed|null El resultado de la ejecución del query.
         */
        public $result = null;

        /**
         * @var int|null El número total de filas afectadas por el query.
         */
        public $total = null;

        /**
         * @var int|null El nuevo ID generado después de una inserción.
         */
        public $new_id = null;

        /**
         * @var int|null Todas las filas obtenidas como resultado del query.
         */
        public ?int $allRows = null;

        /**
         * @var int|null El código de error (si lo hay) generado por el query.
         */
        public $errorNo = null;

        /**
         * @var string|null La descripción del error (si lo hay) generado por el query.
         */
        public $error = null;

        /**
         * @var array|null Las filas obtenidas como resultado del query en forma de arreglo.
         */
        public $inArray = null;

        /**
         * @var array|null Las filas obtenidas como resultado del query en forma de arreglo asociativo.
         */
        public $inAssoc = null;

        /**
         * @var string La sentencia SQL del query ejecutado.
         */
        public $sql;
    }

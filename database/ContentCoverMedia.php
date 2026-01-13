<?php

    use QCubed\Exception\Caller;
    use QCubed\Exception\InvalidCast;
    use QCubed\Query\QQ;

    require(QCUBED_PROJECT_MODEL_GEN_DIR . '/ContentCoverMediaGen.php');

    /**
     * The ContentCoverMedia class defined here contains any
     * customized code for the ContentCoverMedia class in the
     * Object Relational Model. It represents the "content_cover_media" table
     * in the database and extends from the code generated abstract ContentCoverMediaGen
     * class, which contains all the basic CRUD-type functionality as well as
     * basic methods to handle relationships and index-based loading.
     *
     * @package My QCubed Application
     * @subpackage Model
     *
     */
    class ContentCoverMedia extends ContentCoverMediaGen
    {
        /**
         * Default "to string" handler
         * Allows pages to _p()/echo()/print() this object, and to define the default
         * way this object would be outputted.
         *
         * @return string a nicely formatted string representation of this object
         */
        public function __toString(): string
        {
            return 'ContentCoverMedia Object ' . $this->primaryKey();
        }

        /**
         * Loads a ContentCoverMedia object by a given Content ID and optional clauses.
         * This method performs a query to fetch a single ContentCoverMedia object based on the provided ID.
         *
         * @param int $intId The ID of the content to load.
         * @param mixed $objOptionalClauses Additional optional clauses to apply to the query.
         *
         * @return ContentCoverMedia|null The loaded ContentCoverMedia object if found, or null if no match is found.
         * @throws Caller
         * @throws InvalidCast
         */
        public static function loadByIdFromPopupId(int $intId, mixed $objOptionalClauses = null): ?ContentCoverMedia
        {
            // Use QuerySingle to Perform the Query
            return ContentCoverMedia::querySingle(
                QQ::AndCondition(
                    QQ::Equal(QQN::ContentCoverMedia()->ContentId, $intId)
                ), $objOptionalClauses
            );
        }

        /**
         * Load a ContentCoverMedia object by a given group ID, with an optional set of query clauses.
         * This method performs a single query to fetch the corresponding object.
         *
         * @param int $intGroupId The ID of the group used to load the ContentCoverMedia object.
         * @param mixed $objOptionalClauses Optional query clauses to customize the query execution.
         *
         * @return ContentCoverMedia|null The loaded ContentCoverMedia object, or null if no match is found.
         * @throws Caller
         * @throws InvalidCast
         */
        public static function loadByIdFromGroupId(int $intGroupId, mixed $objOptionalClauses = null): ?ContentCoverMedia
        {
            // Use QuerySingle to Perform the Query
            return ContentCoverMedia::querySingle(
                QQ::AndCondition(
                    QQ::Equal(QQN::ContentCoverMedia()->MenuContentId, $intGroupId)
                ), $objOptionalClauses
            );
        }

        // NOTE: Remember that when introducing a new custom function,
        // you must specify types for the function parameters as well as for the function return type!

        // Override or Create New load/count methods
        // (For obvious reasons, these methods are commented out...
        // But feel free to use these as a starting point)
        /*

            public static function loadArrayBySample($strParam1, $intParam2, $objOptionalClauses = null) {
                // This will return an array of ContentCoverMedia objects
                return ContentCoverMedia::queryArray(
                    QQ::AndCondition(
                        QQ::Equal(QQN::ContentCoverMedia()->Param1, $strParam1),
                        QQ::GreaterThan(QQN::ContentCoverMedia()->Param2, $intParam2)
                    ),
                    $objOptionalClauses
                );
            }


            public static function loadBySample($strParam1, $intParam2, $objOptionalClauses = null) {
                // This will return a single ContentCoverMedia object
                return ContentCoverMedia::querySingle(
                    QQ::AndCondition(
                        QQ::Equal(QQN::ContentCoverMedia()->Param1, $strParam1),
                        QQ::GreaterThan(QQN::ContentCoverMedia()->Param2, $intParam2)
                    ),
                    $objOptionalClauses
                );
            }


            public static function countBySample($strParam1, $intParam2, $objOptionalClauses = null) {
                // This will return a count of ContentCoverMedia objects
                return ContentCoverMedia::queryCount(
                    QQ::AndCondition(
                        QQ::Equal(QQN::ContentCoverMedia()->Param1, $strParam1),
                        QQ::Equal(QQN::ContentCoverMedia()->Param2, $intParam2)
                    ),
                    $objOptionalClauses
                );
            }


            public static function loadArrayBySample($strParam1, $intParam2, $objOptionalClauses) {
                // Performing the load manually (instead of using QCubed Query)

                // Get the Database Object for this Class
                $objDatabase = ContentCoverMedia::getDatabase();

                // Properly Escape All Input Parameters using Database->SqlVariable()
                $strParam1 = $objDatabase->SqlVariable($strParam1);
                $intParam2 = $objDatabase->SqlVariable($intParam2);

                // Setup the SQL Query
                $strQuery = sprintf('
                    SELECT
                        `content_cover_media`.*
                    FROM
                        `content_cover_media` AS `content_cover_media`
                    WHERE
                        param_1 = %s AND
                        param_2 < %s',
                    $strParam1, $intParam2);

                // Perform the Query and Instantiate the Result
                $objDbResult = $objDatabase->Query($strQuery);
                return ContentCoverMedia::instantiateDbResult($objDbResult);
            }
        */

        // Override or Create New Properties and Variables
        // For performance reasons, these variables and __set and __get override methods
        // are commented out.  But if you wish to implement or override any
        // of the data-generated properties, please feel free to uncomment them.
        /*
            protected $strSomeNewProperty;

            protected function __set(string $strName, mixed $mixValue): void
            {
                switch ($strName) {
                    case 'SomeNewProperty': return $this->strSomeNewProperty;

                    default:
                        try {
                            return parent::__get($strName);
                        } catch (Caller $objExc) {
                            $objExc->incrementOffset();
                            throw $objExc;
                        }
                }
            }

            public function __set(string $strName, mixed $mixValue): void
            {
                switch ($strName) {
                    case 'SomeNewProperty':
                        try {
                            return ($this->strSomeNewProperty = \QCubed\Type::Cast($mixValue, \QCubed\Type::String));
                        } catch (QInvalidCastException $objExc) {
                            $objExc->incrementOffset();
                            throw $objExc;
                        }

                    default:
                        try {
                            return (parent::__set($strName, $mixValue));
                        } catch (Caller $objExc) {
                            $objExc->incrementOffset();
                            throw $objExc;
                        }
                }
            }
        */

    }

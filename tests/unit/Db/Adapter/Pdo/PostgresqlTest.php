<?php

namespace Phalcon\Test\Unit\Db\Adapter\Pdo;

use Phalcon\Db\Column;
use Phalcon\Db\Reference;
use Phalcon\Test\Module\UnitTest;
use Phalcon\Db\Adapter\Pdo\Postgresql;

/**
 * \Phalcon\Test\Unit\Db\Adapter\Pdo\PostgresqlTest
 * Tests the \Phalcon\Db\Adapter\Pdo\Postgresql component
 *
 * @copyright (c) 2011-2017 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Andres Gutierrez <andres@phalconphp.com>
 * @author    Serghei Iakovlev <serghei@phalconphp.com>
 * @author    Wojciech Ślawski <jurigag@gmail.com>
 * @package   Phalcon\Test\Unit\Db\Adapter\Pdo
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class PostgresqlTest extends UnitTest
{
    /**
     * @var Postgresql
     */
    protected $connection;

    public function _before()
    {
        parent::_before();

        $this->connection = new Postgresql([
            'host'     => TEST_DB_POSTGRESQL_HOST,
            'username' => TEST_DB_POSTGRESQL_USER,
            'password' => TEST_DB_POSTGRESQL_PASSWD,
            'dbname'   => TEST_DB_POSTGRESQL_NAME,
            'port'     => TEST_DB_POSTGRESQL_PORT,
            'schema'   => TEST_DB_POSTGRESQL_SCHEMA
        ]);
    }

    /**
     * Tests Postgresql::listTables
     *
     * @author Serghei Iakovlev <serghei@phalconphp.com>
     * @since  2016-09-29
     */
    public function testListTables()
    {
        $this->specify(
            'List all tables on a database does not return correct result',
            function () {
                $expected = [
                    'customers',
                    'images',
                    'parts',
                    'personas',
                    'personnes',
                    'prueba',
                    'robots',
                    'robots_parts',
                    'subscriptores',
                    'tipo_documento',
                ];

                expect($this->connection->listTables())->equals($expected);
                expect($this->connection->listTables(TEST_DB_POSTGRESQL_SCHEMA))->equals($expected);
            }
        );
    }

    /**
     * Tests Postgresql::listTables
     *
     * @author Serghei Iakovlev <serghei@phalconphp.com>
     * @since  2016-09-29
     */
    public function testTableExists()
    {
        $this->specify(
            'Failed check for existence of a schema.table',
            function ($table, $schema, $expected) {
                expect($this->connection->tableExists($table, $schema))->equals($expected);
            },
            [
                'examples' => [
                    ['personas', null, true ],
                    ['personas', TEST_DB_POSTGRESQL_SCHEMA, true],
                    ['noexist',  null, false],
                    ['noexist',  TEST_DB_POSTGRESQL_SCHEMA, false],
                    ['personas', 'test', false],
                ]
            ]
        );
    }

    /**
     * Tests Postgresql::describeReferences
     *
     * @author Wojciech Ślawski <jurigag@gmail.com>
     * @since  2016-09-28
     */
    public function testDescribeReferencesColumnsCount()
    {
        $this->specify(
            'The table references list contains wrong number of columns',
            function () {
                $referencesWithoutSchema = $this->connection->describeReferences(
                    'robots_parts'
                );

                $referencesWithSchema = $this->connection->describeReferences(
                    'robots_parts',
                    TEST_DB_POSTGRESQL_SCHEMA
                );

                expect($referencesWithoutSchema)->equals($referencesWithSchema);
                expect($referencesWithoutSchema)->count(2);

                /** @var Reference $reference */
                foreach ($referencesWithoutSchema as $reference) {
                    expect($reference->getColumns())->count(1);
                }
            }
        );
    }

    /**
     * Tests Postgresql::describeColumns for Postgresql autoincrement column
     *
     * @issue  https://github.com/phalcon/phalcon-devtools/issues/853
     * @author Serghei Iakovlev <serghei@phalconphp.com>
     * @since  2016-09-28
     */
    public function testDescribeAutoIncrementColumns()
    {
        $this->specify(
            'The table columns array contains incorrect initialized objects',
            function () {
                $columns = [
                    Column::__set_state([
                        '_columnName'    => 'id',
                        '_schemaName'    => null,
                        '_type'          => 14,
                        '_typeReference' => -1,
                        '_typeValues'    => null,
                        '_isNumeric'     => true,
                        '_size'          => 0,
                        '_scale'         => 0,
                        '_default'       => "nextval('images_id_seq'::regclass)",
                        '_unsigned'      => false,
                        '_notNull'       => true,
                        '_primary'       => false,
                        '_autoIncrement' => true,
                        '_first'         => true,
                        '_after'         => null,
                        '_bindType'      => 1,
                    ]),
                    Column::__set_state([
                        '_columnName'    => 'base64',
                        '_schemaName'    => null,
                        '_type'          => 6,
                        '_typeReference' => -1,
                        '_typeValues'    => null,
                        '_isNumeric'     => false,
                        '_size'          => null,
                        '_scale'         => 0,
                        '_default'       => null,
                        '_unsigned'      => false,
                        '_notNull'       => false,
                        '_primary'       => false,
                        '_autoIncrement' => false,
                        '_first'         => false,
                        '_after'         => 'id',
                        '_bindType'      => 2,
                    ]),
                ];

                expect($this->connection->describeColumns('images', null))->equals($columns);
                expect($this->connection->describeColumns('images', TEST_DB_POSTGRESQL_SCHEMA))->equals($columns);
            }
        );
    }
}

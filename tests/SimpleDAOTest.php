<?php

namespace HandlerCore\Tests;

use HandlerCore\models\dao\QueryParams;
use HandlerCore\models\QueryInfo;
use HandlerCore\models\SimpleDAO;
use PHPUnit\Framework\TestCase;

class SimpleDAOTest extends BaseTestCase
{
    /**
     * Test to ensure execQuery executes a valid SELECT query and returns a QueryInfo object with results.
     */
    public function testExecQuery_SelectQuery_ReturnsQueryInfoWithResult()
    {
        // Arrange

        $query = "SELECT * FROM test_table";

        // Act
        $queryInfo = SimpleDAO::execQuery($query);

        // Assert
        $this->assertInstanceOf(QueryInfo::class, $queryInfo);
        $this->assertNotNull($queryInfo->result);
        $this->assertEquals(0, $queryInfo->errorNo);
    }

    /**
     * Test to ensure execQuery executes a valid INSERT query and processes its result correctly.
     */
    public function testExecQuery_InsertQuery_SuccessfulExecution()
    {
        // Arrange

        $query = "INSERT INTO test_table (name, age) VALUES ('John Doe', 30)";

        // Act
        $queryInfo = SimpleDAO::execQuery($query, false);

        // Assert
        $this->assertInstanceOf(QueryInfo::class, $queryInfo);
        $this->assertEquals(1, $queryInfo->total); // 1 row affected
        $this->assertGreaterThan(0, $queryInfo->new_id); // valid new_id
        $this->assertEquals(0, $queryInfo->errorNo);
    }

    /**
     * Test execQuery with an invalid query to ensure proper error handling.
     */
    public function testExecQuery_InvalidQuery_ReturnsError()
    {
        // Arrange

        $query = "INVALID SQL SYNTAX";

        // Act
        $queryInfo = SimpleDAO::execQuery($query);

        // Assert
        $this->assertInstanceOf(QueryInfo::class, $queryInfo);
        $this->assertNotEquals(0, $queryInfo->errorNo); // Error code
        $this->assertNotEmpty($queryInfo->error); // Error message
    }

    /**
     * Test execQuery with autoconfiguration enabled.
     */
    public function testExecQuery_WithAutoConfig_SuccessfulExecution()
    {
        // Arrange

        $query = "SELECT * FROM test_table";
        $queryParams = new QueryParams();
        $queryParams->setEnablePaging(10, 1); // Enable pagination

        // Act
        $queryInfo = SimpleDAO::execQuery($query, true, true, null, $queryParams);

        // Assert
        $this->assertInstanceOf(QueryInfo::class, $queryInfo);
        $this->assertNotNull($queryInfo->result);
        $this->assertEquals(0, $queryInfo->errorNo);
    }

    /**
     * Test execQuery to ensure it respects the connection name.
     */
    public function testExecQuery_WithConnectionName_UsesCorrectConnection()
    {
        // Arrange
                $query = "SELECT * FROM test_table";

        // Act
        $queryInfo = SimpleDAO::execQuery($query, true, false, 'custom_conn');

        // Assert
        $this->assertInstanceOf(QueryInfo::class, $queryInfo);
        $this->assertNotNull($queryInfo->result);
        $this->assertEquals(0, $queryInfo->errorNo);
    }
}
<?php

use Mockery as m;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;

class FoundationInteractsWithDatabaseTest extends PHPUnit_Framework_TestCase
{
    use InteractsWithDatabase;

    protected $table = 'products';

    protected $data = ['title' => 'Spark'];

    protected $connection;

    public function setUp()
    {
        $this->connection = m::mock(Connection::class);
    }

    public function tearDown()
    {
        m::close();
    }

    public function testSeeInDatabaseFindsResults()
    {
        $this->mockCountBuilder(1);

        $this->seeInDatabase($this->table, $this->data);
    }

    /**
     * @expectedException \PHPUnit_Framework_ExpectationFailedException
     * @expectedExceptionMessage The table is empty.
     */
    public function testSeeInDatabaseDoesNotFindResults()
    {
        $builder = $this->mockCountBuilder(0);

        $builder->shouldReceive('get')->andReturn(collect());

        $this->seeInDatabase($this->table, $this->data);
    }

    /**
     * @expectedException \PHPUnit_Framework_ExpectationFailedException
     */
    public function testSeeInDatabaseFindsNotMatchingResults()
    {
        $this->expectExceptionMessage('Found: '.json_encode([['title' => 'Forge']], JSON_PRETTY_PRINT));

        $builder = $this->mockCountBuilder(0);

        $builder->shouldReceive('take')->andReturnSelf();
        $builder->shouldReceive('get')->andReturn(collect([['title' => 'Forge']]));

        $this->seeInDatabase($this->table, $this->data);
    }

    /**
     * @expectedException \PHPUnit_Framework_ExpectationFailedException
     */
    public function testSeeInDatabaseFindsManyNotMatchingResults()
    {
        $this->expectExceptionMessage('Found: '.json_encode(['data', 'data', 'data'], JSON_PRETTY_PRINT).' and 2 others.');

        $builder = $this->mockCountBuilder(0);

        $builder->shouldReceive('take')->andReturnSelf();
        $builder->shouldReceive('get')->andReturn(
            collect(array_fill(0, 5, 'data'))
        );

        $this->seeInDatabase($this->table, $this->data);
    }

    public function testDontSeeInDatabaseDoesNotFindResults()
    {
        $this->mockCountBuilder(0);

        $this->dontSeeInDatabase($this->table, $this->data);
    }

    /**
     * @expectedException \PHPUnit_Framework_ExpectationFailedException
     * @expectedExceptionMessage a row in the table [products] does not match the attributes {"title":"Spark"}
     */
    public function testDontSeeInDatabaseFindsResults()
    {
        $builder = $this->mockCountBuilder(1);

        $builder->shouldReceive('take')->andReturnSelf();
        $builder->shouldReceive('get')->andReturn(collect([$this->data]));

        $this->dontSeeInDatabase($this->table, $this->data);
    }

    protected function mockCountBuilder($countResult)
    {
        $builder = m::mock(Builder::class);

        $builder->shouldReceive('where')->with($this->data)->andReturnSelf();

        $builder->shouldReceive('count')->andReturn($countResult);

        $this->connection->shouldReceive('table')
            ->with($this->table)
            ->andReturn($builder);

        return $builder;
    }

    protected function getConnection()
    {
        return $this->connection;
    }
}

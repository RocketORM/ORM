<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Model;

use Fixture\Car\Model\Car;
use Fixture\Car\Model\Company;
use Rocket\ORM\Model\Model;
use Rocket\ORM\Rocket;
use Rocket\ORM\Test\Generator\Model\ModelTestHelper;
use Rocket\ORM\Test\Generator\Schema\SchemaTestHelper;
use Rocket\ORM\Test\RocketTestCase;
use Rocket\ORM\Test\Utils\String;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 *
 * @covers \Rocket\ORM\Model\Model
 */
class ModelTest extends RocketTestCase
{
    use ModelTestHelper, SchemaTestHelper;

    /**
     * @test
     */
    public function methodValidation()
    {
        $car = new Car();
        $date = \DateTime::createFromFormat('Y-m-d', '2014-01-01');

        $this->assertTrue($car->setDoorCount(5) instanceof Car);
        $this->assertTrue($car->setPrice(19990.50) instanceof Car);
        $this->assertTrue($car->setWheelName('Wheel name') instanceof Car);
        $this->assertTrue($car->setReleasedAt($date) instanceof Car);

        $this->assertEquals(5, $car->getDoorCount());
        $this->assertEquals(19990.50, $car->getPrice());
        $this->assertEquals('Wheel name', $car->getWheelName());
        $this->assertTrue($car->getReleasedAt() instanceof \DateTime);
        $this->assertEquals($date->getTimestamp(), $car->getReleasedAt()->getTimestamp());

        // TODO relations validation
    }

    /**
     *
     */
    public function defaultValues()
    {
        // TODO test default value, like _isNew, etc
    }

    /**
     * @test
     *
     * @return Company
     */
    public function insert()
    {
        $company = new Company();

        $company->setName('Foo');

        $this->assertProtectedAttributeEquals(true, '_isModified', $company);

        $result = $company->save();

        $this->assertTrue($result, 'Cannot insert the object');
        $this->assertNotNull($company->getId(), 'The autoincrement id should not be null');
        $this->assertProtectedAttributeEquals(false, '_isModified', $company);
        $this->assertProtectedAttributeEquals(false, '_isNew', $company);
        $this->assertProtectedAttributeEquals(false, '_isDeleted', $company);
        $this->assertCompanyIntegrity($company);

        return $company;
    }

    /**
     * @test
     *
     * @depends insert
     *
     * @param Company $company
     */
    public function update(Company $company)
    {
        $id = $company->getId();
        $result = $company
            ->setName('Bar')
        ->save();

        $this->assertTrue($result, 'Cannot update the object');
        $this->assertEquals($id, $company->getId(), 'The primary key should not have been updated');
        $this->assertProtectedAttributeEquals(false, '_isModified', $company);
        $this->assertProtectedAttributeEquals(false, '_isNew', $company);
        $this->assertProtectedAttributeEquals(false, '_isDeleted', $company);

        $this->assertCompanyIntegrity($company);
    }

    /**
     * @test
     *
     * @depends insert
     *
     * @param Company $company
     *
     * @return Company
     */
    public function delete(Company $company)
    {
        $result = $company->delete();

        $this->assertTrue($result);
        $this->assertCompanyIntegrity($company, true);
        $this->assertProtectedAttributeEquals(false, '_isModified', $company);
        $this->assertProtectedAttributeEquals(false, '_isNew', $company);
        $this->assertProtectedAttributeEquals(true, '_isDeleted', $company);

        return $company;
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Cannot save a deleted object
     *
     * @depends delete
     *
     * @param Company $company
     */
    public function saveDeletedObjectException(Company $company)
    {
        $company->save();
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Cannot delete an already deleted object
     *
     * @depends delete
     *
     * @param Company $company
     */
    public function deleteDeletedObjectException(Company $company)
    {
        $company->delete();
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Cannot delete a new object
     */
    public function deleteNewObjectException()
    {
        (new Company())->delete();
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Houston, we have a problem
     */
    public function saveException()
    {
        $this->createMockedThrowMethodCompany('doInsert')
            ->setName(String::generateRandomString(10))
        ->save();
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Houston, we have a problem
     */
    public function saveWithTransactionException()
    {
        $con = Rocket::getConnection(
            Rocket::getTableMap(get_class(new Company()))->getConnectionName()
        );

        $con->beginTransaction();

        try {
            $this->createMockedThrowMethodCompany('doInsert')
                ->setName(String::generateRandomString(10))
            ->save($con);
        } catch (\LogicException $e) {
            $this->assertFalse($con->inTransaction());

            throw $e;
        }
    }

    /**
     * @test
     *
     * @expectedException \LogicException
     * @expectedExceptionMessage Houston, we have a problem
     *
     * @depends insert
     * @depends delete
     */
    public function deleteWithTransactionException()
    {
        $company = $this->createMockedThrowMethodCompany('doDelete');

        // First save the object
        $company
            ->setName(String::generateRandomString(10))
        ->save();

        $con = Rocket::getConnection(
            Rocket::getTableMap(get_class(new Company()))->getConnectionName()
        );

        $con->beginTransaction();

        try {
            $company->delete($con);
        } catch (\LogicException $e) {
            $this->assertFalse($con->inTransaction());

            throw $e;
        }
    }

    /**
     * @test
     */
    public function preSave()
    {
        $company = new Company();
        $tableMap = Rocket::getTableMap(get_class($company));

        $this->assertTrue(
            $this->getProtectedMethod($company, 'preSave')->invoke($company, Rocket::getConnection($tableMap->getConnectionName()))
        );
    }

    /**
     * @test
     */
    public function postSave()
    {
        $company = new Company();
        $tableMap = Rocket::getTableMap(get_class($company));

        $this->assertTrue(
            $this->getProtectedMethod($company, 'postSave')->invoke($company, Rocket::getConnection($tableMap->getConnectionName()))
        );
    }

    /**
     * @test
     */
    public function preDelete()
    {
        $company = new Company();
        $tableMap = Rocket::getTableMap(get_class($company));

        $this->assertTrue(
            $this->getProtectedMethod($company, 'preDelete')->invoke($company, Rocket::getConnection($tableMap->getConnectionName()))
        );
    }

    /**
     * @test
     */
    public function postDelete()
    {
        $company = new Company();
        $tableMap = Rocket::getTableMap(get_class($company));

        $this->assertTrue(
            $this->getProtectedMethod($company, 'postDelete')->invoke($company, Rocket::getConnection($tableMap->getConnectionName()))
        );
    }

    /**
     * @param string $methodName
     *
     * @return Company|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createMockedThrowMethodCompany($methodName)
    {
        $company = $this->getMockBuilder('\Fixture\Car\Model\Company')
            ->setMethods([$methodName, 'getTableMap'])
            ->getMock()
        ;

        /** @var Company|\PHPUnit_Framework_MockObject_MockObject $company */
        $company
            ->expects($this->once())
            ->method($methodName)
            ->willThrowException(new \LogicException('Houston, we have a problem'))
        ;

        $company
            ->expects($this->any())
            ->method('getTableMap')
            ->willReturn(Rocket::getTableMap(get_class(new Company())))
        ;

        return $company;
    }

    /**
     * @param Model  $object
     * @param string $methodName
     *
     * @return \ReflectionMethod
     */
    protected function getProtectedMethod(Model $object, $methodName)
    {
        $reflection = new \ReflectionObject($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @param mixed   $expectedValue
     * @param string  $attributeName
     * @param Company $company
     */
    protected function assertProtectedAttributeEquals($expectedValue, $attributeName, Company $company)
    {
        $reflection = new \ReflectionObject($company);
        $attribute = $reflection->getProperty($attributeName);
        $attribute->setAccessible(true);
        $this->assertEquals($expectedValue, $attribute->getValue($company));
    }

    /**
     * @param Company $company
     * @param bool    $isDelete
     */
    protected function assertCompanyIntegrity(Company $company, $isDelete = false)
    {
        $tableMap = Rocket::getTableMap(get_class($company));
        $con = Rocket::getConnection($tableMap->getConnectionName());

        $stmt = $con->prepare('SELECT id, name FROM ' . $tableMap->getTableName() . ' WHERE id = :id LIMIT 1');
        $stmt->bindValue(':id', $company->getId(), \PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($isDelete) {
            $this->assertFalse($data);
        } else {
            $this->assertCount(2, $data);
            $this->assertEquals($company->getId(), $data['id']);
            $this->assertEquals($company->getName(), $data['name']);
        }
    }
}

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
use Fixture\Car\Model\Wheel;
use Rocket\ORM\Record\Record;
use Rocket\ORM\Rocket;
use Rocket\ORM\Test\Generator\Model\ModelTestHelper;
use Rocket\ORM\Test\Generator\Schema\SchemaTestHelper;
use Rocket\ORM\Test\RocketTestCase;
use Rocket\ORM\Test\Utils\StringUtil;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 *
 * @covers \Rocket\ORM\Record\Record
 */
class ModelTest extends RocketTestCase
{
    use ModelTestHelper, SchemaTestHelper;

    /**
     * @var array
     */
    protected static $companyScheduledDeletion = [];


    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass()
    {
        if (isset(self::$companyScheduledDeletion[0])) {
            $tableMap = Rocket::getTableMap(get_class(new Company()));
            $con = Rocket::getConnection($tableMap->getConnectionName());

            $con->exec(
                'DELETE FROM `' . $tableMap->getTableName()
                . '` WHERE id IN ('
                    . join(', ', self::$companyScheduledDeletion)
                . ')'
            );
        }

        parent::tearDownAfterClass();
    }

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

        $this->assertEquals(true, $this->getObjectAttribute($company, '_isModified'));

        $result = $company->save();

        $this->assertTrue($result, 'Cannot insert the object');
        $this->assertNotNull($company->getId(), 'The autoincrement id should not be null');

        self::$companyScheduledDeletion[] = $company->getId();

        $this->assertEquals(false, $this->getObjectAttribute($company, '_isModified'));
        $this->assertEquals(false, $this->getObjectAttribute($company, '_isNew'));
        $this->assertEquals(false, $this->getObjectAttribute($company, '_isDeleted'));
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
        $this->assertEquals(false, $this->getObjectAttribute($company, '_isModified'));
        $this->assertEquals(false, $this->getObjectAttribute($company, '_isNew'));
        $this->assertEquals(false, $this->getObjectAttribute($company, '_isDeleted'));

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
        $this->assertEquals(false, $this->getObjectAttribute($company, '_isModified'));
        $this->assertEquals(false, $this->getObjectAttribute($company, '_isNew'));
        $this->assertEquals(true, $this->getObjectAttribute($company, '_isDeleted'));

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
            ->setName(StringUtil::generateRandomString(10))
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
                ->setName(StringUtil::generateRandomString(10))
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
            ->setName(StringUtil::generateRandomString(10))
        ->save();
        self::$companyScheduledDeletion[] = $company->getId();

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
     * @test
     *
     * @depends insert
     * @depends update
     */
    public function setRelationOneToMany()
    {
        $company = new Company();
        $company
            ->setName(StringUtil::generateRandomString(10))
            ->save()
        ;

        self::$companyScheduledDeletion[] = $company->getId();

        $wheel = new Wheel();

        $wheel
            ->setUniqueName(StringUtil::generateRandomString(10))
            ->setScore(95)
            ->setCompany($company)
        ;

        $this->assertEquals($company->getId(), $wheel->getCompanyId());

        $wheel->save();

        // Wheel integrity
        $this->assertEquals(false, $this->getObjectAttribute($wheel, '_isNew'));
        $this->assertEquals(false, $this->getObjectAttribute($wheel, '_isModified'));
        $this->assertEquals(false, $this->getObjectAttribute($wheel, '_isDeleted'));

        // Company integrity
        $this->assertEquals(false, $this->getObjectAttribute($company, '_isNew'));
        $this->assertEquals(false, $this->getObjectAttribute($company, '_isModified'));
        $this->assertEquals(false, $this->getObjectAttribute($company, '_isDeleted'));
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
     * @param Record $object
     * @param string $methodName
     *
     * @return \ReflectionMethod
     */
    protected function getProtectedMethod(Record $object, $methodName)
    {
        $reflection = new \ReflectionObject($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
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

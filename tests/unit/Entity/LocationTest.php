<?php

namespace App\Tests\Unit\Entity;

use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Survos\LocationBundle\Entity\Location;


class LocationTest extends TestCase
{
    // build
    public function testBuildAssignsTheParametersToTheCorrectFields()
    {

        // Arrange
        $testCode = 'NC';
        $testName = 'North Carolina';
        $testLevel = 2;

        // Act
        $result = Location::build($testCode, $testName, $testLevel);

        // Assert
        $this->assertEquals($testCode, $result->getCode());
    }

}

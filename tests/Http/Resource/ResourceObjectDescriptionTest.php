<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge,
 * publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE
 * USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

declare(strict_types=1);

namespace Tests\Conjoon\Http\Resource;

use Conjoon\Http\Resource\ResourceObjectDescription;
use Conjoon\Http\Resource\ResourceObjectDescriptionList;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionClass;
use Tests\TestCase;

/**
 * Class ResourceObjectDescriptionTest
 * @package Tests\Conjoon\Resource
 */
class ResourceObjectDescriptionTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $resource = $this->getResourceObjectDescriptionMock();

        $this->assertInstanceOf(ResourceObjectDescription::class, $resource);
    }


    /**
     * tests getAllRelationshipTypes()
     * @return void
     * @throws ReflectionException
     */
    public function testGetAllRelationshipTypes()
    {
        $translator = $this->getResourceObjectDescriptionMock(["getAllRelationshipResourceDescriptions"]);
        $reflection = new ReflectionClass($translator);

        $resourceTarget = $this->getResourceObjectDescriptionMock(["getRelationships"]);
        $resourceTarget->expects($this->exactly(1))->method("getType")->willReturn("entity");
        $resourceTarget_1 = $this->getResourceObjectDescriptionMock(["getRelationships"]);
        $resourceTarget_1->expects($this->exactly(2))->method("getType")->willReturn("entity_1");
        $resourceTarget_2 = $this->getResourceObjectDescriptionMock(["getRelationships"]);
        $resourceTarget_2->expects($this->exactly(2))->method("getType")->willReturn("entity_2");

        $relationships1 = new ResourceObjectDescriptionList();
        $relationships1[] = $resourceTarget_1;
        $relationships1[] = $resourceTarget_2;

        $relationships2 = new ResourceObjectDescriptionList();
        $relationships2[] = $resourceTarget;
        $relationships2[] = $resourceTarget_1;
        $relationships2[] = $resourceTarget_2;

        $translator
            ->expects($this->exactly(2))
            ->method("getAllRelationshipResourceDescriptions")
            ->withConsecutive([false], [true])
            ->willReturnOnConsecutiveCalls(
                $relationships1,
                $relationships2,
            );


        $getAllRelationshipTypes = $reflection->getMethod("getAllRelationshipTypes");
        $getAllRelationshipTypes->setAccessible(true);

        $this->assertEquals([
            "entity_1", "entity_2"
        ], $getAllRelationshipTypes->invokeArgs($translator, []));

        $this->assertEquals([
            "entity", "entity_1", "entity_2"
        ], $getAllRelationshipTypes->invokeArgs($translator, [true]));
    }


    /**
     * tests getAllRelationshipPaths() with dotnotation
     * @return void
     * @throws ReflectionException
     */
    public function testGetAllRelationshipPaths()
    {
        $relationships = new ResourceObjectDescriptionList();

        $resourceTarget = $this->getResourceObjectDescriptionMock([
            "getRelationships",
            "getAllRelationshipResourceDescriptions"
        ]);
        $resourceTarget->expects($this->any())->method("getType")->willReturn("entity");
        $resourceTarget->expects($this->any())->method("getRelationships")->willReturn($relationships);
        $reflection = new ReflectionClass($resourceTarget);


        $relationships_1 = new ResourceObjectDescriptionList();
        $relationships_1_1 = new ResourceObjectDescriptionList();
        $relationships_1_2 = new ResourceObjectDescriptionList();


        $resourceTarget_1 = $this->getResourceObjectDescriptionMock(["getRelationships"]);
        $resourceTarget_1->expects($this->any())->method("getType")->willReturn("entity_1");
        $resourceTarget_1->expects($this->any())->method("getRelationships")->willReturn($relationships_1);

        $resourceTarget_1_1 = $this->getResourceObjectDescriptionMock(["getRelationships"]);
        $resourceTarget_1_1->expects($this->any())->method("getType")->willReturn("entity_1_1");
        $resourceTarget_1_1->expects($this->any())->method("getRelationships")->willReturn($relationships_1_1);

        $resourceTarget_1_2 = $this->getResourceObjectDescriptionMock(["getRelationships"]);
        $resourceTarget_1_2->expects($this->any())->method("getType")->willReturn("entity_1_2");
        $resourceTarget_1_2->expects($this->any())->method("getRelationships")->willReturn($relationships_1_2);

        $resourceTarget_1_1_1 = $this->getResourceObjectDescriptionMock(["getRelationships"]);
        $resourceTarget_1_1_1->expects($this->any())->method("getType")->willReturn("entity_1_1_1");

        $resourceTarget_1_2_1 = $this->getResourceObjectDescriptionMock(["getRelationships"]);
        $resourceTarget_1_2_1->expects($this->any())->method("getType")->willReturn("entity_1_2_1");

        $relationships[] = $resourceTarget_1;
        $relationships_1[] = $resourceTarget_1_1;
        $relationships_1[] = $resourceTarget_1_2;
        $relationships_1_1[] = $resourceTarget_1_1_1;
        $relationships_1_2[] = $resourceTarget_1_2_1;


        /**
         * [
         *  "entity",
         *  "entity_1",
         *  "entity_1.entity_1_1",
         *  "entity_1.entity_1_1.entity_1_1_1",
         *  "entity_1.entity_1_2",
         *  "entity_1.entity_1_2.entity_1_2_1"
         * ]
         */

        $getAllRelationshipPaths = $reflection->getMethod("getAllRelationshipPaths");
        $getAllRelationshipPaths->setAccessible(true);

        $this->assertEquals([
            "entity",
            "entity.entity_1",
            "entity.entity_1.entity_1_1",
            "entity.entity_1.entity_1_1.entity_1_1_1",
            "entity.entity_1.entity_1_2",
            "entity.entity_1.entity_1_2.entity_1_2_1"
        ], $getAllRelationshipPaths->invokeArgs($resourceTarget, [true]));

        $this->assertEquals([
            "entity_1",
            "entity_1.entity_1_1",
            "entity_1.entity_1_1.entity_1_1_1",
            "entity_1.entity_1_2",
            "entity_1.entity_1_2.entity_1_2_1"
        ], $getAllRelationshipPaths->invokeArgs($resourceTarget, [false]));
    }



    /**
     * Tests getAllRelationshipResourceDescriptions
     * @return void
     * @throws ReflectionException
     */
    public function testGetAllRelationshipResourceDescriptions(): void
    {
        $resourceTarget = $this->getResourceObjectDescriptionMock(["getRelationships"]);
        $reflection = new ReflectionClass($resourceTarget);

        $resourceTarget_1_1 = $this->getResourceObjectDescriptionMock(["getRelationships"]);
        $resourceTarget_1_2 = $this->getResourceObjectDescriptionMock(["getRelationships"]);
        $resourceTarget_2_1 = $this->getResourceObjectDescriptionMock(["getRelationships"]);

        $relationships = new ResourceObjectDescriptionList();
        $relationships[] = $resourceTarget_1_1;
        $relationships[] = $resourceTarget_1_2;

        $relationships_1_1 = new ResourceObjectDescriptionList();
        $relationships_1_1[] = $resourceTarget_2_1;

        $relationships_1_2 = new ResourceObjectDescriptionList();
        $relationships_2_1 = new ResourceObjectDescriptionList();

        $callTimes = 2;


        $resourceTarget->expects($this->exactly($callTimes))->method("getRelationships")->willReturn(
            $relationships
        );

        $resourceTarget_1_1->expects($this->exactly($callTimes))->method("getRelationships")->willReturn(
            $relationships_1_1
        );
        $resourceTarget_1_2->expects($this->exactly($callTimes))->method("getRelationships")->willReturn(
            $relationships_1_2
        );
        $resourceTarget_2_1->expects($this->exactly($callTimes))->method("getRelationships")->willReturn(
            $relationships_2_1
        );

        $getRelatedResourceTargetsReflection = $reflection->getMethod("getAllRelationshipResourceDescriptions");
        $getRelatedResourceTargetsReflection->setAccessible(true);

        $list = $getRelatedResourceTargetsReflection->invokeArgs($resourceTarget, []);
        foreach (
            [
                $resourceTarget_1_1, $resourceTarget_1_2, $resourceTarget_2_1
            ] as $resourceObject
        ) {
            $this->assertContains($resourceObject, $list);
        }

        $list = $getRelatedResourceTargetsReflection->invokeArgs($resourceTarget, [true]);
        foreach (
            [
                $resourceTarget, $resourceTarget_1_1, $resourceTarget_1_2, $resourceTarget_2_1
            ] as $resourceObject
        ) {
            $this->assertContains($resourceObject, $list);
        }
    }


    /**
     * @param array $methods
     * @return MockObject
     */
    protected function getResourceObjectDescriptionMock(array $methods = []): MockObject
    {
        return $this->getMockForAbstractClass(
            ResourceObjectDescription::class,
            [],
            '',
            true,
            true,
            true,
            $methods
        );
    }
}

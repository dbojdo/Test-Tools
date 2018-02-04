<?php

namespace Webit\Tests\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Webit\Tests\Bootstrap\AppConfigurableKernel;
use Webit\Tests\Bootstrap\Entity\User;
use Webit\Tests\Kernel\ConfigurableKernel;

class SchemaEnsureTraitIntegrationTest extends TestCase
{
    use SchemaEnsureTrait;

    /**
     * @param string|null $hash
     * @return ConfigurableKernel
     */
    protected function createKernel($hash = null)
    {
        return new AppConfigurableKernel($hash);
    }

    /**
     * @test
     */
    public function it_prepares_schema()
    {
        $this->prepareSchema('default');

        $this->assertSchemaPrepared();
    }

    /**
     * @test
     */
    public function it_drops_database()
    {
        $this->prepareSchema('default', true);

        $this->assertSchemaPrepared();
    }

    /**
     * @test
     */
    public function it_disable_foreign_keys_checks_for_mysql()
    {
        $this->prepareSchema('default');

        $this->addUser();

        $this->prepareSchema('default');

        $this->assertSchemaPrepared();
    }

    private function assertSchemaPrepared()
    {
        /** @var EntityManagerInterface $em */
        $em = $this->service('doctrine')->getManager('default');
        $schemaManager = $em->getConnection()->getSchemaManager();

        $tables = $schemaManager->listTables();
        $this->assertEquals('user_addresses', $tables[0]->getName());
        $this->assertEquals('users', $tables[1]->getName());
    }

    private function addUser()
    {
        $em = $this->entityManager('default');

        $user = new User('test');
        $em->persist($user);

        $address = $user->addAddress('street', '24334', 'City');
        $em->persist($address);

        $address = $user->addAddress('street2', '24334', 'City');
        $em->persist($address);

        $em->flush();
        $em->clear();
    }
}

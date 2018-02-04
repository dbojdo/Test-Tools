<?php

namespace Webit\Tests\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Webit\Tests\Kernel\KernelAwareTrait;

trait SchemaEnsureTrait
{
    /** @var string */
    private static $kernelHash;

    use KernelAwareTrait;

    /**
     * @param string $entityManagerName
     * @param bool $dropDatabase
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function prepareSchema($entityManagerName, $dropDatabase = false)
    {
        $em = $this->entityManager($entityManagerName);

        $connection = $em->getConnection();

        $this->clearSchema($connection, $dropDatabase);

        $metadatas = $em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($em);
        $schemaTool->createSchema($metadatas);
    }

    /**
     * @param Connection $connection
     * @param bool $dropDatabase
     * @throws \Doctrine\DBAL\DBALException
     */
    private function clearSchema(Connection $connection, $dropDatabase = false)
    {
        if ($dropDatabase) {
            $baseConnection = $this->bareConnection($connection);
            $schemaManager = $baseConnection->getSchemaManager();
            $schemaManager->dropDatabase($connection->getDatabase());
            $schemaManager->createDatabase($connection->getDatabase());

            return;
        }

        $schemaManager = $connection->getSchemaManager();

        $isMysql = $connection->getDatabasePlatform() instanceof MySqlPlatform;
        if ($isMysql) {
            $connection->exec('SET FOREIGN_KEY_CHECKS=0');
        }

        foreach ($schemaManager->listTables() as $table) {
            $schemaManager->dropTable($table->getName());
        }

        if ($isMysql) {
            $connection->exec('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /**
     * @param Connection $connection
     * @return Connection
     * @throws \Doctrine\DBAL\DBALException
     */
    private function bareConnection(Connection $connection)
    {
        $params = $connection->getParams();
        unset($params['dbname']);

        return DriverManager::getConnection($params);
    }

    /**
     * @param string $name
     * @return EntityManagerInterface
     */
    private function entityManager($name)
    {
        $kernel = $this->createKernel(self::$kernelHash);
        $kernel->boot();

        self::$kernelHash = $kernel->hash();

        return $kernel->getContainer()->get('doctrine')->getManager($name);
    }
}

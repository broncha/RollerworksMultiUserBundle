<?php

/*
 * This file is part of the RollerworksMultiUserBundle package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rollerworks\Bundle\MultiUserBundle\Tests\Functional\Command;

use Rollerworks\Bundle\MultiUserBundle\Command\ActivateUserCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @group functional
 */
class ActivateUserCommandTest extends CommandTestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testDeactivateUser()
    {
        $command = new ActivateUserCommand();
        $this->application->add($command);

        $command = $this->application->find('fos:user:activate');
        $commandTester = new CommandTester($command);

        $container = $this->application->getKernel()->getContainer();

        $acmeAdminUserManager = $container->get('acme_admin.user_manager');
        $acmeUserUserManager = $container->get('acme_user.user_manager');

        $user = $acmeAdminUserManager->createUser();
        $user->setEmail('test@example.com');
        $user->setUsername('testUser');
        $user->setPlainPassword('very-not-secure');
        $user->setEnabled(false);

        $acmeAdminUserManager->updateUser($user);

        $this->assertNotNull($acmeAdminUserManager->findUserByUsername('testUser'));
        $this->assertNull($acmeUserUserManager->findUserByUsername('testUser'));

        $commandTester->execute(array(
            'command' => $command->getName(),
            'username' => 'testUser',
            'user-system' => 'acme_admin'
        ));

        $this->assertNotNull($acmeAdminUserManager->findUserByUsername('testUser'));
        $this->assertNull($acmeUserUserManager->findUserByUsername('testUser'));
        $this->assertTrue($acmeAdminUserManager->findUserByUsername('testUser')->isEnabled());
    }
}

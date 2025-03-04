<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistrationControllerTest extends WebTestCase
{
    private ?KernelBrowser $client = null;

    protected function setUp(): void
    {
        $this->client = $this->createClient();
    }

    public function testRegisterUser(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test@example.com',
                'password' => 'securepassword',
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('user', $responseContent);
        $this->assertArrayHasKey('email', $responseContent['user']);
        $this->assertEquals('test@example.com', $responseContent['user']['email']);
    }

    public function testRegisterWithExistingEmail(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $user = new User();
        $user->setEmail('duplicate@example.com');
        $user->setPassword('hashedpassword');
        $user->setRoles(['ROLE_USER']);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->client->request(
            Request::METHOD_POST,
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'duplicate@example.com',
                'password' => 'securepassword',
            ])
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $responseContent);
        $this->assertEquals('User with this email already exists', $responseContent['error']);
    }

    protected function tearDown(): void
    {
        // Очистка таблицы app_user
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->getConnection()->executeQuery('TRUNCATE TABLE app_user CASCADE');

        parent::tearDown();
    }
}
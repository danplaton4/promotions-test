<?php

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthenticationTest extends WebTestCase {
  protected KernelBrowser $client;
  protected EntityManager $entityManager;
  protected array $userCredentials = ['email' => '', 'password' => ''];

  public function testAuthenticate(): int {
    $this->setCredentials();

    $this->client = static::createClient();

    $doctrine = self::$kernel->getContainer()->get('doctrine');
    $this->entityManager = $doctrine->getManager();

    // Request lookup service
    $this->client->request('POST', 'http://localhost:8001/api/auth', $this->userCredentials);
    $this->assertResponseIsSuccessful('Authentication Failed!!!');

    $authenticateResponse = json_decode($this->client->getResponse()->getContent(), true);
    echo "\nAuthentication > Success";

    return $authenticateResponse['id'];
  }

  private function setCredentials(): void {
    $this->userCredentials['email'] = 'john@doe.com';
    $this->userCredentials['password'] = 'blablabla';
  }
}

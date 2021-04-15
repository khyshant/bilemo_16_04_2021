<?php
/**
 * Created by PhpStorm.
 * User: khysh
 * Date: 22/03/2020
 * Time: 21:36
 */

namespace App\Tests;


use App\Entity\Group;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FunctionalTest extends WebTestCase
{
    public function testStatusUsersWithoutToken()
    {
        $client = static::createClient();
        $crawler = $client->request(Request::METHOD_GET, "/api/users");
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param string $username
     * @param string $password
     * @return \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    protected function createAuthenticatedClient($username = 'customer_1', $password = 'azerty123')
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/login_check',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode(array(
                'customername' => $username,
                'password' => $password,
            ))
        );

        $data = json_decode($client->getResponse()->getContent(), true);

        $client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $data['token']));

        return $client;
    }


    public function testGetUsers()
    {
        $client = $this->createAuthenticatedClient('customer_1', 'azerty123');
        $data = json_decode($client->getResponse()->getContent(), true);
        $bearer = sprintf('Bearer %s', $data['token']);
        $client->request('GET','/api/users',[],[],['CONTENT_TYPE' => 'application/json']);
        $response = $client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $user = $content[0];
        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('lastname', $user);
        $this->assertArrayHasKey('firstname', $user);
    }

    public function testPostUser()
    {
        $lastname = 'nom_' . time();
        $firstname = 'prenom_' . time();
        $data = '{"lastname":"'.$lastname.'", "firstname": "'.$firstname.'"}';
        $client = $this->createAuthenticatedClient('customer_1', 'azerty123');
        $client->request('POST', '/api/users', [], [], ['CONTENT_TYPE' => 'application/json'], $data);
        $response = $client->getResponse();
        $this->assertEquals(201, $client->getResponse()->getStatusCode());
    }

    public function testPutUser()
    {
        $lastname = 'nom_' . time();
        $firstname = 'prenom_' . time();
        $data = '{"lastname":"'.$lastname.'", "firstname": "'.$firstname.'"}';
        $client = $this->createAuthenticatedClient('customer_1', 'azerty123');
        $client->request('PUT', '/api/users/62', [], [], ['CONTENT_TYPE' => 'application/json'], $data);
        $response = $client->getResponse();
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }

    public function testDeleteUser()
    {
        $lastname = 'nom_' . time();
        $firstname = 'prenom_' . time();
        $data = '{"lastname":"'.$lastname.'", "firstname": "'.$firstname.'"}';
        $client = $this->createAuthenticatedClient('customer_1', 'azerty123');
        $client->request('POST', '/api/users', [], [], ['CONTENT_TYPE' => 'application/json'], $data);
        $response = $client->getResponse();
        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em->getRepository(User::class)->findOneByLastname($lastname);
        dump($user);
        $client->request('DELETE', '/api/users/'.$user->getId(), [], [], []);
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }




    /*public function testPostProduct()
    {
        $id = 'id_' . time();
        $desc = 'prenom_' . time();
        $brand = '{"lastname":"'.$lastname.'", "firstname": "'.$firstname.'"}';
        $client = $this->createAuthenticatedClient('customer_1', 'azerty123');
        $client->request('POST', '/api/users', [], [], ['CONTENT_TYPE' => 'application/json'], $data);
        $response = $client->getResponse();
        $this->assertEquals(201, $client->getResponse()->getStatusCode());
    }

    public function testPutUser()
    {
        $lastname = 'nom_' . time();
        $firstname = 'prenom_' . time();
        $data = '{"lastname":"'.$lastname.'", "firstname": "'.$firstname.'"}';
        $client = $this->createAuthenticatedClient('customer_1', 'azerty123');
        $client->request('PUT', '/api/users/55', [], [], ['CONTENT_TYPE' => 'application/json'], $data);
        $response = $client->getResponse();
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }

    public function testDeleteUser()
    {
        $lastname = 'nom_' . time();
        $firstname = 'prenom_' . time();
        $data = '{"lastname":"'.$lastname.'", "firstname": "'.$firstname.'"}';
        $client = $this->createAuthenticatedClient('customer_1', 'azerty123');
        $client->request('POST', '/api/users', [], [], ['CONTENT_TYPE' => 'application/json'], $data);
        $response = $client->getResponse();
        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em->getRepository(User::class)->findOneByLastname($lastname);
        $client->request('DELETE', '/api/users/'.$user->getId(), [], [], []);
        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }*/
}
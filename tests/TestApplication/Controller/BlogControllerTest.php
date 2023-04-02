<?php

declare(strict_types=1);

namespace App\Tests\TestApplication\Controller;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Pagination\Paginator;
use App\Infrastructure\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Functional test for the controllers defined inside BlogController.
 *
 * See https://symfony.com/doc/current/testing.html#functional-tests
 *
 * Execute the application tests using this command (requires PHPUnit to be installed):
 *
 *     $ cd your-symfony-project/
 *     $ ./vendor/bin/phpunit
 *
 * @internal
 */
class BlogControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/blog/');

        $this->assertResponseIsSuccessful();

        self::assertCount(
            Paginator::PAGE_SIZE,
            $crawler->filter('article.post'),
            'The homepage displays the right number of posts.',
        );
    }

    public function testRss(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/blog/rss.xml');

        $this->assertResponseHeaderSame('Content-Type', 'text/xml; charset=UTF-8');

        self::assertCount(
            Paginator::PAGE_SIZE,
            $crawler->filter('item'),
            'The xml file displays the right number of posts.',
        );
    }

    /**
     * This test changes the database contents by creating a new comment. However,
     * thanks to the DAMADoctrineTestBundle and its PHPUnit listener, all changes
     * to the database are rolled back when this test completes. This means that
     * all the application tests begin with the same database contents.
     */
    public function testNewComment(): void
    {
        $client = static::createClient();

        /** @var UserRepositoryInterface $userRepository */
        $userRepository = $client->getContainer()->get(UserRepository::class);

        /** @var User $user */
        $user = $userRepository->findOneByUsername('jane_admin');

        $client->loginUser($user);

        $client->followRedirects();

        // Find first blog post
        $crawler = $client->request('GET', '/en/blog/');
        $postLink = $crawler->filter('article.post > h2 a')->link();

        $client->click($postLink);
        $crawler = $client->submitForm('Publish comment', [
            'comment[content]' => 'Hi, Symfony!',
        ]);

        $newComment = $crawler->filter('.post-comment')->first()->filter('div > p')->text();

        self::assertSame('Hi, Symfony!', $newComment);
    }

    public function testAjaxSearch(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/en/blog/search', ['q' => 'lorem']);

        $this->assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('article.post'));
        self::assertSame('Lorem ipsum dolor sit amet consectetur adipiscing elit', $crawler->filter('article.post')->first()->filter('h2 > a')->text());
    }
}

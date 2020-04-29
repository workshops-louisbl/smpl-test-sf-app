<?php


namespace App\Tests\Controller;


use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BlogControllerTest extends WebTestCase
{
    public function testIndexListTenPosts()
    {
        $client = static::createClient();

        $crawler = $client->request("GET", "/en/blog/");

        $this->assertResponseIsSuccessful("Blog index loads successfully");

        $this->assertCount(
            10,
            $crawler->filter("article.post"),
            "Blog index lists 10 articles"
        );
    }

    public function testIndexShowsPostSummary()
    {
        $client = static::createClient();

        $crawler = $client->request("GET", "/en/blog/");
        $this->assertResponseIsSuccessful();

        $actualPostTitle = $crawler->filter("article.post")->first()->filter("h2 a")->text();
        $expectedPost = $client->getContainer()->get("doctrine")->getRepository(Post::class)->find(1);
        $expectedPostTitle = $expectedPost->getTitle();

        $this->assertSame($expectedPostTitle, $actualPostTitle);
    }

    public function testIndexLinkToPost()
    {
        $client = static::createClient();

        $crawler = $client->request("GET", "/en/blog/");
        $this->assertResponseIsSuccessful();

        $postAnchor = $crawler->filter("article.post")->first()->filter("h2 a");
        $expectedPostTitle = $postAnchor->text();
        $postLink = $postAnchor->link();

        $crawler = $client->click($postLink);

        $actualPostTitle = $crawler->filter("h1")->text();

        $this->assertSame($expectedPostTitle, $actualPostTitle);
    }

    public function testShowPost()
    {
        $client = static::createClient();

        $expectedPost = $client->getContainer()->get("doctrine")->getRepository(Post::class)->find(1);

        $crawler = $client->request("GET", "/en/blog/posts/".$expectedPost->getSlug());

        $actualPostTitle = $crawler->filter("h1")->text();

        $this->assertSame($expectedPost->getTitle(), $actualPostTitle);
    }
}
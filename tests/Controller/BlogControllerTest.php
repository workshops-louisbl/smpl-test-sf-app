<?php


namespace App\Tests\Controller;


use App\Entity\Comment;
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

    public function testCommentAsUser()
    {
        $client = static::createClient([], [
            "PHP_AUTH_USER" => "john_user",
            "PHP_AUTH_PW" => "kitten",
        ]);
        $client->followRedirects();

        $expectedPost = $client->getContainer()->get("doctrine")->getRepository(Post::class)->find(1);

        $client->request("GET", "/en/blog/posts/".$expectedPost->getSlug());

        $newComment = "This is a comment from a test";
        $crawler = $client->submitForm("Publish comment", [
            "comment[content]" => $newComment
        ]);

        $comment = $crawler->filter(".post-comment")->first()->filter("div > p")->text();

        $this->assertSame($newComment, $comment);

        $storedComments = $client->getContainer()->get("doctrine")->getRepository(Comment::class)->findBy([
            "content" => $newComment
        ]);

        $this->assertGreaterThanOrEqual(1, $storedComments);

        $storedComment = $client->getContainer()->get("doctrine")->getRepository(Comment::class)->findOneBy([
            "content" => $newComment
        ]);
        $this->assertSame($newComment, $storedComment->getContent());
    }

    public function testAjaxSearch(): void
    {
        $client = static::createClient();
        $client->xmlHttpRequest('GET', '/en/blog/search', ['q' => 'lorem']);

        $results = json_decode($client->getResponse()->getContent(), true);

        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $this->assertCount(1, $results);
        $this->assertSame('Lorem ipsum dolor sit amet consectetur adipiscing elit', $results[0]['title']);
        $this->assertSame('Jane Doe', $results[0]['author']);
    }
}
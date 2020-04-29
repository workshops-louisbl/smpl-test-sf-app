<?php


namespace App\Tests\Controller;


use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SmokeTest extends WebTestCase
{
    /**
     * @dataProvider generateUrls
     */
    public function testPublicPagesAreLoadingSuccessfully($url)
    {
        $client = static::createClient();

        $client->request("GET", $url);

        $this->assertResponseIsSuccessful("Home loaded successfully");
    }

    public function generateUrls()
    {
        return array(
            array("/"),
            array("/en/blog"),
        );
    }

    public function testBlogRedirectSuccessfully()
    {
        $client = static::createClient();

        $client->request("GET", "/en/blog");

        $this->assertResponseRedirects(
            "http://localhost/en/blog/",
            Response::HTTP_MOVED_PERMANENTLY,
            "Blog redirects successfully"
        );

        $client->followRedirect();

        $this->assertResponseIsSuccessful();
    }

    public function testBlogPostLoadsSuccessfully()
    {
        $client = static::createClient();
        // the service container is always available via the test client
        $blogPost = $client->getContainer()->get('doctrine')->getRepository(Post::class)->find(1);

        $client->request('GET', sprintf('/en/blog/posts/%s', $blogPost->getSlug()));

        $this->assertResponseIsSuccessful();
    }
}
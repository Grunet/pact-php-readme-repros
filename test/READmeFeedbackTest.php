<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

use PhpPact\Standalone\MockService\MockServer;
use PhpPact\Standalone\MockService\MockServerConfig;

use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use PhpPact\Consumer\InteractionBuilder;

use GuzzleHttp\Client;

final class READmeFeedbackTest extends TestCase
{
    public function test__READme_Feedback(): void
    {
        // Create your basic configuration. The host and port will need to match
        // whatever your Http Service will be using to access the providers data.
        $config = new MockServerConfig();
        $config->setHost('localhost');
        $config->setPort(7200);
        $config->setConsumer('someConsumer');
        $config->setProvider('someProvider');
        $config->setCors(true);

        // $config->setHealthCheckTimeout(20);
        // $config->setHealthCheckRetrySec(2);

        $request = new ConsumerRequest();
        $request
            ->setMethod('GET')
            ->setPath('/get');


        $response = new ProviderResponse();
        $response
            ->setStatus(200)
            ->addHeader('Content-Type', 'application/json');

        // Instantiate the mock server object with the config. This can be any
        // instance of MockServerConfigInterface.
        $server = new MockServer($config);

        // Create the process.
        $server->start();

        // Create a configuration that reflects the server that was started. You can
        // create a custom MockServerConfigInterface if needed. This configuration
        // is the same that is used via the PactTestListener and uses environment variables.
        $builder = new InteractionBuilder($config);
        $builder
            ->given('a person exists')
            ->uponReceiving('a get request to /hello/{name}')
            ->with($request)
            ->willRespondWith($response); // This has to be last. This is what makes an API request to the Mock Server to set the interaction

        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $config->getBaseUri(),
        ]);

        $response = $client->request('GET', '/get');

        $builder->verify();

        // Stop the process.
        $server->stop();

        $this->expectNotToPerformAssertions();
    }
}
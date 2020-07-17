<?php

namespace App\Action;

use App\Domain\User\Service\UserReader;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

final class UserReadAction
{
    private $userReader;

    public function __construct(UserReader $userReader)
    {
        $this->userReader = $userReader;
    }

    public function __invoke(ServerRequest $request, Response $response, array $args = []): Response
    {
        // Invoke the Domain with inputs and retain the result
		$rslt = $this->userReader->getUsers();

        // Build the HTTP response
        return $response->withJson($rslt)->withStatus(200);
    }
}
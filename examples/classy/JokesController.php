<?php

require 'Joke.php';
require 'User.php';


use Zarf\{Controller, Request, Response};

class JokesController extends Controller
{
    private Joke $jokes;
    private User $users;
    function __construct(Joke $jokes, User $users)
    {
        $this->jokes = $jokes;
        $this->users = $users;
    }
    public function index(Request $request, Response $response)
    {
        $jokesAll = $this->jokes->findMany();
        return $response->json($jokesAll);
    }

    public function detail(Request $request, Response $response)
    {
        $joke = $this->jokes->findOne($request->params['id']);
        if ($joke) {
            return $response->json($joke);
        } else {
            return $response->notFound();
        }
    }
    public function create(Request $request, Response $response)
    {
        echo "create";
    }
    public function update(Request $request, Response $response)
    {
        echo "update";
    }
    public function delete(Request $request, Response $response)
    {
        echo "delete";
        $joke = $this->jokes->delete('id', $request->params['id']);
        if ($joke) {
            echo $joke['id'];
        } else {
            echo "not available";
        }
    }
}

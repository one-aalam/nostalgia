<?php

use Zarf\{Zarf, Model, Request};

class Joke extends Model
{
}

$jokes = new Joke;

$app = new Zarf;

$app->get('/jokes', function () use ($jokes) {
    $jokesAll = $jokes->findMany();
    foreach ($jokesAll as $key => $joke) {
        echo $key;
        echo $joke['id'] . '   \n';
    }
})->get('/jokes/:id', function (Request $req)  use ($jokes) {
    $joke = $jokes->findOne($req->params['id']);
    if ($joke) {
        echo $joke['id'];
    } else {
        echo "not available";
    }
})->del('/jokes/:id', function (Request $req)  use ($jokes) {
    $joke = $jokes->delete('id', $req->params['id']);
    if ($joke) {
        echo $joke['id'];
    } else {
        echo "not available";
    }
});

$app->run();

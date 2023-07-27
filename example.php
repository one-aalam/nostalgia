<?php

use Ramsey\Uuid\Uuid;

$jokesTbl = new DBTable($pdo, "jokes");

// List
$jokes = $jokesTbl->findAll();
foreach ($jokes as $key => $joke) {
    echo $key;
    echo $joke['id'] . '   ';
}

// Create
$query = $jokesTbl->create([
    'id' => Uuid::uuid4(),
    'joke' => 'lorem ipsum doler sit amet'
]);

// Update
$query = $jokesTbl->updateById(
    '21c0cbd5-053f-45b4-8f99-4ba84f877995',
    [
        'joke' => 'dorem dipsum oler it met'
    ]
);

// Delete
$query = $jokesTbl->deleteById(
    '46e082ca0-3263-4244-aab1-1be490534baa'
);

<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;
use Tests\TestCase;

class ExceptionTest extends TestCase
{
    public function testInvalidArgumentException()
    {
        Route::get(
            '/test',
            function () {
                throw new InvalidArgumentException('invalid argument');
            }
        );

        $response = $this->get('/test');

        $response
            ->assertStatus(422)
            ->assertJsonFragment(
                [
                'error' => 'invalid argument'
                ]
            );
    }

    public function testNotFoundException()
    {
        $response = $this->get('/test');

        $response
            ->assertStatus(404)
            ->assertJsonFragment(
                [
                'error' => 'Запрашиваемая страница не существует'
                ]
            );
    }

    public function testQueryException()
    {
        Route::get(
            '/test',
            function () {
                throw new QueryException('QueryException', '', [], new \Exception('QueryException'));
            }
        );

        $response = $this->get('/test');

        $response
            ->assertStatus(500)
            ->assertJsonFragment(
                [
                'error' => 'QueryException (Connection: QueryException, SQL: )'
                ]
            );
    }

    public function testMethodNotAllowedHttpException()
    {
        Route::get(
            '/test',
            function () {
                return response()->json([]);
            }
        );

        $response = $this->post('/test');

        $response
            ->assertStatus(405)
            ->assertJsonFragment(
                [
                'error' => 'The POST method is not supported for route test. Supported methods: GET, HEAD.'
                ]
            );
    }
}

<?php

namespace App\Http\Controllers;


/**
 * @OA\Info(
 *    title="Innoscripta Api Docs",
 *    version="v1",
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     securityScheme="api_key",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */

abstract class Controller
{
    //
}

<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    info: new OA\Info(
        title: 'Bulk Message Sender API',
        version: '1.0.0',
        description: 'API documentation for Bulk Message Sender project'
    ),
)]
final class OpenApiSpec {}
    
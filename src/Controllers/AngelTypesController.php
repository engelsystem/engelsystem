<?php

declare(strict_types=1);

namespace Engelsystem\Controllers;

use Engelsystem\Http\Response;
use Engelsystem\Models\AngelType;

class AngelTypesController extends BaseController
{
    public function __construct(protected Response $response)
    {
    }

    public function about(): Response
    {
        $angeltypes = AngelType::all();

        return $this->response->withView(
            'pages/angeltypes/about',
            ['angeltypes' => $angeltypes]
        );
    }
}

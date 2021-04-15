<?php


namespace App\Controller;


use http\Env\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiDocLogin
{
    /**
     * @Route(name="api_login_check_get", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function getToken(Request $request) {

        return new Response(
            '<html><body>Lucky number:</body></html>'
        );
    }
}
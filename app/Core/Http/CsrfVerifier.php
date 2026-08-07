<?php namespace Neo\Core\Http;
class CsrfVerifier
{
    public function handle($app)
    {
        $request = $app->request;
        if (
            $request->isMethod("POST") ||
            $request->isMethod("PUT") ||
            $request->isMethod("DELETE")
        ) {
            $token =
                $request->input("_token") ??
                ($_SERVER["HTTP_X_CSRF_TOKEN"] ?? null);
            if (!$token || $token !== $app->session->get("_token")) {
                http_response_code(419);
                die("<h1>419 | Page Expired (CSRF Mismatch)</h1>");
            }
        }
        return true;
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MinifyHtml
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only minify if it's a standard HTML response and not a binary file download, etc.
        if ($this->isHtml($response)) {
            $content = $response->getContent();
            $minified = $this->minify($content);
            $response->setContent($minified);
        }

        return $response;
    }

    protected function isHtml(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type');
        return str_contains($contentType, 'text/html');
    }

    protected function minify(string $buffer): string
    {
        $search = [
            '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
            '/[^\S ]+\</s',     // strip whitespaces before tags, except space
            '/(\s)+/s',         // shorten multiple whitespace sequences
            '/<!--(.|\s)*?-->/' // Remove HTML comments
        ];

        $replace = [
            '>',
            '<',
            '\\1',
            ''
        ];

        return preg_replace($search, $replace, $buffer);
    }
}

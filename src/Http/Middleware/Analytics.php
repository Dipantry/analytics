<?php

namespace Dipantry\Analytics\Http\Middleware;

use Closure;
use Dipantry\Analytics\Agent;
use Dipantry\Analytics\Contracts\SessionProvider;
use Dipantry\Analytics\Models\PageView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class Analytics
{
    public function handle(Request $request, Closure $next)
    {
        $uri = str_replace($request->root(), '', $request->url()) ?: '/';

        $response = $next($request);

        $agent = new Agent();
        $agent->setUserAgent($request->headers->get('user-agent'));
        $agent->setHttpHeaders($request->headers->all());

        if (config('analytics.ignoreRobots', false) && $agent->isRobot()) {
            return $response;
        }

        foreach (config('analytics.mask', []) as $mask) {
            $mask = trim($mask, '/');

            if ($request->fullUrlIs($mask) || $request->is($mask)) {
                $uri = '/' . str_replace('*', '∗︎', $mask);
                break;
            }
        }

        $view = new PageView();
        $view->session = $this->getSessionProvider()->get($request);
        $view->uri = $uri;
        $view->source = $request->headers->get('referer');
        $view->country = $agent->languages()[0] ?? 'en-en';
        $view->browser = $agent->browser() ?? null;
        $view->device = $agent->deviceType();
        $view->save();

        return $response;
    }

    protected function input(Request $request): array
    {
        $files = $request->files->all();

        array_walk_recursive($files, function (&$file) {
            $file = [
                'name' => $file->getClientOriginalName(),
                'size' => $file->isFile() ? ($file->getSize() / 1000) . 'KB' : '0',
            ];
        });

        return array_replace_recursive($request->input(), $files);
    }

    private function getSessionProvider(): SessionProvider
    {
        return App::make(config('analytics.session.provider'));
    }
}
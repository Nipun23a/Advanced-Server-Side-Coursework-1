<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        if (!$session -> get('is_logged_in')){
            $session->set('redirect_url',current_url());
            return redirect() -> to('/auth/login')
                -> with('error','Please login to access this page.');
        }

        $loginTime = $session -> get('login_time');
        $maxInactivity = 7200;
        $lastActivity = $session -> get('last_activity') ?? $loginTime;

        if ((time() - $lastActivity) > $maxInactivity){
            $session -> destroy();
            return redirect() -> to ('/auth/login') -> with('error','Your session has expired. Please login again.');
        }

        $session -> set('last_activity',time());
        if (!empty($arguments)){
            $userRole = $session -> get('user_role');
            if (! in_array($userRole,$arguments)){
                return redirect() -> to('/dashboard') -> with('error','You do not have permission to access this page.');
            }
        }
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post processing required
    }
}

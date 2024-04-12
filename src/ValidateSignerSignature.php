<?php

namespace Masterei\Signer;

use Illuminate\Support\Facades\Auth;
use Masterei\Signer\Models\Signed;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Symfony\Component\HttpFoundation\Response;

class ValidateSignerSignature
{
    public function handle(Request $request, Closure $next, ...$args): Response
    {
        // 'relative' argument is used to exclude domain from validation hash.
        $isAbsolute = !in_array('relative', $args);

        // 'strict' argument is used to disable native signed url validation;
        // thus enforcing package only validation method.
        $isStrict = in_array('strict', $args);

        // validate using native signed url method
        if($request->hasValidSignature($isAbsolute) && !$isStrict){
            return $next($request);
        }

        // preparing and populate request url into class object model
        $url = URLParser::fromString($request->fullUrl(), $isAbsolute);

        // retrieving data from database
        $signedRoute = Signed::findValidSignature($url->getSignature(), $url->getPath());

        // 1. if signed url cannot be found on database or already expired
        // 2. if user doesn't exist in the authorized user list
        // 3. if route access is already fully consumed
        if(empty($signedRoute)
            || !$this->validateAuthentication($signedRoute, $request)
            || !$this->validateConsume($signedRoute)){
            throw new InvalidSignatureException;
        }

        return $next($request);
    }

    /**
     * Validating if current authenticated user has access to url.
     */
    private function validateAuthentication(Signed $signedRoute): bool
    {
        // terminate if url does not use authenticated user
        if(!isset($signedRoute->parameters->data->auth)){
            return true;
        }

        // require to be authenticated
        if(!Auth::check()){
            abort(401, 'Unauthenticated.');
        }

        return in_array(Auth::user()->id, $signedRoute->parameters->data->auth->users);
    }

    /**
     * Validate if url is fully consumed or not.
     * Signed url is automatically deleted after fully consumed from database.
     */
    private function validateConsume(Signed $signedRoute): bool
    {
        // terminate if url does not use consumable access
        if(!isset($signedRoute->parameters->data->consumable)){
            return true;
        }

        // limit counter
        $signedRoute->parameters->data->consumable->remaining_access--;

        // update or delete based on counter status
        if($signedRoute->parameters->data->consumable->remaining_access <= 0){
            $signedRoute->delete();
        } else {
            $signedRoute->update([
                'parameters' => $signedRoute->parameters
            ]);
        }

        return true;
    }
}

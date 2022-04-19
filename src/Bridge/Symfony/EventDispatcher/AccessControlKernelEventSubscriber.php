<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Bridge\Symfony\EventDispatcher;

use MakinaCorpus\AccessControl\Authorization;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Controller\ErrorController;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Listen on kernel event, when controller is found, in order to be able to
 * read its attributes and allow or disallow access to it.
 *
 * We listen on KernelEvents::CONTROLLER_ARGUMENTS event, once all arguments
 * have been computed so that they may serve in the future for attribute based
 * calculations.
 *
 * Please note that this class might need some love, it has been written very
 * quickly in order to put in place a proof of concept.
 */
final class AccessControlKernelEventSubscriber implements EventSubscriberInterface
{
    private Authorization $authorization;

    public function __construct(Authorization $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => ['onKernelControllerArguments', -10000],
        ];
    }

    public function onKernelControllerArguments(ControllerArgumentsEvent $event)
    {
        $controller = $event->getController();
        if ((\is_string($controller) && $controller === ErrorController::class) || (\is_object($controller) && $controller instanceof ErrorController)) {
            return;
        }

        $arguments = $this->getArguments($controller, $event);

        if (!$this->authorization->isGranted($controller, $arguments)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Event controller arguments are numerically indexed, we need to name them
     * in order to propagate correct names, and allow API end user to use its
     * controller argument names as context value names for service and method
     * call expressions.
     */
    private function getArguments(mixed $controller, ControllerArgumentsEvent $event): array
    {
        $eventArgs = $event->getArguments();
        $argsSize = \count($eventArgs);

        $callback = \Closure::fromCallable($controller);

        try {
            $ret = [];
            foreach ((new \ReflectionFunction($callback))->getParameters() as $index => $parameter) {
                \assert($parameter instanceof \ReflectionParameter);
                if ($index < $argsSize) {
                    $ret[$parameter->getName()] = $eventArgs[$index];
                } else if ($parameter->isDefaultValueAvailable()) {
                    // For all unspecified values, use the function parameter
                    // default value, if set or optional.
                    $ret[$parameter->getName()] = $parameter->getDefaultValue();
                }
            }
        } catch (\ReflectionException $e) {
            // Better be safe than sorry, this will restore previous
            // version behaviour.
            $ret = $eventArgs;
        }

        $request = $event->getRequest();

        // Always expose request.
        if (!\array_key_exists('request', $ret)) {
            $ret['request'] = $request;
        }

        // Expose meta-arguments for reading incomming request parameters.
        $ret['_get'] = new ParameterBagValueHolder($request->query);
        $ret['_post'] = new ParameterBagValueHolder($request->request);

        return $ret;
    }

    private function getParameters(ControllerArgumentsEvent $event): array
    {

    }
}

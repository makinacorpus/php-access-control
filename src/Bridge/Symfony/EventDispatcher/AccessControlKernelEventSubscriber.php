<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Bridge\Symfony\EventDispatcher;

use MakinaCorpus\AccessControl\Authorization;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Listen on kernel event, when controller is found, in order to be able to
 * read its annotation and allow or disallow access to it.
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

        // A controller can be anything that is "callable" per the PHP engine
        // but we cannot handle all use cases, let's just see how it goes with
        // those provided here.
        if (\is_object($controller)) {
            // Controller is an instance of something, it could be a closure
            // or a callable class with an __invoke() method, let see is this
            // class has policies.
            if (!$this->authorization->isGranted($controller, ...$event->getArguments())) {
                throw new AccessDeniedException();
            }
        } else if (\is_string($controller)) {
            // A callable string is probably a function name, let's just see
            // if that works.
            // @todo It could be something like "ClassName::method" as well.
            if (!$this->authorization->isGranted($controller, ...$event->getArguments())) {
                throw new AccessDeniedException();
            }
        } else if (\is_array($controller) && \count($controller) === 2) {
            // This supposed to be the most common way of having a controller,
            // we have an array containing either a class name (if it's
            // stateless) or an instance (if it's a service) of a controller
            // class name, and a method name as second parameter.
            $object = $controller[0];
            if (\is_object($object) || (\is_string($object) && \class_exists($object))) {
                if (!$this->authorization->isMethodGranted($object, $controller[1], ...$event->getArguments())) {
                    throw new AccessDeniedException();
                }
            }
        }
    }
}
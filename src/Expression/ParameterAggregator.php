<?php

declare(strict_types=1);

namespace MakinaCorpus\AccessControl\Expression;

use MakinaCorpus\AccessControl\AccessConfigurationError;

final class ParameterAggregator
{
    /**
     * Aggregate parameters from context.
     *
     * Policy parameters are the user given parameter names, they are simple
     * strings, such as "resource" or "subject", but they also can be arbitrary
     * parameters.
     */
    public function aggregateParameters(array $policyParameters, $subject, $resource, $userParameters): array
    {
        $ret = [];
        foreach ($policyParameters as $parameterName) {
            switch ($parameterName) {

                case 'resource':
                    $ret[] = $resource;
                    break;

                case 'subject':
                    $ret[] = $subject;
                    break;

                default:
                    throw new AccessConfigurationError(\sprintf("Unsupported parameter name: '%s'", $parameterName));
            }
        }

        foreach ($userParameters as $parameterValue) {
            $ret[] = $parameterValue;
        }

        return $ret;
    }
}

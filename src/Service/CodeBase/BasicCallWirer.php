<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\CodeBase;

use Micoli\SymfonyCartography\Model\AnalyzedCodeBase;
use Micoli\SymfonyCartography\Model\EnrichedClass;
use Micoli\SymfonyCartography\Model\EnrichedMethod;
use Micoli\SymfonyCartography\Model\MethodCall;
use Micoli\SymfonyCartography\Model\MethodName;

final class BasicCallWirer implements CodeBaseWireInterface
{
    public function wire(AnalyzedCodeBase $analyzedCodebase): void
    {
        /**
         * @var EnrichedClass $class
         * @var EnrichedMethod $method
         * @var MethodCall $call
         */
        foreach ($analyzedCodebase->enrichedClasses->getMethodCalls() as [$class, $method, $call]) {
            $implements = $analyzedCodebase->interfaceImplements->get($call->to->namespacedName);
            if ($implements === null) {
                continue;
            }
            $method->getMethodCalls()->remove($call);
            foreach ($implements as $implement) {
                $method->getMethodCalls()->append(new MethodCall(
                    $call->from,
                    new MethodName($implement, $call->to->name),
                    $call->arguments,
                    null,
                ));
            }
        }
    }
}

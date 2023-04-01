<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\CodeBase;

use Micoli\SymfonyCartography\Model\AnalyzedCodeBase;
use Micoli\SymfonyCartography\Model\MethodCall;
use Micoli\SymfonyCartography\Model\MethodName;

final class BasicCallWirer implements CodeBaseWireInterface
{
    public function wire(AnalyzedCodeBase $analyzedCodebase): void
    {
        foreach ($analyzedCodebase->enrichedClasses->getMethodCalls() as [$class, $method, $call]) {
            $implements = $analyzedCodebase->interfaceImplements->get($call->to->namespacedName);
            if ($implements === null) {
                continue;
            }
            foreach ($implements as $implement) {
                $method->getMethodCalls()->remove($call);
                $method->getMethodCalls()->add(new MethodCall(
                    $call->from,
                    new MethodName($implement, $call->to->name),
                    $call->arguments,
                    null,
                ));
            }
        }
    }
}
